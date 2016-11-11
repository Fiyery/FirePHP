<?php
/**
 * Css gère le traitement des fichiers CSS.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses Ressource
 */
class Css extends Ressource
{
	/**
	 * Variable d'instance de singleton.
	 * @var Css
	 */
	protected static $_instance = NULL;
	
	/**
	 * Nom du dossier CSS.
	 * @var string
	 */
	private $_dirname = NULL;
	
	/**
	 * Nom du module.
	 * @var string
	 */
	private $_module = NULL;
	
	/**
	 * Nom de l'action du module.
	 * @var string
	 */
	private $_action = NULL;

	/**
	 * Constructeur.
	 * @param string $dirname Nom du dossier de destination des fichiers CSS
	 * @param string $module Nom du module.
	 * @param string $action Nom de l'action.
	 */
	protected function __construct($dirname)
	{
		parent::__construct('text/css', 'css', $dirname);
	}
	
	/**
	 * Définie un nouveau package à partir d'un dossier.
	 * @param string $name Nom du package associé.
	 * @param string $dir Chemin du dossier.
	 * @param array<string> Liste des extensions à importer si renseigné. Par défaut, c'est ".css".
	 * @return boolean
	 */
	public function add_package($name, $dir, $exts=NULL)
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
	public function get_html($root_dir=NULL, $root_url=NULL)
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

	/**
	 * Retourne une instance de la classe avec les arguments correctement ordonnés selon le constructeur de la classe.
	 * @param array $args Tableau d'arguments du constructeur.
	 * @return Css
	 */
	protected static function __create($args)
	{
		return new self($args[0]);
	}
}
?>