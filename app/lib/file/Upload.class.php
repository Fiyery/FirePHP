<?php
namespace FirePHP\File;

use FirePHP\Exception\UploadBadFileFormatException;
use FirePHP\Exception\UploadFileNotFoundException;
use FirePHP\Exception\UploadMoveFileException;
use FirePHP\Exception\UploadLimitSizeExceededException;

/**
* Upload manage le traitement du téléchargement de fichiers.
* @author Yoann Chaumin <yoann.chaumin@gmail.com>
* @uses File
* @uses FireException	
*/
class Upload
{
    /**
     * Liste des formats possibles.
     * @var string
     */
    const FILE_TYPE_IMAGE = "image";
    const FILE_TYPE_TEXT = "text";
    const FILE_TYPE_AUDIO = "audio";
    const FILE_TYPE_VIDEO = "video";
    const FILE_TYPE_APPLICATION = "application";
	
	/**
	 * Instance du fichier courant
	 * @var File
	 */
	protected $_file = NULL;
	
	/**
	 * Informations contenues dans $_FILES sur le fichier chargé.
	 * @var string[]
	 */
	protected $_file_data = [];

	/**
	 * Format du fichier attendu.
	 * @var string
	 */
	protected $_expected_file_type = NULL;
	
	/**
	 * Taille maximale du fichier à télécharger en octet.
	 * @var int
	 */
	protected $_authorised_size_max = -1;
	
	/**
	 * Liste des extensions autorisées pour le fichier.
	 * @var string[]
	 */
	protected $_authorised_exts = [];
	
	/**
	 * Taille maximale en pixel de la largeur du fichier image.
	 * @var int
	 */
	protected $_authorised_x_max = -1;
	
	/**
	 * Taille minimale en pixel de la largeur du fichier image.
	 * @var int
	 */
	protected $_authorised_x_min = -1;
	
	/**
	 * Taille maximale en pixel de la hauteur du fichier image.
	 * @var int
	 */
	protected $_authorised_y_max = -1;
	
	/**
	 * Taille minimale en pixel de la hauteur du fichier image.
	 * @var int
	 */
	protected $_authorised_y_min = -1;

	/**
	 * Constructeur.
	 * @param string $filename Chemin du fichier de base de données des types mimes et des extensions.
	 * @throws FireException
	 */
	public function __construct()
	{
        
	}

	/**
	 * Charge un fichier en effectuant les vérifications.
	 * @param string $name Nom du fichier à charger.
	 * @return Upload
	 */
	public function load(string $name) : Upload
	{
		$this->unload();
		$this->_check($name);
		return $this;
	}

	/**
	 * Décharge le fichier courrant.
	 * @return boolean
	 */
	public function unload() : bool
	{
		$this->_file = NULL;
		$this->_file_data = [];
		return TRUE;
	}

	/**
	 * Définie le type de fichier attendu.
	 * @param string $file_type
	 * @return Upload
	 */
	public function expect(string $file_type=NULL) : Upload
	{
		$this->_expected_file_type = $file_type;
		return $this;
	}
	
	/**
	 * Définie la taille maximale pour l'upload en octet.
	 * @param int $max Taille maximale en octet.
	 * @return Upload
	 */
	public function size(int $value=-1) : Upload
	{
		$this->_authorised_size_max = $value;
		return $this;
	}

	/**
	 * Définie les extensions de fichiers acceptées.
	 * @param string[] $exts Liste des extensions acceptées.
	 * @return Upload
	 */
	public function exts(array $exts=[]) : Upload
	{
		$this->_authorised_exts = array_map('strtolower', $exts);
		return $this;
	}
	
	/**
	 * Définie la taille maximale et minimale en largeur du fichier image.
	 * @param int $max Taille maximale en pixel.
	 * @param int $min Taille minimal en pixel.
	 * @return Upload
	 */
	public function width(int $max=-1, int $min=-1) : Upload
	{
		$this->_authorised_x_max = $max;
		$this->_authorised_x_min = $min;
		return $this;
	}
	
