<?php
/**
* Upload manage le traitement du téléchargement de fichiers.
* @author Yoann Chaumin <yoann.chaumin@gmail.com>
* @copyright 2011-2015 Yoann Chaumin
* @uses Singleton
* @uses File
* @uses FireException
*/
class Upload extends Singleton
{
    /**
     * Liste des erreurs possibles d'un upload.
     * @var string
     */
    const ERR_FILE_NOT_FOUND = 1;
    const ERR_FILE_MAX_SIZE = 2;
    const ERR_FILE_MIN_SIZE = 3;
    const ERR_FILE_FORMAT = 4;
    const ERR_FILE_WIDTH = 5;
    const ERR_FILE_HEIGHT = 6;
    const ERR_FILE_EXISTS = 7;
    const ERR_DIR_INVALID = 8;
    const ERR_MOVE_FILE = 9;
    const ERR_FILE_NOT_IMAGE = 10;
    
	/**
	 * Variable de singleton.
	 * @var Upload
	 */
	protected static $_instance = NULL;

	/**
	 * Chemin de la base de données.
	 * @var string
	 */
	private $_db_name = NULL;
	
	/**
	 * Liste des types mimes et leurs extensions.
	 * @var array<string>
	 */
	private $_db = array();
	
	/**
	 * Instance du fichier courant
	 * @var File
	 */
	private $_file = NULL;
	
	/**
	 * Informations contenues dans $_FILES sur le fichier chargé.
	 * @var array<string>
	 */
	private $_file_data = array();
	
	/**
	 * Taille maximale du fichier à télécharger en octet.
	 * @var int
	 */
	private $_authorised_size_max = -1;
	
	/**
	 * Taille minimale du fichier à télécharger en octet.
	 * @var int
	 */
	private $_authorised_size_min = -1;
	
	/**
	 * Liste des extensions autorisées pour le fichier.
	 * @var array<string>
	 */
	private $_authorised_exts = array();
	
	/**
	 * Taille maximale en pixel de la largeur du fichier image.
	 * @var int
	 */
	private $_authorised_x_max = -1;
	
	/**
	 * Taille minimale en pixel de la largeur du fichier image.
	 * @var int
	 */
	private $_authorised_x_min = -1;
	
	/**
	 * Taille maximale en pixel de la hauteur du fichier image.
	 * @var int
	 */
	private $_authorised_y_max = -1;
	
	/**
	 * Taille minimale en pixel de la hauteur du fichier image.
	 * @var int
	 */
	private $_authorised_y_min = -1;
	
	/**
	 * Dernière erreur générée.
	 * @var array<string>
	 */
	private $_error = array();
	
	/**
	 * Liste des erreurs récupérables lors du téléchargzement d'un fichier.
	 * @var array
	 */
	private $_list_errors = array(
		1 => 'Failed to load file',
		2 => 'File is bigger than max limit size',
		3 => 'File is smaller than min limit size',
		4 => 'Type of file is invalide',
		5 => 'Width of image is bigger or smaller than limit',
		6 => 'height of image is bigger or smaller than limit',
		7 => 'A file already exists',
		8 => 'Dir is invalid or unexisted',
		9 => 'File isn\'t move correctly',
		10 => 'File isn\'t image or can\'t be resized'
	);
	
	/**
	 * Constructeur.
	 * @param string $filename Chemin du fichier de base de données des types mimes et des extensions.
	 * @throws FireException
	 */
	protected function __construct($filename)
	{
        if (is_file($filename) == FALSE || is_readable($filename) == FALSE)
        {
           $d = debug_backtrace();
           $d = $d[1];
           throw new FireException("Liste des types mime introuvable", $d['file'], $d['line']);
        }
        $this->_db_name = $filename;
        $this->_db = json_decode(file_get_contents($this->_db_name));
        if (empty($this->_db))
        {
            $d = debug_backtrace();
            $d = $d[1];
            throw new FireException("Liste des types mime invalide", $d['file'], $d['line']);
        }
        $this->_db = get_object_vars($this->_db);
	}

