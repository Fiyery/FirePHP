<?php
// Récupération des classes quelque soit le niveau du site.
chdir('../../');
require_once('app/etc/init.php');
$controller = init_core();

if ($controller->req->post('id') != NULL)
{
	$name = str_replace('..', '.', $controller->req->id);
	$dirname = Membre::get_root_heberg(Site::DIR).$controller->session->user->IdMembre.'/';
	if (file_exists($dirname.$name))
	{
		$file = new File($dirname.$name);
		$size = File::format_size($file->get_size());
		$type = $file->get_type().' ('.$file->get_ext().')';
		$dimension = $file->get_width().'px - '.$file->get_height().'px';
		$url = $controller->site->get_root().'img/'.String::format_url($controller->session->user->Pseudo).'/'.$name;
		$data = array(
			'name' => $name,
			'size' => $size,
			'type' => $type,
			'dimension' => $dimension,
			'url' => $url
		);
		echo json_encode($data);
	}
	else
	{
		echo json_encode(FALSE);	
	}
}

if ($controller->req->post('id_suppr') != NULL)
{
	$name = $controller->req->id_suppr;
	$dirname = Membre::get_root_heberg(Site::DIR).$controller->session->user->IdMembre.'/';
	if (file_exists($dirname.$name))
	{
		unlink($dirname.$name);
		$files = scandir($dirname);
		$nb = 0;
		foreach ($files as $f)
		{
			if (is_dir($dirname.$f) == FALSE)
			{
				$nb++;
			}
		}
		$dir = new Dir($dirname);
		$files = $dir->scan();
		$current_size = number_format($dir->size()/(1024*1024),2);
		$limit_size = $controller->session->user->LimitCapacite;
		$data['size'] = $current_size;
		$data['width'] = ($current_size/$limit_size)*100;
		$data['nb'] = $nb;
		$data['limit'] = $limit_size;
		echo json_encode($data);
	}
}
?>