	/**
	 * Définie la taille maximale et minimale en hauteur du fichier image.
	 * @param int $max Taille maximale en pixel.
	 * @param int $min Taille minimal en pixel.
	 * @return Upload
	 */
	public function height(int $max=-1, int $min=-1) : Upload
	{
		$this->_authorised_y_max = $max;
		$this->_authorised_y_min = $min;
		return $this;
	}
	
	/**
	 * Vérifie si le fichier courant respecte les exigences définies.
	 * @param string $name Index du fichier dans $_FILES.
	 */
	protected function _check(string $name)
	{
		if (isset($_FILES[$name]) === FALSE)
		{
			throw new UploadFileNotFoundException("File index \"".$name."\" not found ");
		}

		if (is_readable($_FILES[$name]["tmp_name"]) === FALSE)
		{
			throw new UploadFileNotFoundException("File \"".$_FILES[$name]["tmp_name"]."\" not found ");
		}

		$this->_file = new File($_FILES[$name]["tmp_name"]);
		$this->_file_data = $_FILES[$name];

		$size = $this->_file->size();
		if ($this->_authorised_size_max > -1 && $size > $this->_authorised_size_max)
		{
			throw new UploadLimitSizeExceededException("File \"".$this->_file->path()."\" (".$size." octets) exceeds ".$this->_authorised_size_max." octets max size");
		}

		$name_tmp = strtolower($this->_file_data['name']);
		$pos = strrpos($name_tmp, '.');
		$ext = ($pos !== FALSE) ? (substr($name_tmp, $pos + 1)) : ($name_tmp);
		if (count($this->_authorised_exts) > 0 && in_array($ext, $this->_authorised_exts) === FALSE)
		{
			throw new UploadBadFileFormatException("File \"".$this->_file->path()."\" has unsupported extension \"".$ext."\"");	
		}

		$type = ($this->_file->type_mime()) ?: ($this->_file_data["type"]);
		$type = substr($type, 0, strpos($type, "/"));
		if ($this->_expected_file_type !== NULL && $this->_expected_file_type !== $type)
		{
			throw new UploadBadFileFormatException("File type \"".$type."\" for \"".$this->_file->path()."\" is not supported");
		}
	}
	
	/**
	 * Déplace le fichier temporaire courant vers son nouvelle emplacement renseigné par set_new_name.
	 * @param string $new_name Chemin de destination du nouveau fichier.
	 * @param boolean $erase Si TRUE, si un fichier existe déjà avec le même nom, il sera écrasé.
	 * @param boolean $force Si TRUE, si le chemin n'existe pas, il sera créé.
	 */
	public function move(string $new_name, bool $erase=FALSE, bool $force=FALSE) 
	{
		if (in_array(substr($new_name,-1), array('/','\\')) || empty($new_name))
		{
			$new_name .= $this->_file_data['name'];
		}
		if ($erase === FALSE && file_exists($new_name))
		{
			throw new UploadMoveFileException("File \"".$new_name."\" is already existed");
		}

		$dir = dirname($new_name);
		if (is_dir($dir) === FALSE)
		{
			if ($force === FALSE)
			{
				throw new UploadMoveFileException("Dir \"".$dir."\" not found");
			}
			else
			{
				if (mkdir($dir, 0755, TRUE) === FALSE)
				{
					throw new UploadMoveFileException("Fail to create dir \"".$dir."\"");
				}
			}
		}
		if (move_uploaded_file($this->_file->path(), $new_name) === FALSE)
		{
			throw new UploadMoveFileException("Fail to move temporary file to \"".$new_name."\"");
		}

		unset($this->_file);
		$this->_file = new File($new_name);
	}
	
	/**
	 * Retourne le fichier courant temporaire ou téléchargé.
	 * @return File
	 */
	public function file() : File
	{
	    return $this->_file;
	}
}
?>