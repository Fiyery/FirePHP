<?php

error_reporting(E_ALL); 
ini_set("display_errors", "stdout");

require(__DIR__.'/lib/Core/Core.class.php');
use FirePHP\Core\Core;
use FirePHP\Event\Event;
use FirePHP\Controller\FrontController;

function init() 
{
    // Début de la capture du tampon de sortie.
    ob_start();
    $core = new Core();
    
    // Chargement des paramètres et classes outils et récupération du controller.
    $controller = $core->controller();
    
	register_shutdown_function(function() use ($controller)
	{
		$errors = error_get_last();
		if (is_array($errors) && count($errors) > 0)
		{
			var_dump($errors);
		}
		$controller->hook->notify(new Event('Core::end_script'));
	});
    return $controller;
}

function execute(FrontController $controller)
{
    try
    {
        // Exécution du Controller spécifique.
        if ($controller->execute() !== FALSE)
        {
            // Envoie des informations à la vue.
            $controller->assign();
        }
    }
    catch (Throwable $e) 
    {
        $controller->error->handle_throwable($e);
    }
}

function show(FrontController $controller)
{
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
            $controller->tpl->display($controller->config->path->root_dir.$controller->config->path->tpl.'main.tpl');
        }
        catch (Exception $e)
        {
            var_dump($e);
        }
    } 
    else 
    {
        echo file_get_contents($controller->config->path->root_dir.$controller->config->path->tpl.'main.tpl');
    }

    // On vide le buffer.
    ob_end_flush();
}

?>