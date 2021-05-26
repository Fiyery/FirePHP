<?php
namespace FirePHP\Resource;

/**
 * Css gère le traitement des fichiers CSS.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses Ressource
 */
class Css extends Resource
{	
	/**
	 * Constructeur.
	 * @param string $dirname Nom du dossier de destination des fichiers CSS
	 */
	public function __construct($dirname)
	{
		parent::__construct('text/css', 'css', $dirname);
	}
	
	/**
	 * Définie un nouveau package à partir d'un dossier.
	 * @param string $name Nom du package associé.
	 * @param string $dir Chemin du dossier.
	 * @param string[] Liste des extensions à importer si renseigné. Par défaut, c'est ".css".
	 * @return bool
	 */
	public function add_package($name, $dir, $exts=NULL) : bool
	{
	    if ($exts == NULL)
	    {
	        $exts = array('.css');
	    }
	    return parent::add_package($name, $dir, $exts);
	}
	  
	/**
	 * Retourne les liens CSS sous forme de balises HTML. Si les deux paramètre sont renseignés, le lien sera en URL.
	 * @param string $root_dir Chemin du dossier racine.
	 * @param string $root_url Adresse URL du dossier racine.
	 * @return string Code HTMl du CSS.
	 */
	public function get_html($root_dir = NULL, $root_url = NULL) : string
	{
	    $links = $this->get_link_packages();
	    $html = '';
	    foreach ($links as $l)
	    {
	        if (empty($root_dir) == FALSE && empty($root_url) == FALSE)
	        {
	            $l = realpath($l);
	            $l = str_replace(DIRECTORY_SEPARATOR, '/', $l);
	            $l = str_replace($root_dir, $root_url, $l);
	        }
	        $html .= "<link rel='stylesheet' type='text/css' href='".$l."'/>\n";
	    }
	    return $html;
	}
}
?>