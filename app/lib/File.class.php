<?php
/**
 * File est une classe qui rassemble des fonctions utiles sur les fichiers et dossiers.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses FireException
 */
class File 
{    
    /**
     * Chemin du fichier.
     * @var string
     */
    private $_file = NULL;
    
    /**
     * Type mime du fichier.
     * @var string
     */
    private $_type_mime = NULL;
    
    /**
     * Type du fichier parmi application, audio, image, text et video.
     * @var string
     */
    private $_type = NULL;
    
    /**
     * Taille en octet du fichier.
     * @var number
     */
    private $_size = -1;
    
    /**
     * Extension du fichier.
     * @var string
     */
    private $_ext = NULL;
    
    /**
     * Taille en pixel de la hauteur.
     * @var int
     */
    private $_height = -1;
    
    /**
     * Taille en pixel de la largeur.
     * @var int
     */
    private $_width = -1;
    
    /**
     * Constructeur
     * @param string $filname Chemin du fichier.
     */
    public function __construct($filename)
    {
        if (file_exists($filename) == FALSE || is_dir($filename) || is_readable($filename) == FALSE)
        {
            self::set_error("Invalid filename : it isn't a file");            
        }
        $this->_file = realpath($filename);
    }
    
    /**
     * Retourne le type mime du fichier.
     * @return string 
     */
    public function get_type_mime()
    {
        if (empty($this->_type_mime))
        {
            if (class_exists('finfo'))
            {
            	$finfo = new finfo(FILEINFO_MIME_TYPE);
            	$type = $finfo->file($this->_file);
            	$pos = strpos($type, ' ');
            	$this->_type_mime = ($pos !== FALSE) ? (substr($type, 0, $pos)) : ($type);
            }
            else
            {
            	exec('file -bi '.$this->_file, $out);
            	if (is_array($out) && isset($out[0]))
            	{
            	    $pos = strpos($out[0], ';');
            	    $this->_type_mime = ($pos !== FALSE) ? (substr($out[0], 0, $pos)) : ($out[0]);
            	}
            	else
            	{
            		$this->_type_mime = NULL;
            	}
            }
        }
    	return $this->_type_mime;
    }
    
    /**
     * Retourne la taille du fichier courant.
     * @return number Nombre d'octets.
     */
    public function get_size()
    {
    	if ($this->_size < 0)
    	{
    	    $this->_size = filesize($this->_file);
    	}
    	return $this->_size;
    }
    
    /**
     * Retourne l'extension du fichier.
     * @return string
     */
    public function get_ext()
    {
    	if (empty($this->_ext))
    	{
    	    $name = strtolower(basename($this->_file));
    	    $pos = strrpos($name, '.');
    	    $this->_ext = ($pos !== FALSE) ? (substr($name, $pos + 1)) : ($name);
    	}
        return $this->_ext;
    }
    
    /**
     * Retourne la hauteur du fichier si ce dernier est une image.
     * @return int
     */
    public function get_height()
    {
    	if ($this->is_image())
    	{
    		if ($this->_height < 0)
    	    {
    	        $res = getimagesize($this->_file);
    	        $this->_width = $res[0];
    	        $this->_height = $res[1];
    	    }
    	    return $this->_height;
    	}
    	return $this->_height;
    }
    
    /**
     * Retourne la largeur du fichier si ce dernier est une image.
     * @return int
     */
    public function get_width()
    {
    	if ($this->is_image())
    	{
    	    if ($this->_width < 0)
    	    {
    	        $res = getimagesize($this->_file);
    	        $this->_width = $res[0];
    	        $this->_height = $res[1];
    	    }
    	    return $this->_width;
    	}
    	return $this->_width;
    }
    
    /**
     * Récupère le type du fichier.
     * @return string
     */
    public function get_type()
    {
        if (empty($this->_type) == FALSE)
        {
        	return $this->_type;
        }
    	$pos = strpos($this->get_type_mime(), '/');
    	$this->_type = substr($this->_type_mime, 0, $pos);
    	return (empty($this->_type) == FALSE) ? ($this->_type) : (NULL);
    }
    