	/**
	 * Charge un fichier en effectuant les vérifications.
	 * @param string $name Nom du fichier à charger.
	 * @return boolean
	 */
	public function load($name)
	{
		$this->unload();
		$this->_error = NULL;
		if (isset($_FILES[$name]) && file_exists($_FILES[$name]['tmp_name']))
		{
			$this->_file = new File($_FILES[$name]['tmp_name']);
			$this->_file_data = $_FILES[$name];
			return TRUE;
		}
		else
		{
			$this->set_error(self::ERR_FILE_NOT_FOUND);
			return FALSE;
		}
	}

	/**
	 * Décharge le fichier courrant.
	 * @return boolean
	 */
	public function unload()
	{
		$this->_file = NULL;
		$this->_file_data = array();
		return TRUE;
	}
	
	/**
	 * Vérifie si un fichier est chargé.
	 * @return boolean
	 */
	public function is_load()
	{
	    return (is_object($this->_file));
	}
	
	/**
	 * Définie la taille maximale et minimale pour l'upload en octet.
	 * @param int $max Taille maximale en octet.
	 * @param int $min Taille minimale en octet.
	 */
	public function set_size($max=-1, $min=-1)
	{
		if (is_numeric($max))
		{
			$this->_authorised_size_max = $max;
		}
		if (is_numeric($min))
		{
			$this->_authorised_size_min = $min;
		}
	}

	/**
	 * Définie les extensions de fichiers acceptées.
	 * @param array<string> $exts Liste des extensions acceptées.
	 */
	public function set_exts($exts=array())
	{
		if (is_array($exts))
		{
			$this->_authorised_exts = $exts;
		}
	}
	
	/**
	 * Définie la taille maximale et minimale en largeur du fichier image.
	 * @param int $max Taille maximale en pixel.
	 * @param int $min Taille minimal en pixel.
	 */
	public function set_width($max=-1, $min=-1)
	{
		if (is_numeric($max))
		{
			$this->_authorised_x_max = $max;
		}
		if (is_numeric($min))
		{
			$this->_authorised_x_min = $min;
		}
	}
	
	/**
	 * Définie la taille maximale et minimale en hauteur du fichier image.
	 * @param int $max Taille maximale en pixel.
	 * @param int $min Taille minimal en pixel.
	 */
	public function set_height($max=-1, $min=-1)
	{
		if (is_numeric($max))
		{
			$this->_authorised_y_max = $max;
		}
		if (is_numeric($min))
		{
			$this->_authorised_y_min = $min;
		}
	}
	
	/**
	 * Vérifie si le fichier courant respecte les exigences définies.
	 * @return boolean
	 */
	public function check()
	{
	    if ($this->is_load() == FALSE)
		{
		    $this->set_error(self::ERR_FILE_NOT_FOUND);
		    $path = $this->_file->get_path();
            unset($path);
			return FALSE;
		}
		
		$size = $this->_file->get_size();
		if ($this->_authorised_size_max > -1 && $size > $this->_authorised_size_max)
		{
			$this->set_error(self::ERR_FILE_MAX_SIZE);
			$path = $this->_file->get_path();
            unset($path);
			return FALSE;
		}
		if ($this->_authorised_size_min > -1 && $size < $this->_authorised_size_min)
		{
			$this->set_error(self::ERR_FILE_MIN_SIZE);
			$path = $this->_file->get_path();
            unset($path);
			return FALSE;
		}
		
		$name = strtolower($this->_file_data['name']);
		$pos = strrpos($name, '.');
		$ext = ($pos !== FALSE) ? (substr($name, $pos + 1)) : ($name);
		$type_mime = $this->_file->get_type_mime();
		if (empty($type_mime) == FALSE)
		{
		    $type_mime = $this->_file_data['type'];
		}
		
		if (empty($this->_db_name) == FALSE)
		{
		    if (isset($this->_db[$type_mime]) == FALSE || in_array($ext, $this->_db[$type_mime]) == FALSE)
		    {
		        $this->set_error(self::ERR_FILE_FORMAT);
		        $path = $this->_file->get_path();
                unset($path);
		        return FALSE;
		    }
		}
		
		if (count($this->_authorised_exts) > 0 && in_array($ext, $this->_authorised_exts) == FALSE)
		{
			$this->set_error(self::ERR_FILE_FORMAT);
			$path = $this->_file->get_path();
            unset($path);
			return FALSE;
		}
		
		if ($this->_file->is_image())
		{
			if ($this->_authorised_x_max > -1 && $this->_file->get_width() > $this->_authorised_x_max)
			{
				$this->set_error(self::ERR_FILE_WIDTH);
				$path = $this->_file->get_path();
                unset($path);
				return FALSE;
			}
			if ($this->_authorised_x_min > -1 && $this->_file->get_width() < $this->_authorised_x_min)
			{
				$this->set_error(self::ERR_FILE_WIDTH);
				$path = $this->_file->get_path();
                unset($path);
				return FALSE;
			}
			if ($this->_authorised_y_max > -1 && $this->_file->get_height() > $this->_authorised_y_max)
			{
				$this->set_error(self::ERR_FILE_HEIGHT);
				$path = $this->_file->get_path();
                unset($path);
				return FALSE;
			}
			if ($this->_authorised_y_min > -1 && $this->_file->get_height() < $this->_authorised_y_min)
			{
				$this->set_error(self::ERR_FILE_HEIGHT);
				$path = $this->_file->get_path();
                unset($path);
				return FALSE;
			}
		}
		return TRUE;
	}
	
