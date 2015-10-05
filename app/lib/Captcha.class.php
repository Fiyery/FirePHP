<?php
/**
 * Captcha est une classe qui génère des images type captcha. 
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @copyright 2011-2015 Yoann Chaumin
 * @uses Session
 */
class Captcha
{
	/**
	 * Chemin du dossier de stockage des captchas.
	 * @var string
	 */
	private static $_dir = NULL;
	
	/**
	 * 
	 * @var string
	 */
	private $_word;
	
	/**
	 * Nombre de caractères dans le captcha.
	 * @var int
	 */
	private $_length = 7;
	
	/**
	 * Couleur du captcha.
	 * @var string
	 */
	private $_color;
	
	/**
	 * Taille de la police des caractères du captcha.
	 * @var int
	 */
	private $_size = 7;
	
	/**
	 * Hash du mot contenu par le captcha.
	 * @var string
	 */
	private $_hash = NULL;

	/**
	 * Largeur du captcha.
	 * @var int
	 */
	private $_width = 210;
	
	/**
	 * Hauteur du captcha.
	 * @var int
	 */
	private $_height = 50;
	
	/**
	 * Dossier des fonts du captcha.
	 * @var string
	 */
	private static $_dir_backgrounds = NULL;
	
	/**
	 * Dossier des polices du captcha.
	 * @var string
	 */
	private static $_dir_fonts = NULL;
	
	/**
	 * Angle minimal de rotation.
	 * @var int
	 */
	private static $_rotation_min = 0;
	
	/**
	 * Angle maximal de rotation.
	 * @var int
	 */
	private static $_rotation_max = 0;
	
	
	/**
	 * Constructeur.
	 */
	public function __construct()
	{
		$this->get_word();
		$this->_color = array(0,0,0);
	}
	
	/**
	 * Définie le nombre de caractères du captcha.
	 * @param int $nb Nombre de caractères du captcha.
	 * @return Captcha|boolean Si le paramètre est bien un int, cette fonction retourne l'instance, sinon FALSE.
	 */
	public function set_length($nb=7)
	{
		if (is_int($nb))
		{
			$this->_length = $nb;
			return $this;
		}
		return FALSE;
	}
	
	/**
	 * Définie la couleur de la police.
	 * @param string $color Couleur de la police.
	 * @return Captcha|boolean Si le paramètre est bien une couleur, cette fonction retourne l'instance, sinon FALSE.
	 */
	public function set_color($color)
	{
		if (is_string($color))
		{
			$color = Toolbox::hex_to_rvb($color);
		}
		if (count($color) == 3)
		{
			$valid = TRUE;
			foreach ($color as $c)
			{
				if (is_numeric($c) == FALSE || $c > 255 || $c < 0)
				{
					$valid = FALSE;
				}
			}
			if ($valid)
			{
				$this->_color = $color;
				return $this;
			}
		}
		
		return FALSE;
	}
	
	/**
	 * Définie la largeur du captcha.
	 * @param int $width Largeur du captcha.
	 * @return Captcha|boolean Si la largeur est bien un int, cette fonction retourne l'instance, sinon FALSE.
	 */
	public function set_width($width)
	{
		if (is_numeric($width))
		{
			$this->_width = $width;
			return $this;
		}
		return FALSE;
	}
	
	/**
	 * Définie la hauteur du captcha.
	 * @param int $height Hauteur du captcha.
	 * @return Captcha|boolean Si la hauteur est bien un int, cette fonction retourne l'instance, sinon FALSE.
	 */
	public function set_height($height)
	{
		if (is_numeric($height))
		{
			$this->_height = $height;
			return $this;
		}
		return FALSE;
	}
	
	/**
	 * Définie la taille de la police.
	 * @param int $nb Taille de la police en pixel.
	 * @return Captcha|boolean Si le paramètre est bien un int, cette fonction retourne l'instance, sinon FALSE.
	 */
	public function set_size($nb=7)
	{
		if (is_int($nb))
		{
			$this->_size = $nb;
			return $this;
		}
		return FALSE;
	}
	
	/**
	 * Génère des caractères aléatoires pour le captcha.
	 * @return string Caractères aléatoires.
	 */
	private function get_word()
	{
		$letters = array_merge(range('a','z'),range('A','Z'),range('0','9'));
		$word = '';
		for($i=0;$i<$this->_length;$i++)
		{
			$word .= $letters[array_rand($letters)];
		}
		$this->_word = $word;
		$this->save();
		return $this->_word;
	}
	
