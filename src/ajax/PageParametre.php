<?php
//Récupération des classes quelque soit le niveau du site.
chdir('..');
require_once('app/etc/init.php');
$controller = init_core();

if ($controller->req->nom != NULL && $controller->req->val != NULL)
{
	$c = Configuration::search('Nom', $controller->req->nom);
	if (count($c) > 0)
	{
	   $c = $c[0];
	   $c->modify(array('Valeur'=>$controller->req->val)); 
	}
}
?>