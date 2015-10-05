<?php
// Récupération des classes quelque soit le niveau du site.
chdir('../../');
require_once('app/etc/init.php');
$controller = init_core();

$return = new stdClass();

if ($controller->session->is_open() == FALSE)
{
	$return->error = "Les informations transmisent ne sont pas valides";
}
else
{
	/* Avec XMLHttpRequest :
	$header = getallheaders();
	$file_name = $header['x-file-name'];
	$file_size = $header['x-file-size'];
	$file_type = $header['x-file-type'];
	$file_source = file_get_contents('php://input');
	//*/
	
	//* Avec AJAX :
	$file_name = $controller->req->name;
	$file_size = $controller->req->size;
	$file_type = $controller->req->type;
	$file_source = $controller->req->source;
	//*/
	
	$allowed_type = array('image/jpeg','image/jpg','image/bmp','image/png','image/gif');
	if (in_array($file_type,$allowed_type) == FALSE)
	{
		$return->error = "Le format du fichier n'est pas supporté";
	}
	else 
	{
		$max_file_size = 2; // En Mo.
		if ($file_size > $max_file_size * 1048576)
		{
			$size = number_format($header['x-file-size']/1048576,2,',','');
			$return->error = 'La taille du fichier dépasse la taille maximal : '.$max_file_size.' Mo'."\r\n".'et votre fichier fait : '.$size.' Mo';
		}
		else
		{
			$dir = Membre::get_root_heberg(Site::DIR).$controller->session->user->IdMembre.'/';
			if (file_exists($dir) == FALSE)
			{
				mkdir($dir, '0755', TRUE);
			}
			$dir = new Dir($dir);
			if ($file_size + $dir->size() > $controller->session->user->LimitCapacite * 1048576)
			{
				$return->error = 'Vous avez atteint votre limite maximale de stockage : '.$controller->session->user->LimitCapacite.' Mo';
			}
			else
			{
				$ext = array_reverse(explode('.',$file_name));
				$ext = (count($ext) >= 2) ? ($ext[0]) : ('jpg');
				$name = $file_name;
				$name_encode = md5($name);
				while (file_exists($dir->get().$name_encode.'.'.$ext))
				{
					$name .= time(); 
					$name_encode = md5($name);
				}
				$file = $name_encode.'.'.$ext;
				
				$upload = Upload::get_instance();
				$upload->set_exts(array('jpg','gif','bmp','png','jpeg'));
				$upload->load('source');				
				$upload->set_size($max_file_size * 1048576);
				if ($upload->check() == FALSE)
				{
					if ($upload->get_file()->is_image() == FALSE)
					{
						$return->error = "Le format du fichier n'est pas supporté. Le fichier n'est pas une image";
					}
					elseif  ($upload->get_size() > $max_file_size * 1048576)
					{
						$size = number_format($file_size/1048576,2,',','');
						$return->error = 'La taille du fichier dépasse la taille maximale : '.$max_file_size.' Mo'."\r\n".'et votre fichier fait : '.$size.' Mo';
					}
					else 
					{
						$return->error = 'Erreur inconnue. Le fichier n\'a pas été transféré';
					}
				}
				else
				{
					$upload->move($dir->get().$file);
					$new_size_dir = $dir->size();
					if ($new_size_dir > $controller->session->user->LimitCapacite * 1048576)
					{
						$return->error = 'Vous avez atteint votre limite maximale de stockage : '.$controller->session->user->LimitCapacite.' Mo';
						unlink($dir->get().$file);
					}
					else 
					{
						$files = $dir->scan();
						$nb = 0;
						foreach ($files as $f)
						{
							if (is_dir($dir->get().$f) == FALSE)
							{
								$nb++;
							}
						}
						$limit_size = $controller->session->user->LimitCapacite;
						$current_size = number_format($new_size_dir/(1024*1024),2);
						$return->size = $current_size;
						$return->width = ($current_size/$limit_size)*100;
						$return->limit = $limit_size;
						$return->nb = $nb;	
						$return->filename = $dir->get().$file;
						$return->filename = $controller->site->get_root().'img/'.String::format_url($controller->session->user->Pseudo).'/'.$file;
						$return->name = $file;	
					}
				}
			}
		}
	}
}


echo json_encode($return);

?>