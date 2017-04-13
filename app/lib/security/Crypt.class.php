<?php
/**
 * Crypt est une interface simplifiée de gestion du cryptage et hashage.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 */
class Crypt
{
	/**
	 * Salt ajouté au début du hashage.
	 * @var string
	 */
	private $_prefix_salt = '';
	
	/**
	 * Salt ajouté à la fin du hashage.
	 * @var string
	 */
	private $_suffix_salt = '';
    
    /**
     * Tableau de concordance de caractères spéciaux.
     * @var string[]
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
	public function __construct(string $key = NULL)
	{
		$this->_key = sha1($key);
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
	public function encrypt(string $string) : string
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
	public function decrypt(string $string) : string
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
	 * Hash une chaine de caractère en BCRYPT.
	 * @param string $string Chaine de caractère à hasher.
	 * @return string
	 */
	public function hash(string $string) : string
	{
	    if (is_scalar($string))
	    {
		  	return password_hash($this->_prefix_salt.$string.$this->_suffix_salt, PASSWORD_BCRYPT);
	    }
	    return NULL;
	}

	/**
	 * Vérifie une chaine à haser par rapport à un Hash existant.
	 * @param string $string Chaine à valider.
	 * @param string $hash Chaine de caractère déjà hashé.
	 * @return bool
	 */
	public function verify_hash(string $string, string $hash) : bool
	{
	    if (is_scalar($string) && is_scalar($hash))
	    {
		  	return password_verify($this->_prefix_salt.$string.$this->_suffix_salt, $hash);
	    }
	    return FALSE;
	}
	
	/**
	 * Définie la clé par défaut.
	 * @param string $key Valeur de la clé.
	 * @return string
	 */
	public function key(string $key) : string
	{
	    if (is_scalar($key))
	    {
	        $this->_key = sha1($key);
	    }
	    return $this->_key;
	}
	
	/**
	 * Définie les salts du hashage.
	 * @param string $key Valeur de la clé.
	 * @return boolean
	 */
	public function salts(string $prefix, string $suffix) : bool
	{
		if (is_scalar($prefix) && is_scalar($suffix))
		{
			$this->_prefix_salt = $prefix;
			$this->_suffix_salt = $suffix;
			return TRUE;
		}
		return FALSE;
	}
}
?>
