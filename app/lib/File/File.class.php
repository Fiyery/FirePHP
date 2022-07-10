<?php
namespace FirePHP\File;

use finfo;

/**
 * File est une classe qui rassemble des fonctions utiles sur les fichiers et dossiers.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
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
    public function __construct(string $filename)
    {
        $this->_file = (realpath($filename)) ?: ($filename);
    }

    /**
     * Vérifie si le fichier exists.
     * @return bool
     */
    public function is_exists() : bool
    {
        return file_exists($this->_file);
    }

    /**
     * Vérifie si le fichier est lisible.
     * @return bool
     */
    public function is_readable() : bool
    {
        return is_readable($this->_file);
    }
    
    /**
     * Retourne le type mime du fichier.
     * @return string 
     */
    public function type_mime() : ?string
    {
        if ($this->_type_mime === NULL)
        {
            if (class_exists("finfo"))
            {
            	$finfo = new finfo(FILEINFO_MIME_TYPE);
            	$type = $finfo->file($this->_file);
            	$pos = strpos($type, " ");
            	$this->_type_mime = ($pos !== FALSE) ? (substr($type, 0, $pos)) : ($type);
            }
            else
            {
            	exec("file -bi ".$this->_file, $out);
            	if (is_array($out) && isset($out[0]))
            	{
            	    $pos = strpos($out[0], ";");
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
     * @return int Nombre d'octets.
     */
    public function size() : int
    {
		if ($this->_size < 0)
		{
			$size = "".filesize($this->_file);
			if ($size < 0)
			{
				$file = fopen($this->_file, 'rb');
				if ($file === FALSE)
				{
					return FALSE;
				}
				$size = PHP_INT_MAX - 1;
				if (fseek($file, PHP_INT_MAX - 1) !== 0)
				{
					fclose($file);
					return FALSE;
				}
				$length = 1024 * 1024;
				while (feof($file) === FALSE)
				{
					$read = fread($file, $length);
					$size = bcadd($size, $length);
				}
				$size = bcsub($size, $length);
				$size = bcadd($size, strlen($read));
				fclose($file);
			}
			$this->_size = $size;
    	}
    	return $this->_size;
    }
    
    /**
     * Retourne l'extension du fichier.
     * @return string
     */
    public function ext() : string
    {
    	if (empty($this->_ext))
    	{
    	    $name = strtolower(basename($this->_file));
    	    $pos = strrpos($name, ".");
    	    $this->_ext = ($pos !== FALSE) ? (substr($name, $pos + 1)) : ($name);
    	}
        return $this->_ext;
    }
    
    /**
     * Retourne la hauteur du fichier si ce dernier est une image.
     * @return int
     */
    public function height() : int
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
    public function width() : int
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
    public function type() : string
    {
        if (empty($this->_type) == FALSE)
        {
        	return $this->_type;
        }
    	$pos = strpos($this->type_mime(), "/");
    	$this->_type = substr($this->_type_mime, 0, $pos);
    	return (empty($this->_type) == FALSE) ? ($this->_type) : (NULL);
    }
    
    /**
     * Récupère le chemin du fichier.
     * @return string
     */
    public function path() : string
    {
    	return $this->_file;
    }
    
    /**
     * Vérifie si le fichier est une image.
     * @return bool
     */
    public function is_image() : bool
    {
		return ($this->type() == "image");		
    }
    
    /**
     * Vérifie si le fichier est un texte.
     * @return bool
     */
    public function is_text()
    {
		return ($this->type() == "text");
    }
    
    /**
     * Vérifie si le fichier est un audio.
     * @return bool
     */
    public function is_audio() : bool
    {
    	return ($this->type() == "audio");
    }
    
    /**
     * Vérifie si le fichier est une vidéo.
     * @return bool
     */
    public function is_video() : bool
    {
    	return ($this->type() == "video");
    }
    
    /**
     * Vérifie si le fichier est une application.
     * @return bool
     */
    public function is_application() : bool
    {
        return ($this->type() == "application");
    }
    
    /**
     * Copie le fichier dans un autre emplacement.
     * @param string $new_name 
     * @return File
     */
    public function copy(string $new_name=NULL) : File
    {
        if (empty($new_name))
        {
            $new_name = substr($this->_file, 0, (strlen($this->ext()) + 1)* -1 )."_";
            $i = 0;
            while (file_exists($new_name.$i.".".$this->_ext))
            {
                $i++;
            }
            $new_name = $new_name.$i.".".$this->_ext;
        }
        if (copy($this->_file, $new_name) === FALSE)
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
        if (is_numeric($x) === FALSE || is_numeric($y) === FALSE || is_bool($deform) === FALSE || $x <= 0 || $y <= 0)
        {
        	return FALSE;
        }
        if ($this->is_image() === FALSE || in_array($this->ext(),array("jpg","jpeg","png","gif", "webp")) === FALSE)
        {
        	return FALSE;
        }
        $image_x = $this->width();
        $image_y = $this->height();
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
        $mime = $this->type_mime();
        if ($mime === "image/jpg" || $mime === "image/jpeg")
        {
        	$image = imagecreatefromjpeg($this->_file);
        	$miniature = imagecreateTRUEcolor($x, $y);
        	imagecopyresampled($miniature, $image, 0, 0, 0, 0, $x, $y, $image_x, $image_y);
        	imagejpeg($miniature, $this->_file);
        }
        elseif ($mime == "image/png")
        {
        	$image = imagecreatefrompng($this->_file);
        	$miniature = imagecreateTRUEcolor($x, $y);
        	// Transparence.
        	imagealphablending($miniature, FALSE);
        	imagesavealpha($miniature, TRUE);
        	$transparent = imagecolorallocatealpha($miniature, 255, 255, 255, 127);
        	imagefilledrectangle($miniature, 0, 0, $x, $y, $transparent);
        	imagecopyresampled($miniature,$image,0,0,0,0,$x,$y,$image_x,$image_y);
        	imagesavealpha ($miniature, TRUE);
        	imagepng($miniature,$this->_file);
        }
        elseif ($mime == "image/gif")
        {
        	$image = imagecreatefromgif($this->_file);
        	$miniature = imagecreateTRUEcolor($x,$y);
        	imagecopyresampled($miniature,$image,0,0,0,0,$x,$y,$image_x,$image_y);
        	imagegif($miniature,$this->_file);
        }
		elseif ($mime === "image/webp")
		{
			$image = imagecreatefromwebp($this->_file);
        	$miniature = imagecreateTRUEcolor($x, $y);
        	imagecopyresampled($miniature, $image, 0, 0, 0, 0, $x, $y, $image_x, $image_y);
        	imagewebp($miniature, $this->_file);
		}
        else
        {
            return FALSE;
        }
        return TRUE;
    }
    
    /**
     * Reçoit une taille en octet et la retourne avec une unité plus adaptée.
     * @param float $size Taille en octet.
     * @return string Taille avec l'unité.
     */
    public static function format_size(float $size) : string
    {
    	if (is_numeric($size) == FALSE || $size < 0)
    	{
    	    return NULL;
    	}
    	if ($size == 0)
    	{
    		return "0 o";
    	}
	    $unit = array("o","Ko","Mo","Go", "To");
	    $i = floor(log($size, 1024));
	    $size = round($size/pow(1024, $i), 2);
	    $size .= " ".$unit[$i];
	    return $size;
    }
}
?>