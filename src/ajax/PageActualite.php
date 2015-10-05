<?php
// Récupération des classes quelque soit le niveau du site.
chdir('..');
require_once('app/etc/init.php');
$controller = init_core();

if ($controller->req->id !== NULL)
{
	$a = Actualite::load($controller->req->id);
	if (is_object($a))
	{
		echo json_encode(array('Id'=>$a->IdActu,'Titre'=>$a->NomActu,'Contenu'=>$a->Description));
	}
	else
	{
		echo json_encode('');
	}
}

if ($controller->req->id_actu_long !== NULL)
{
	$a = ActualiteIndex::load($controller->req->id_actu_long);
	if (is_object($a))
	{
		echo json_encode(array('Id'=>$a->IdActu,'Titre'=>$a->Titre,'Contenu'=>$a->Contenu));
	}
	else
	{
		echo json_encode('');
	}
}
?>