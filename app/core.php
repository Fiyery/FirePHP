<?php
require(__DIR__.'/lib/core/Core.class.php');
$core = new Core();

// Chargement des paramètres et classes outils et récupération du controller.
$controller = $core->get_controller();

// On impose à la fin du script le lancement de la barre de debug.
if ($controller->config->feature->debug && $controller->req->disable_debug_tool == NULL)
{
	register_shutdown_function(function() use ($controller)
	{
		$controller->hook->notify(new Event('Core::end_script'));
	});
}

// Chargement du Controller du site.
$redirect = ($controller->config->feature->access_redirection === TRUE) ? (TRUE) : (FALSE);
if ($controller->config->feature->access)
{
	$controller->get_access($redirect);
}

try
{
	// Exécution du Controller spécifique.
	if ($controller->execute() !== FALSE)
	{
		// Envoie des informations à la vue.
		$controller->assign();
	}
}
catch (Exception $e) 
{
	$controller->error->handle_exception($e);
}

// Récupération les affichages "parasites" (echo, print, var_dump...).
$echx = ob_get_clean();

// Début de la capture des affichages PHP avec compression GZIP si possible
if (ob_start("ob_gzhandler") == FALSE)
{
	ob_start();
}

// Affiche le template.
if ($controller->config->tpl->enable)
{
	try
	{
		$controller->tpl->display($controller->config->path->root_dir.$controller->config->path->tpl.'main/main.tpl');
	}
	catch (Exception $e)
	{
		var_dump($e);
	}
} 
else 
{
	echo file_get_contents($controller->config->path->root_dir.$controller->config->path->tpl.'main/main.tpl');
}

// On vide le buffer.
ob_end_flush();
?>