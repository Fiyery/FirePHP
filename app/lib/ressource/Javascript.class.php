<?php
/**
 * Javascript gère le traitement des fichiers JS.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses Ressource
 */
class Javascript extends Ressource
{	
	/**
	 * Nom du dossier JS.
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
	 * @param string $dirname Nom du dossier de destination des fichiers JS
	 * @param string $module Nom du module.
	 * @param string $action Nom de l'action.
	 */
	public function __construct($dirname)
	{
		parent::__construct('text/javascript', 'js', $dirname);
	}
	
	/**
	 * Définie un nouveau package à partir d'un dossier.
	 * @param string $name Nom du package associé.
	 * @param string $dir Chemin du dossier.
	 * @param string[] Liste des extensions à importer si renseigné. Par défaut, c'est ".js".
	 * @return boolean
	 */
	public function add_package($name, $dir, $exts=NULL)
	{
	    if ($exts == NULL)
	    {
	        $exts = array('.js');
	    }
	    return parent::add_package($name, $dir, $exts);
	}
	
	/**
	 * Retourne les liens JS sous forme de balises HTML. Si les deux paramètre sont renseignés, le lien sera en URL.
	 * @param string $root_dir Chemin du dossier racine.
	 * @param string $root_url Adresse URL du dossier racine.
	 * @return string Code HTMl du JS.
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
			$html .= "<script type='text/javascript' src='".$l."'></script>\n";
		}
		return $html;
	}
}
?>