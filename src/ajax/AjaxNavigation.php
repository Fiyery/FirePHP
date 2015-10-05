<?php
// Récupération des classes quelque soit le niveau du site.
chdir('..');
require_once('app/etc/init.php');
$controller = init_core();

// Variable de retour.
$return = new stdClass();

// Vérification de la source.
if (FALSE && $ajax->req->check_source() == FALSE)
{
	$return->error = new stdClass();
	$return->error->id = 1;
	$return->error->info = "Invalid request domain";
	$return->content = '<h1>fail</h1>';
}
else 
{
	$url = $ajax->req->url;
	$ajax->site->load($url);
	
	require ('../lib/ressources/Smarty-3.1.1/libs/Smarty.class.php');
	$tpl = Template::get_instance();
	
	// Définition du dossier des ressources du site.
	Ressource::set_default_dir('../lib/ressources/cache/');
	
	// Intialisation du Css.
	Css::get_instance($ajax->site->get_module(), $ajax->site->get_action(), '../'.Config::get('DossierCSS'));
	
	// Intialisation du JavaScript.
	Javascript::get_instance($ajax->site->get_module(), $ajax->site->get_action(), '../'.Config::get('DossierJS'));
	
	// Moteur du site
	$moteur = new Engine();
	$moteur->initialize(FALSE);
	$moteur->instructions();
	$return->content = $moteur->load_content();
}

echo json_encode($return);
?>