    /**
     * Récupère le chemin du fichier.
     * @return string
     */
    public function get_path()
    {
    	return $this->_file;
    }
    
    /**
     * Vérifie si le fichier est une image.
     * @return boolean
     */
    public function is_image()
    {
		return ($this->get_type() == 'image');		
    }
    
    /**
     * Vérifie si le fichier est un texte.
     * @return boolean
     */
    public function is_text()
    {
		return ($this->get_type() == 'text');
    }
    
    /**
     * Vérifie si le fichier est un audio.
     * @return boolean
     */
    public function is_audio()
    {
    	return ($this->get_type() == 'audio');
    }
    
    /**
     * Vérifie si le fichier est une vidéo.
     * @return boolean
     */
    public function is_video()
    {
    	return ($this->get_type() == 'video');
    }
    
    /**
     * Vérifie si le fichier est une application.
     * @return boolean
     */
    public function is_application()
    {
        return ($this->get_type() == 'application');
    }
    
    public function copy($new_name=NULL)
    {
        if (empty($new_name))
        {
            $new_name = substr($this->_file, 0, (strlen($this->get_ext()) + 1)* -1 ).'_';
            $i = 0;
            while (file_exists($new_name.$i.'.'.$this->_ext))
            {
                $i++;
            }
            $new_name = $new_name.$i.'.'.$this->_ext;
        }
        if (copy($this->_file, $new_name) == FALSE)
        {
            return NULL;
        }
        else
        {
            return (new self($new_name));
        }
    }
    
    /**
     * Redimensionne le fichier image.
     * @param int $x Taille maximale de la largeur en pixel.
     * @param int $y Taille maximale de la hauteur en pixel.
     * @param boolean $deform Si TRUE, l'image peut être déformée mais respectera strictement les dimensions.
     * @return boolean
     */
    public function resize($x, $y, $deform=FALSE)
    {
        if (is_numeric($x) == FALSE || is_numeric($y) == FALSE || is_bool($deform) == FALSE || $x <= 0 || $y <= 0)
        {
        	return FALSE;
        }
        if ($this->is_image() == FALSE || in_array($this->get_ext(),array('jpg','jpeg','png','gif')) == FALSE)
        {
        	return FALSE;
        }
        $image_x = $this->get_width();
        $image_y = $this->get_height();
        if ($deform == FALSE)
        {
        	if ($image_x > $x || $image_y > $y)
        	{
        		$coef_x = $image_x / $x;
        		$coef_y = $image_y / $y;
        		if ($coef_x >= $coef_y)
        		{
        			$y = floor($image_y / $coef_x);
        			$x = floor($image_x / $coef_x);
        		}
        		else
        		{
        			$x = floor($image_x/$coef_y);
        			$y = floor($image_y/$coef_y);
        		}
        	}
        	else
        	{
        		$x = $image_x;
        		$y = $image_y;
        	}
        		
        }
        $mime = $this->get_type_mime();
        if ($mime == 'image/jpg' || $mime == 'image/jpeg')
        {
        	$image = imagecreatefromjpeg($this->_file);
        	$miniature = imagecreatetruecolor($x,$y);
        	imagecopyresampled($miniature,$image,0,0,0,0,$x,$y,$image_x,$image_y);
        	imagejpeg($miniature, $this->_file);
        }
        elseif ($mime == 'image/png')
        {
        	$image = imagecreatefrompng($this->_file);
        	$miniature = imagecreatetruecolor($x,$y);
        	// Transparence.
        	imagealphablending($miniature, false);
        	imagesavealpha($miniature,true);
        	$transparent = imagecolorallocatealpha($miniature, 255, 255, 255, 127);
        	imagefilledrectangle($miniature, 0, 0, $x, $y, $transparent);
        	imagecopyresampled($miniature,$image,0,0,0,0,$x,$y,$image_x,$image_y);
        	imagesavealpha ($miniature, TRUE);
        	imagepng($miniature,$this->_file);
        }
        elseif ($mime == 'image/gif')
        {
        	$image = imagecreatefromgif($this->_file);
        	$miniature = imagecreatetruecolor($x,$y);
        	imagecopyresampled($miniature,$image,0,0,0,0,$x,$y,$image_x,$image_y);
        	imagegif($miniature,$this->_file);
        }
        else
        {
            return FALSE;
        }
        return TRUE;
    }
    
