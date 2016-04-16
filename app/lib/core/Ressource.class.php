<?php
/**
 * Ressource gère le traitement des fichiers externes à une page web tels que les fichiers CSS, JavaScript, ...
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @copyright 2011-2015 Yoann Chaumin
 * @uses Minifier
 * @uses Singleton
 */
class Ressource extends Singleton
{
	/**
	 * Variable d'instance de singleton.
	 * @var Ressource
	 */
	protected static $_instance = NULL;
	
	/**
	 * Dossier des ressources par défaut.
	 * @var string
	 */
	private static $_default_dir = './';
	
	/**
	 * Temps du cache en seconde.
	 * @var int
	 */
	private $_cache_time = 604800;
	
	/**
	 *  Dossier des ressources.
	 * @var string
	 */
	private $_dir = NULL;
	
	/**
	 * Content-Type des ressources.
	 * @var string
	 */
	private $_content_type = NULL;
	
	/**
	 * Extension du fichier
	 * @var string
	 */
	private $_ext = NULL;
	
	/**
	 * Liste des ressources.
	 * @var array<string>
	 */
	private $_list = NULL;
	
	/**
	 * Nom du fichier de la ressources.
	 * @var string
	 */
	private $_file = NULL;
	
	/**
	 * Liste des liens des packages de ressources.
	 * @var array<string>
	 */
	private $_links = NULL;
	
	/**	
	 * Définie si les ressources seront minifiées.
	 * @var bool
	 */
	private $_minify = TRUE;
	
	/**
	 * Constructeur.
	 * @param string $content_type Content-Type des ressources.
	 * @param string $ext Extension du fichier.
	 * @param string $dirname Chemin du dossier de sauvegarde.
	 */
	protected function __construct($content_type, $ext, $dirname)
	{
		$this->_list = array();
		$this->_links = array();
		$this->_content_type = $content_type;
		$this->_ext = $ext;
		if (file_exists($dirname) == FALSE)
		{
			if (mkdir($dirname,0755,TRUE) == FALSE)
			{
				throw new Exception("Invalid path for ressource directory");
			}
		}
		$this->_dir = (substr($dirname,-1) != '/') ? ($dirname.'/') : ($dirname);
	}
	
	/**
	 * Crée un nouveau package de ressources en lui spécifiant un nom.
	 * @param string $name Nom du package.
	 */
	public function create($name)
	{
		if (is_string($name) && preg_match("#^[a-zA-Z0-9._-]+$#", $name))
		{
			$this->_file = $name;
			$this->_list[$this->_file] = array();
		}
	}
	
