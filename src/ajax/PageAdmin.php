<?php
//Récupération des classes quelque soit le niveau du site.
chdir('..');
require_once('app/etc/init.php');
$controller = init_core();

if ($controller->req->id_membre != FALSE && $controller->req->id_groupes != FALSE && Membre::load($controller->req->id_membre) !== FALSE)
{
	$id_membre = $controller->req->id_membre;
	$id_groupes = json_decode($controller->req->id_groupes);
	if (is_array($id_groupes) == FALSE)
	{
		echo '0';
		exit();
	}
	$groups = Groupe::search();
	$find_all = TRUE;
	foreach ($id_groupes as $i)
	{
		$find = FALSE;
		foreach ($groups as $g)
		{
			if ($i == $g->IdGroupe)
			{
				$find = TRUE;
			}
		}
		$find_all = $find_all && $find;
	}
	if ($find_all == FALSE)
	{
		echo '0';
		exit();
	}	
	
	$link = MembreGroupe::search('IdMembre',$id_membre);
	if (count($link) > 0)
	{
		foreach ($id_groupes as $i)
		{
			MembreGroupe::add(array($id_membre,$i));
		}
	}
	else
	{
		foreach ($link as $l)
		{
			if (in_array($l->IdGroupe,$id_groupes))
			{
				unset($id_groupes[array_search($l->IdGroupe,$id_groupes)]);
			}
			else 
			{
				MembreGroupe::delete(array($id_membre,$l->IdGroupe));
			}
		}
		foreach ($id_groupes as $i)
		{
			MembreGroupe::add(array($id_membre,$i));
		}
	}
	echo 1;
}
?>