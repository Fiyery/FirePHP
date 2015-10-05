<?php
// Récupération des classes quelque soit le niveau du site.
chdir('../../');
require_once('app/etc/init.php');
$controller = init_core();

if ($controller->req->note != FALSE && $controller->req->fiche != FALSE)
{
	$controller->route->set_controller('Wiki');
	$controller->route->set_module('PageFiche');
	$controller->route->set_action('ajax_note_fiche');
	$controller->execute();
	exit();
}

if ($controller->req->fiche_membre != FALSE)
{
	$controller->route->set_controller('Wiki');
	$controller->route->set_module('PageFiche');
	$controller->route->set_action('ajax_edit_list');
	$controller->execute();
	exit();
}

if ($controller->req->load_list == 1)
{
	$controller->route->set_controller('Wiki');
	$controller->route->set_module('PageFiche');
	$controller->route->set_action('ajax_load_list');
	$controller->execute();
	exit();
}

if ($controller->req->save_list == 1)
{
	$controller->route->set_controller('Wiki');
	$controller->route->set_module('PageFiche');
	$controller->route->set_action('ajax_save_list');
	$controller->execute();
	exit();
}

if ($controller->req->update_critique == 1)
{
	$controller->route->set_controller('Wiki');
	$controller->route->set_module('PageFiche');
	$controller->route->set_action('ajax_update_critique');
	$controller->execute();
	exit();
}
?>