<?php
// Nom des moules et actions dans le chemin du site.
$services->get('config')->NAME_MODULE_TREE = array(
	'PageIndex'=> array(
		'name' => 'Accueil',
		'modules' => array(),
	),
	'PageActualite' => array(
		'name' => 'Gestion des actualités',
		'modules' => array(
			'modification'=> 'modifier',				
			'suppression'=> 'supprimer',				
		),
	),
	'PageAdmin' => array(
		'name' => 'Panneau d\'administration',
		'modules' => array(),
	),
	'PageCoeur' => array(
		'name' => 'Kokoro',
		'modules' => array(),
	),
	'PageCompatitibilité' => array(
		'name' => 'Compatibilité',
		'modules' => array(),
	),
	'PageContact' => array(
		'name' => 'Contact',
		'modules' => array(),
	),
	'PageEnregistrement' => array(
		'name' => 'Inscription',
		'modules' => array(),
	),
	'PageEquipe' => array(
		'name' => 'Equipe',
		'modules' => array(),
	),
	'PageFaq' => array(
		'name' => 'Foire aux Questions',
		'modules' => array(),
	),
	'PageFichier' => array(
		'name' => 'Gestion des fichiers',
		'modules' => array(),
	),
	'PageProfil' => array(
		'name' => 'Profil',
		'modules' => array(),
	),
	'PageMessagerie' => array(
		'name' => 'Messagerie',
		'modules' => array(),
	),
	'PageJeu' => array(
		'name' => 'Jeu',
		'modules' => array(),
	),
	'PageTest' => array(
		'name' => 'Laboratoire',
		'modules' => array(),
	),
	'forum/PageForum' => array(
		'name' => 'Sous-Forums',
		'modules' => array(),
	),
	'forum/PageCategorie' => array(
		'name' => 'Catégorie',
		'modules' => array(),
	),
	'forum/PageSujet' => array(
		'name' => 'Sujet',
		'modules' => array(),
	)
);
?>