	/**
	 * Sélectionne un package.
	 * @param string $name Nom du package.
	 * @return boolean
	 */
	public function select($name)
	{
		if (is_string($name) && array_key_exists($name, $this->_list))
		{
			$this->_file = $name;
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * Ajoute une ressource au package de ressources.
	 * @param string $link Lien du fichier à ajouter au package.
	 * @return boolean
	 */
	public function add($link)
	{
		$this->check();
		if (file_exists($link) && in_array($link,$this->_list[$this->_file]) == FALSE)
		{
			$this->_list[$this->_file][] = $link;
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * Définie un nouveau package à partir d'un dossier.
	 * @param string $name Nom du package associé.
	 * @param string $dir Chemin du dossier.
	 * @param array<string> $exts Liste des extensions à importer si renseigné.
	 * @return boolean
	 */
	public function add_package($name, $dir, $exts=NULL)
	{
		if (file_exists($dir))
		{
		    if ($this->select($name) == FALSE)
		    {
		        $this->create($name);
		    }
			$dir = (substr($dir,-1) != '/') ? ($dir.'/') : ($dir);
			$files = array_diff(scandir($dir), array('..','.'));
			if ($exts == NULL)
			{
			    foreach ($files as $f)
    			{
    			    $this->add($dir.$f);
    			}
			}
			elseif (is_array($exts))
			{
			    $exts = array_values($exts);
			    $max = count($exts);
			    foreach ($files as $f)
                {
                    $import = FALSE;
                    for($i=0; $i < $max && !$import; $i++)
                    {
                        $e = $exts[$i];
                        if (substr($f,-1*strlen($e)) == $e)
                        {
                            $import = TRUE;
                        }
                    }
                    if ($import)
                    {
                        $this->add($dir.$f);
                    }
                }
			}
			$this->_file = $name;
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * Supprime les ressources du package courant.
	 */
	public function clean()
	{
		$this->check();
		$this->_list[$this->_file] = array();
	}
	
	/**
	 * Supprime toutes les ressources.
	 */
	public function clean_all()
	{
		$this->_list = array();
	}
	
	/**
	 * Retourne les ressources du package courant.
	 * @return array<string> Liste des ressources du package courant.
	 */
	public function get_list()
	{
		return $this->_list[$this->_file];
	}
	
	/**
	 * Retourne les liens des packages.
	 * @return array<string> Liste des liens des packages.
	 */
	public function get_link_packages()
	{
		return $this->_links;
	}
	
	/**
	 * Récupère le contenu de toutes les ressources.
	 * @return string Le contenu fusionné de tous les fichiers.
	 */
	private function get_content()
	{
		$this->check();
		$content = '';
		foreach ($this->_list[$this->_file] as $r)
		{
			$content .= file_get_contents($r);
		}
		return $content;
	}

	/**
	 * Crée le package.
	 * @return string Chemin du package.
	 */
	public function get()
	{
	    $filename = $this->_dir.$this->_file.'.'.$this->_ext;
	    if (in_array($filename, $this->_links) == FALSE)
	    {
	        $content = $this->get_content();
	        if (empty($content) == FALSE)
	        {
	            if ($this->_minify)
	            {
	            	$content = Minifier::minify($this->_content_type, $content);
	            }
	            $begin = "<?php header('Content-Type:".$this->_content_type."');header('Cache-Control:max-age=".$this->_cache_time.", public');header('Last-Modified:".gmdate('D, d M Y H:i:s ',time())."GMT');header_remove('Pragma');if(!ob_start('ob_gzhandler'))ob_start();?>";
	            $end = "<?php ob_end_flush();?>";
	            file_put_contents($filename, $begin.$content.$end);
	            $this->_links[] = $filename;
	        }
	    }
		return $filename;
	}
	
    /**
     * Remonte un package dans la liste des priorités.
     * @param string $name Nom du package
     * @param int $num Nombre de places à remonter.
     * @return boolean
     */
	public function up($name, $num=1)
	{
	    if (array_key_exists($name, $this->_list) == FALSE)
	    {
	        return FALSE;
	    }
	    $i = 0;
	    while ((list($n, $v) = each($this->_list)) && $n != $name)
	    {
	        $i++;
	    }
	    reset($this->_list);
	    $pos = ($i - $num >= 0) ? ($i - $num) : (0);
	    $current = $this->_list[$name];
	    unset($this->_list[$name]);
	    $tmp = $this->_list;  
	    $this->_list = array();
	    $i = 0;
        while ((list($n, $v) = each($tmp)))
	    {
	        if ($i == $pos)
	        {
	            $this->_list[$name] = $current;
	        }
	        $this->_list[$n] = $v;
	        $i++;
	    }
        reset($this->_list);
        return TRUE;
	}
	
	/**
	 * Descend un package dans la liste des priorités.
	 * @param string $name Nom du package
	 * @param int $num Nombre de places à descendre.
	 * @return boolean
	 */
	public function down($name, $num=1)
	{
		if (array_key_exists($name, $this->_list) == FALSE)
		{
			return FALSE;
		}
		$i = 0;
		while ((list($n, $v) = each($this->_list)) && $n != $name)
		{
			$i++;
		}
		reset($this->_list);
		$count = count($this->_list);
		$pos = ($i + $num < $count) ? ($i + $num) : ($count - 1);
		$current = $this->_list[$name];
		unset($this->_list[$name]);
		$tmp = $this->_list;
		$this->_list = array();
		$i = 0;
		while ((list($n, $v) = each($tmp)))
		{
			$this->_list[$n] = $v;
			$i++;
			if ($i == $pos)
			{
				$this->_list[$name] = $current;
			}
		}
		reset($this->_list);
		return TRUE;
	}
	
	/**
	 * Vérifie si la ressource est utilisable.
	 * @throws RessourceException
	 */
	private function check()
	{
		if ($this->_file == NULL)
		{
		    $caller = Debug::get_caller(2);
			throw new RessourceException("Ressource invalide. La ressource doit être créée avant d'être utilisée", $caller['file'], $caller['line']);
		}
	}
	
	/**
	 * Définie le temps de conservation de la ressource en cache.
	 * @param int $seconds Temps en seconde.
	 */
	public function set_cache_time($seconds)
	{
		if (is_int($seconds))
		{
			$this->_cache_time = $seconds;
		}
	}
	
	/**
	 * Définie si les ressources seront minifiées.
	 * @param bool $bool
	 */
	public function enable_minification($bool)
	{
		$this->_minify = $bool;
	}
	
	/**
	 * Retourne une instance de Base avec les arguments correctement ordonnés selon le constructeur de la classe.
	 * @param array $args Tableau d'arguments du constructeur.
	 * @return Ressource
	 */
	protected static function __create($args)
	{
		return new self($args[0], $args[1]);
	}
}
?>