	/**
	 * Génère l'image du captcha.
	 * @param boolean $show Si TRUE, le captcha sera affiché directement sinon, il sera sauvegardé dans le dossier des captchas.
	 */
	public function get_captcha($show=FALSE)
	{
		$image = imagecreate($this->_width,$this->_height);
		$white = imagecolorallocate($image, 255, 255, 255);
		$colors = $this->get_colors($image);
		$fonts = $this->get_fonts();
		$image = $this->get_background($image);
		$x = 10;
		$y = 33;
		$letters = str_split($this->_word);
		foreach ($letters as $w)
		{
			$c = $colors[array_rand($colors)];
			if (count($fonts) == 0)
			{
				imagechar($image,5,$x,$y,$w,$c);
			}
			else 
			{
				$f = $fonts[array_rand($fonts)];
				$a = rand(self::$_rotation_min,self::$_rotation_max);
				imagettftext($image,18, $a, $x, $y, $c, $f, $w);
			}
			
			$x += 28;
		}
		if ($show)
		{
			header ("Content-type: image/png");
			imagepng($image);
		}
		else
		{
			imagepng($image,self::$_dir.'captcha-'.md5($this->_word).'.png');
		}
		imagedestroy($image);
	}

	/**
	 * Sauvegarde en session le mot du captcha pour la vérification.
	 */
	private function save()
	{
		Session::get_instance()->__captcha = $this;
	}

	/**
	 * Génère un ensemble de couleurs pour la police du captcha.
	 * @param resource $image Image du captcha.
	 * @return array<
	 */
	private function get_colors($image)
	{
		$colors = array();
		for($r=0;$r<220;$r+=75)
		{
			for($v=0;$v<220;$v+=75)
			{
				for($b=0;$b<220;$b+=75)
				{
					$colors[] = imagecolorallocate($image, $r, $v, $b);
				}
			}
		}
		return $colors;
	}
	
	/**
	 * Ajoute un font au captcha.
	 * @param ressource $image Image dynamique.
	 * @return ressource Image dynamique avec fond.
	 */
	private function get_background($image)
	{
		if (self::$_dir_backgrounds !== NULL)
		{
			$list = glob(self::$_dir_backgrounds.'*.jpg');
			if (is_array($list) && count($list) > 0)
			{
				$background = $list[array_rand($list)];
				$background = imagecreatefromjpeg($background);
				imagecopyresampled($image, $background, 0, 0, 0, 0, $this->_width, $this->_height, $this->_width, $this->_height);
				imagedestroy($background);
			}
		}
		return $image;
	}
	
	/**
	 * Retourne un tableau de fonts.
	 * @return array<string> Tableau de fonts.
	 */
	private function get_fonts()
	{
		$fonts = array();
		if (self::$_dir_fonts !== NULL)
		{
			$fonts = glob(self::$_dir_fonts.'*.ttf');
			if (is_array($fonts) == FALSE)
			{
				$fonts = array();
			}
		}
		return $fonts;
	}
	
	/**
	 * Vérifie si le texte entré correspond au captcha.
	 * @return boolean TRUE si le texte correspond au captcha sinon FALSE.
	 */
	public function check($word)
	{
		return ($word == $this->_word);
	}
	
	/**
	 * Récupère les inforations du captcha en session.
	 * @return Captcha Retourne le captcha en session s'il est présent sinon NULL.
	 */
	public static function load()
	{
		$session = Session::get_instance();
		$captcha = $session->__captcha;
		unset($session->__captcha);
		return $captcha;
	}
	
	
	/**
	 * Définie le dossier ou sera sauvegarder les images des captchas.
	 * @param string $dir Dossier de destination des images.
	 * @return boolean TRUE si dossier valide et chargé sinon FALSE.
	 */
	public static function set_dir($dir)
	{
		if (!file_exists($dir))
		{
			mkdir($dir,0755,TRUE);
		}
		if (file_exists($dir) && is_readable($dir) && is_dir($dir))
		{
			self::$_dir = (substr($dir,-1) == '/') ? ($dir) : ($dir.'/');
			return FALSE;
		}	
		else
		{
			return TRUE;
		}
	}

	/**
	 * Supprime tous les captchas du dossier des captchas.
	 */
	public static function clean_dir()
	{
		if (self::$_dir != NULL)
		{
			$files = glob(self::$_dir.'captcha-*.png');
			array_map('unlink',$files);
		}
	}
	
	/**
	 * Définie le dossier des fonds du captcha.
	 * @param string $dir Dossier des fonds du captcha.
	 */
	public static function set_dir_backgrounds($dir)
	{
		if (file_exists($dir))
		{
			self::$_dir_backgrounds = (substr($dir,-1) !== '/') ? ($dir.'/') : ($dir);
		}
	}
	
	/**
	 * Définie le dossier des polices du captcha.
	 * @param string $dir Dossier des polices du captcha.
	 */
	public static function set_dir_fonts($dir)
	{
		if (file_exists($dir))
		{
			self::$_dir_fonts = (substr($dir,-1) !== '/') ? ($dir.'/') : ($dir);
		}
	}
	
	/**
	 * Définie l'intervalle pour la rotation.
	 * @param int $min Angle minimal en degré.
	 * @param int $max Angle maximal en degré.
	 */
	public static function set_rotation($min,$max)
	{
		if (is_numeric($max) && is_numeric($min))
		{
			self::$_rotation_min = $min;
			self::$_rotation_max = $max;
		}
	}
}
?>