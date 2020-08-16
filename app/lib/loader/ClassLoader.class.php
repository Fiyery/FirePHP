<?php
/**
 * ClassLoader gère le chargement des classes ou interfaces de l'application.
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
	 * @var string[]
	 */
	private $_exts = [".php"];
	
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
	public function add_dir(string $dir)
	{
		if (is_string($dir) && file_exists($dir) && in_array($dir, $this->_dirs) === FALSE)
		{
			$this->_dirs[] = (substr($dir,-1) != "/") ? ($dir."/") : ($dir);
		}
	}

	/**
	 * Ajoute le dossier et les sous dossier récursivement.
	 * @param string $dir Chemin du dosier de classes.
	 * @param string[] $excluded Nom de dossier 
	 * @param int $depth Niveau des sous-dode sous dossier à ajouter
	 */
	public function add_dir_recursive(string $dir, array $excluded = [], int $depth = -1)
	{
		if (is_string($dir) && file_exists($dir))
		{
			$dir = (substr($dir,-1) != "/") ? ($dir."/") : ($dir);
			$this->_dirs[] = $dir;
			if ($depth != 0)
			{
				$dirs = array_diff(scandir($dir), ["..", "."]);
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
	 * @param string $exts Extention des classes.
	 */
	public function set_exts(array $exts = [".php"])
	{
		$this->_exts = [];
		foreach ($exts as $e) 
		{
			$this->_exts[] = (substr($e, 0, 1) != ".") ? (".".$e) : ($e);
		}
	}
	
	/**
	 * Active le chargement des classes.
	 */
	public function enable()
	{
		spl_autoload_register(array($this,"load"),TRUE);
	}
	
	/**
	 * Désactive le chargement des classes.
	 */
	public function disable()
	{
		spl_autoload_unregister(array($this,"load"));
	}
	
	/**
	 * Inclue les fichiers correspondant au pattern.
	 * @param string $file_pattern Pattern de la fonction glob().
	 * @return boolean TRUE si importation d'au moins un fichier.
	 */
	public function import(string $file_pattern)
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
	 * Charge une classe ou interface.
	 * @param string $name Nom de la classe ou interface.
	 * @return bool
	 */
	public function load(string $name) : bool
	{
		if (count($this->_dirs) === 0 || class_exists($name))
		{
			return FALSE;
		}
		$find = FALSE;
		$files = [];
		foreach ($this->_exts as $e) 
		{
			$files[] = strtolower($name.$e);
		}
		foreach ($this->_dirs as $dir)
		{
			$original = glob($dir."*");
			$dir = strtolower($dir);
			$dir_files = array_map("strtolower", $original);
			foreach ($files as $file)
			{
				if (($index = array_search($dir.$file, $dir_files)) !== FALSE)
				{
					include($original[$index]);
					$find = TRUE;
					break;
				}
			}
		}
		if ($find === FALSE)
		{
			throw new Exception("Class or interface \"" . $name . "\" not found");
		}
		return TRUE;
	}
}
?>