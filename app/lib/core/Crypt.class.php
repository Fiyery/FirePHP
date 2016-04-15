<?php
/**
 * Crypt est une interface simplifiée de gestion du cryptage et hashage.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @copyright 2011-2015 Yoann Chaumin
 */
class Crypt
{
	/**
	 * Salt ajouté au début du hashage.
	 * @var string
	 */
	private static $_prefix_salt = '';
	
	/**
	 * Salt ajouté à la fin du hashage.
	 * @var string
	 */
	private static $_suffix_salt = '';
	
    /**
     * Clé d'encryptage par défaut.
     * @var string
     */
    private static $_default_key = NULL;
    
    /**
     * Tableau de concordance de caractères spéciaux.
     * @var array<string>
     */
	private $_table;
	
	/**
	 * CLé d'encryptage courante.
	 * @var string
	 */
	private $_key = NULL;
	
	/**
	 * Constructeur.
	 * @param string $key Clé d'encryptage.
	 */
	public function __construct($key=NULL)
	{
		if (empty($key))
		{
			$this->_key = self::$_default_key;
		}
		$this->_key = md5($this->_key);
		$this->_table = array();
		$this->_table['+'] = 'PL01';
		$this->_table['/'] = 'SL02';
		$this->_table['='] = 'EG03';
	}
	
	/**
	 * Encrypte une chaîne de caractères.
	 * @param string $string Chaîne à encrypter.
	 * @return mixed
	 */
	public function encrypt($string)
	{
	    if (is_string($string))
	    {
    	    $md5_key = md5($this->_key);
    		$encrypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->_key, $string, MCRYPT_MODE_CBC, $md5_key);
    		$encrypt = base64_encode($encrypt);
    		$encrypt = str_replace(array_keys($this->_table),array_values($this->_table),$encrypt);
    		return $encrypt;
	    }
		return NULL;
	}

	/**
	 * Decrypte une chaine encryptée.
	 * @param string $string Chaînée à décrypter.
	 * @return string 
	 */
	public function decrypt($string)
	{
	    if (is_string($string))
	    {
	        $decrypt = str_replace(array_values($this->_table),array_keys($this->_table),$string);
	        $decrypt = base64_decode($decrypt);
	        $md5_key = md5($this->_key);
	        $decrypt = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $this->_key, $decrypt, MCRYPT_MODE_CBC, $md5_key);
	        $decrypt = rtrim($decrypt, "\0");
	        return $decrypt;
	    }
		return NULL;
	}
	
	/**
	 * Hash une chaine de caractère en MD5 puis SHA1.
	 * @param string $string Chaine de caractère à hasher.
	 * @return string
	 */
	public static function hash($string)
	{
	    if (is_scalar($string))
	    {
		  return sha1(md5(self::$_prefix_salt.$string.self::$_suffix_salt));
	    }
	    return NULL;
	}
	
	/**
	 * Définie la clé par défaut.
	 * @param string $key Valeur de la clé.
	 * @return boolean
	 */
	public static function set_default_key($key)
	{
	    if (is_scalar($key))
	    {
	        self::$_default_key = $key;
	        return TRUE;
	    }
	    return FALSE;
	}
	
	/**
	 * Définie les salts du hashage.
	 * @param string $key Valeur de la clé.
	 * @return boolean
	 */
	public static function set_salts($prefix, $suffix)
	{
		if (is_scalar($prefix) && is_scalar($suffix))
		{
			self::$_prefix_salt = $prefix;
			self::$_suffix_salt = $suffix;
			return TRUE;
		}
		return FALSE;
	}
}
?>