	/**
	 * Déplace le fichier temporaire courant vers son nouvelle emplacement renseigné par set_new_name.
	 * @param string $new_name Chemin de destination du nouveau fichier.
	 * @param boolean $erase Si TRUE, si un fichier existe déjà avec le même nom, il sera écrasé.
	 * @param boolean $force Si TRUE, si le chemin n'existe pas, il sera créé.
	 * @return boolean
	 */
	public function move($new_name, $erase=FALSE, $force=FALSE)
	{
		if (in_array(substr($new_name,-1), array('/','\\')) || empty($new_name))
		{
			$new_name .= $this->_file_data['name'];
		}
		if ($erase == FALSE && file_exists($new_name))
		{
			$this->set_error(self::ERR_FILE_EXISTS);
			return FALSE;
		}
		$dir = dirname($new_name);
		if (is_dir($dir) == FALSE)
		{
			if ($force == FALSE)
			{
				$this->set_error(self::ERR_DIR_INVALID);
				return FALSE;
			}
			else
			{
				if (mkdir($dir, 0755, TRUE) == FALSE)
				{
					$this->set_error(self::ERR_DIR_INVALID);
					return FALSE;
				}
			}
		}
		if (move_uploaded_file($this->_file->get_path(), $new_name) && is_file($new_name))
		{
			unset($this->_file);
			$this->_file = new File($new_name);
			return TRUE;
		}
		else
		{
			$this->set_error(self::ERR_MOVE_FILE);
			return FALSE;
		}
	}
	
	/**
	 * Définie la dernière erreur.
	 * @param int $code Code de l'erreur parmi les constantes.
	 */
	private function set_error($code)
	{
		$this->_error = array($code => $this->_list_errors[$code]);
	}
	
	/**
	 * Définie la dernière erreur.
	 * @return array Tableau contenant le code de l'erreur et son message.
	 */
	public function get_error()
	{
		return $this->_error;
	}

	/**
	 * Effectue un raccourcie appel de fonction pour le téléchargement de fichier.
	 * @param string $index Identifiant de l'index dans la variable globale $_FILES.
	 * @param string $new_name Nouveau chemin et non du fichier après le téléchargement
	 * @param boolean $erase Si TRUE, un fichier porte le même nom, il sera écrasé.
	 * @param boolean $force Si TRUE, le dossier sera créer s'il n'existe pas.
	 * @return boolean
	 */
	public function import($index, $new_name, $erase=FALSE, $force=FALSE)
	{
		return ($this->load($index) && $this->check() && $this->move($new_name, $erase, $force));
	}
	
	/**
	 * Retourne le fichier courant temporaire ou téléchargé.
	 * @return File
	 */
	public function get_file()
	{
	    return $this->_file;
	}
	
    /**
	 * Retourne une instance de Base avec les arguments correctement ordonnés selon le constructeur de la classe.
	 * @param array $args Tableau d'arguments du constructeur.
	 * @return Upload
	 */
	protected static function __create($args)
	{
		return new self($args[0]);
	} 
}
?>