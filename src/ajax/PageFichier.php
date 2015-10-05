<?php
//Récupération des classes quelque soit le niveau du site.
chdir('..');
require_once('app/etc/init.php');
$controller = init_core();

if (isset($_GET['dossier']))
{
	$dir = './'.$_GET['dossier'];
	$dir_icone = $controller->conf->path->image.'design/icones/';
	$trash = $controller->conf->DossierCorbeille;
	$exts = $controller->conf->ExtensionModifiable;
	$chaine = FileExplorer::GestionFichier($dir_icone, $trash, $exts, $dir);
	echo str_replace('\\/','/',json_encode($chaine));
}

if (isset($_GET['infodossier']))
{
	$filename = $_GET['infodossier'];
	$name = basename($filename);
	if ($name =='..')
	{
		$name = 'racine';	
	}
	$dir = new Dir($filename);
	$info = array('nom'=>$name,'taille'=>File::format_size($dir->size()));
	echo json_encode($info);
}

?>