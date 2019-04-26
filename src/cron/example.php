<?php

// Initialisation du Cron.
chdir("../../");
require("app/core.php");
$controller = init();

// Fichier de log.
$dir = $controller->config->path->root_dir.$controller->config->path->log;
$controller->error->set_file($dir.'cron/example.log');

// Définition de l'utilisateur.
// $controller->session->open(User::load(1));

// Définition de la route.
$controller->router->module("index");
$controller->router->action("index");

// Execution.
execute($controller);
show($controller);
?>