    /**
     * Génère une erreur.
     * @param string $msg Message de l'erreur.
     * @param int $level Nombre de fonction à remonter pour l'erreur.
     * @throws FireException
     */
    private static function set_error($msg, $level=1)
    {
        $d = debug_backtrace();
        $d = $d[$level];
        throw new FireException($msg, $d['file'], $d['line']);
    }
    
    /**
     * Reçoit une taille en octet et la retourne avec une unité plus adaptée.
     * @param int $size Taille en octet.
     * @return string Taille avec l'unité.
     */
    public static function format_size($size)
    {
    	if (is_numeric($size) == FALSE || $size < 0)
    	{
    	    return NULL;
    	}
    	if ($size == 0)
    	{
    		return '0 o';
    	}
	    $unit = array('o','Ko','Mo','Go', 'To');
	    $i = floor(log($size, 1024));
	    $size = round($size/pow(1024, $i), 2);
	    $size .= ' '.$unit[$i];
	    return $size;
    }
    
    /**
     * Compte le nombre de fichiers selon les extentions.
     * @param string $dir Nom dossier.
     * @param array<string> $exts Liste des extensions à compter.
     * @param boolean $subdir Si TRUE, la fonction ira récursivement dans les sous dossiers.
     * @return int 
     */
    public static function count($dir='.', $exts=array(), $subdir=TRUE)
    {
        $dir = (is_dir($dir)) ? ($dir) : ('.');
        $dir = (substr($dir, -1) == '/') ? ($dir) : ($dir.'/');
        $specified = (is_array($exts) && count($exts) > 0);
        $exts = array_map('strtolower', $exts);
        $nb_files = 0;
        $list_dirs = array($dir);
        $i = 0;
        while(isset($list_dirs[$i]))
        {
            $d = $list_dirs[$i];
            $list = array_diff(scandir($d), array('..', '.'));
            foreach ($list as $f)
            {
                if (is_dir($d.$f))
                {
                    $list_dirs[] = $d.$f.'/';
                }
                elseif ($specified)
                {
                    if (in_array(strtolower(substr(strrchr($f,"."), 1)), $exts))
                    {
                        $nb_files++;
                    }
                }
                else
                {
                    $nb_files++;
                }
                   
            }
            $i++;
        } 
        return $nb_files;
    }
    
    /**
     * Compte le nombre ligne des fichiers d'un répertoire selon les extentions.
     * @param string $dir Nom dossier.
     * @param array<string> $exts Liste des extensions à prendre en compte.
     * @param boolean $subdir Si TRUE, la fonction ira récursivement dans les sous dossiers.
     * @return int
     */
    public static function count_line($dir='.', $exts=array(), $subdir=TRUE)
    {
    	$dir = (is_dir($dir)) ? ($dir) : ('.');
    	$dir = (substr($dir, -1) == '/') ? ($dir) : ($dir.'/');
    	$specified = (is_array($exts) && count($exts) > 0);
    	$exts = array_map('strtolower', $exts);
    	$nb_lines = 0;
    	$list_dirs = array($dir);
    	$i = 0;
    	while(isset($list_dirs[$i]))
    	{
    		$d = $list_dirs[$i];
    		$list = array_diff(scandir($d), array('..', '.'));
    		foreach ($list as $f)
    		{
    			if (is_dir($d.$f))
    			{
    				$list_dirs[] = $d.$f.'/';
    			}
    			elseif ($specified)
    			{
    				if (in_array(strtolower(substr(strrchr($f,"."), 1)), $exts))
    				{
    					$nb_lines += count(file($d.$f));
    				}
    			}
    			else
    			{
    				$nb_lines += count(file($d.$f));
    			}
    			 
    		}
    		$i++;
    	}
    	return $nb_lines;
    }
}
?>