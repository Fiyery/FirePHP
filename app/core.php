<?php
// Début de la capture des affichages type echo / erreurs.
ob_start();

// On affichage la barre de debug à la fin du script.
function debug_tool_load($controller, $echx=NULL)
{
    if ($echx === NULL)
    {
        $echx = ob_get_clean();
    }    
    chdir(__DIR__);
	if ($controller->config->ENABLE_DEBUG !== FALSE && file_exists(__DIR__.'/../app/plugins/debug_tool/debug_tool.php'))
	{
		require_once(__DIR__.'/../app/plugins/debug_tool/debug_tool.php');
		debug_tool_exec($controller, $echx);
	}
}

// Chargement des paramètres et classes outils et récupération du controller.
require(__DIR__.'/../app/etc/init.php');
$controller = init_core();

// On impose à la fin du script le lancement de la barre de débug.
if ($controller->config->feature->debug && $controller->req->disable_debug_tool == NULL)
{
	register_shutdown_function('debug_tool_load', $controller);
}

// Message à afficher si la reprise de session ne peut se faire.
if ($controller->session->get_status() == -1)
{
	$controller->site->add_message('Votre session a expirée. Vous avez été déconnecté.');
}
elseif ($controller->session->get_status() == -2)
{
	$controller->site->add_message('Votre session est invalide. Vous avez été déconnecté.');
}

// Chargement du Controller du site.
$redirect = ($controller->config->feature->access_redirection === TRUE) ? (TRUE) : (FALSE);
if ($controller->config->feature->access)
{
	$controller->get_access($redirect);
}

// Exécution du Controller spécifique.
$controller->execute();

// Envoie des informations à la vue.
try
{
    $controller->assign();
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
try 
{
    $controller->tpl->display($controller->config->path->root_dir.$controller->config->path->tpl.'main/main.tpl');
}
catch (Exception $e) 
{
    var_dump($e);
}

// Lance de débogage.
if ($controller->config->feature->debug && $controller->req->disable_debug_tool == NULL)
{
	debug_tool_load($controller, $echx);
}

// On vide le buffer.
ob_end_flush();
?>