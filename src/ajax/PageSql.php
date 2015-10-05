<?php
//Récupération des classes quelque soit le niveau du site.
chdir('..');
require_once('app/etc/init.php');
$controller = init_core();

if (isset($_GET['table']))
{
	$modes = explode(',',Config::get('ModeFormulaire'));
	$code = '';
	foreach ($modes as $m)
	{
		$code .= Toolbox::Formulaire($m,$_GET['table'],'?module=PageSql&action=traitement');
	}
	echo str_replace('\\/','/',json_encode(utf8_encode($code)));
}
?>