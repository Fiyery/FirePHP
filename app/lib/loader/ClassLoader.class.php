<?php
/**
 * ClassLoader gère le chargement des classe du site.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 */
class ClassLoader
{	
	/**
	 * Tableau des localisations des fichiers des classes.
	 * @var string[]
	 */
	private $_dirs = array();	
	
	/**
	 * Extention des fichiers classe.
	 * @var sting
	 */
	private $_ext = '.php';
	
	/**
	 * Constructeur.
	 */
	public function __construct()
	{
		
	}
	
	/**
	 * Ajoute un nouveau dossier de classes.
	 * @param string $dir Chemin du dosier de classes.
	 */
	public function add_dir($dir)
	{
		if (is_string($dir) && file_exists($dir) && in_array($dir, $this->_dirs) === FALSE)
		{
			$this->_dirs[] = (substr($dir,-1) != '/') ? ($dir.'/') : ($dir);
		}
	}

	/**
	 * Ajoute le dossier et les sous dossier récursivement.
	 * @param string $dir Chemin du dosier de classes.
	 * @param string[] $excluded Nom de dossier 
	 * @param int $depth Niveau des sous-dode sous dossier à ajouter
	 */
	public function add_dir_recursive($dir, $excluded=[], $depth=-1)
	{
		if (is_string($dir) && file_exists($dir))
		{
			$dir = (substr($dir,-1) != '/') ? ($dir.'/') : ($dir);
			$this->_dirs[] = $dir;
			if ($depth != 0)
			{
				$dirs = array_diff(scandir($dir), ['..', '.']);
				foreach ($dirs as $d)
				{
					if (is_dir($dir.$d) && in_array($d, $excluded) === FALSE)
					{
						$this->add_dir_recursive($dir.$d, $excluded, $depth-1);
					}
				}
			}
		}
	}
	
	/**
	 * Définie l'extention des fichiers.
	 * @param string $ext Extention des classes.
	 */
	public function set_ext($ext='php')
	{
		$this->_ext = (substr($ext, 0, -1) != '.') ? ('.'.$ext) : ($ext);
	}
	
	/**
	 * Active le chargement des classes.
	 */
	public function enable()
	{
		spl_autoload_register(array($this,'load'),TRUE);
	}
	
	/**
	 * Désactive le chargement des classes.
	 */
	public function disable()
	{
		spl_autoload_unregister(array($this,'load'));
	}
	
	/**
	 * Inclue les fichiers correspondant au pattern.
	 * @param string $file_pattern Pattern de la fonction glob().
	 * @return boolean TRUE si importation d'au moins un fichier.
	 */
	public function import($file_pattern)
	{
		$file = glob($file_pattern);
		if (is_array($file) && count($file) > 0)
		{
			foreach ($file as $f)
			{
				if (is_file($f))
				{
					require($f);
				}
			}
			return TRUE;
		}
		else 
		{
			return FALSE;
		}
	}
	
	/**
	 * Change une classe.
	 * @param string $name Nom de la classe.
	 * @return boolean
	 */
	public function load($name)
	{
		if (count($this->_dirs) === 0 || class_exists($name))
		{
			return FALSE;
		}
		$find = FALSE;
		$file = $name.$this->_ext;
		reset($this->_dirs);
		while ($find === FALSE && ($dir = current($this->_dirs)))
		{
			if (file_exists($dir.$file))
			{
				include($dir.$file);
				$find = TRUE;
			}
			next($this->_dirs);
		}
		return TRUE;
	}
}

?>