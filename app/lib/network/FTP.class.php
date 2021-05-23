<?php
namespace FirePHP\Network;

/**
 * FTP est une interface simplifiée de l'utilisation du File Transfert Protocol.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 */
class FTP
{
	/**
	 * URL du serveur FTP
	 * @var string
	 */
	private $_host = '';
	
	/**
	 * Numéro du port du serveur pour le service FTP.
	 * @var int
	 */
	private $_port = 21;
	
	/**
	 * Identifiant de l'utilisateur.
	 * @var string
	 */
	private $_login = '';
	
	/**
	 * Mot de passe de l'utilisateur.
	 * @var string
	 */
	private $_pass = '';
	
	/**
	 * Connexion avec le serveur FTP.
	 * @var Ressource
	 */
	private $_connection = NULL;
	
	/**
	 * Définie si les envois et receptions se fond en mode bloquant ou non.
	 * @var boolean
	 */
	private $_non_blocking = FALSE;
	
	/**
	 * Constructeur.
	 */
	public function __construct()
	{

	}
	
	/**
	 * Intialise la connexion au serveur FTP.
	 * @param string $host Url du serveur.
	 * @param int $port Numéro du port.
	 * @param int $timeout Temps maximale en secondes pour la connexion au serveur. 
	 * @param boolean $secure Si TRUE, tente de faire du SFTP.
	 * @return boolean
	 */
	public function open($host, $port=21, $timeout=30, $secure=FALSE)
	{
		$this->_host = $host;
		$this->_port = $port;
	    if ($secure && function_exists('ftp_ssl_connect'))
	    {
	    	$this->_connection = ftp_ssl_connect($host, $port, $timeout);
	    }
	    else
	    {
	    	$this->_connection = ftp_connect($host, $port, $timeout);
	    }
	    return (is_resource($this->_connection));
	}
	
	/**
	 * Ferme la connexion.
	 */
	public function close()
	{
		if ($this->is_connected())
		{
			ftp_close($this->_connection);
		}
	}
	
	/**
	 * Vérifie si la connexion est établie.
	 * @return boolean
	 */
	public function is_connected()
	{
	    return (is_resource($this->_connection));
	}
	
	/**
	 * Identifie le compte FTP.
	 * @param string $login Nom de l'utilisateur.
	 * @param string $pass Mot de passe de connexion.
	 * @return boolean
	 */
	public function login($login, $pass)
	{
	    if ($this->is_connected() == FALSE)
	    {
	        return FALSE;
	    }
	    $this->_login = $login;
	    $this->_pass = $pass;
	    return (ftp_login($this->_connection, $login, $pass));
	}
	
	/**
	 * Destructeur.
	 */
	public function __destruct()
	{
		if ($this->is_connected())
		{
			ftp_close($this->_connection);
		}
	}
	
	/**
	 * Active ou désactive le mode non bloquant.
	 * @param boolean $active
	 * @return boolean
	 */
	public function set_non_blocking($active=TRUE)
	{
	    if ($this->is_connected() == FALSE)
	    {
	    	return FALSE;
	    }
		$this->_non_blocking = (is_bool($active)) ? ($active) : (FALSE);
		return TRUE;
	}
	
	/**
	 * Active ou désactive le mode passif (il est recommandé de l'activer).
	 * @param boolean $active
	 * @return boolean
	 */
	public function set_passive($active=TRUE)
	{
	    if ($this->is_connected() == FALSE)
	    {
	    	return FALSE;
	    }
		$active = (is_bool($active)) ? ($active) : (FALSE);
		ftp_pasv($this->_connection, $active);
		return TRUE;
	}
	
	/**
	 * Récupère la liste des fichiers.
	 * @param string $dir Dossier à scanner.
	 * @param int $rec Nombre de récursivité à faire dans l'arborescende (très coûteux en requête).
	 * @return array Liste des fichiers sans le dossier courant et parent.
	 */
	public function ls($dir='.', $rec=0)
	{
	    if ($this->is_connected() == FALSE)
	    {
	    	return array();
	    }
		$list = ftp_nlist($this->_connection, $dir);
		$list = (is_array($list)) ? (array_diff($list, array('.', '..'))) : (array());
		if ($rec > 0)
		{
			$dir = (substr($dir, -1) == '/') ? ($dir) : ($dir.'/');
			$data = array();
			foreach ($list as $f)
			{
				$size = $this->size($f);
				if ($size < 0)
				{
					$data[basename($f)] = $this->ls($f, $rec - 1);
				}
				else 
				{
					$data[basename($f)] = $size;
				}
			}
			return $data;
		}
		return $list;
	}
	
	/**
	 * Récupère un fichier du serveur.
	 * @param string $file Nom du fichier à télécharger.
	 * @param string $dir Nom du dossier où sera téléchargé le fichier.
	 * @return boolean
	 */
	public function get($file, $dir='./')
	{
	    if ($this->is_connected() == FALSE)
	    {
	    	return FALSE;
	    }
		if (is_dir($dir) == FALSE)
		{
			return FALSE;
		}
		$dir = (substr($dir, -1) != '/') ? ($dir.'/') : ($dir); 
		if ($this->_non_blocking)
		{
			return ftp_nb_get($this->_connection, $dir.basename($file), $file, FTP_BINARY);
		}
		else
		{
			return ftp_get($this->_connection, $dir.basename($file), $file, FTP_BINARY);
		}
	}
	
	/**
	 * Envoie un fichier sur le serveur FTP.
	 * @param string $file Nom du fichier.
	 * @param string $dir Dossier de réception côté serveur.
	 * @return boolean
	 */
	public function put($file, $dir='.')
	{
	    if ($this->is_connected() == FALSE)
	    {
	    	return FALSE;
	    }
		if (file_exists($file) == FALSE)
		{
			 return FALSE;
		}
		$dir = (substr($dir, -1) != '/') ? ($dir.'/') : ($dir);
		if ($this->_non_blocking)
		{
			return ftp_nb_put($this->_connection, $dir.basename($file), $file, FTP_BINARY);
		}
		else
		{
			return ftp_put($this->_connection, $dir.basename($file), $file, FTP_BINARY);
		}
	}
	
	/**
	 * Vérifie si l'envoie est finie.
	 * @return boolean TRUE si l'envoie est en cours ou FASLE.
	 */
	public function proceed()
	{
	    if ($this->is_connected() == FALSE)
	    {
	    	return FALSE;
	    }
		return (ftp_nb_continue($this->_connection) == FTP_MOREDATA);
	}
	
	/**
	 * Crée un dossier dans le serveur.
	 * @param string $dir Nom du dossier
	 * @return boolean
	 */
	public function mkdir($dir)
	{
	    if ($this->is_connected() == FALSE)
	    {
	    	return FALSE;
	    }
	    if ($this->is_existed($dir))
	    {
	        return FALSE;
	    }
		return (ftp_mkdir($this->_connection, $dir) != FALSE);
	}
	
	/**
	 * Supprime un dossier dans le serveur.
	 * @param string $dir Nom du dossier
	 * @return boolean
	 */
	public function rmdir($dir)
	{
	    if ($this->is_connected() == FALSE)
	    {
	    	return FALSE;
	    }
	    if ($this->is_existed($dir) == FALSE)
	    {
	    	return FALSE;
	    }
		return ftp_rmdir($this->_connection, $dir);
	}
	
	/**
	 * Change le dossier courant du serveur.
	 * @param string $dir Nom du dossier
	 * @return boolean
	 */
	public function cd($dir)
	{
	    if ($this->is_connected() == FALSE)
	    {
	    	return FALSE;
	    }
		return ftp_chdir($this->_connection, $dir);
	}
	
	/**
	 * Retourne le chemin du dossier courant sur le serveur.
	 * @return string
	 */
	public function pwd()
	{
	    if ($this->is_connected() == FALSE)
	    {
	    	return FALSE;
	    }
		return ftp_pwd($this->_connection);
	}
	
	/**
	 * Exécute une commande sur le serveur.
	 * @param string $cmd Commande à exécuter.
	 * @return boolean
	 */
	public function exec($cmd)
	{
	    if ($this->is_connected() == FALSE)
	    {
	    	return FALSE;
	    }
		return ftp_exec($this->_connection, $cmd);
	}
	
	/**
	 * Change les droits d'un fichier ou dossier.
	 * @param string $file Nom du fichier ou dossier.
	 * @param int $mode Droit en notation octale.
	 * @return boolean
	 */
	public function chmod($file, $mode)
	{
	    if ($this->is_connected() == FALSE)
	    {
	    	return FALSE;
	    }
		return ftp_chmod($this->_connection, $mode, $file);
	}
	
	/**
	 * Supprime un fichier.
	 * @param string $file Nom du fichier.
	 * @return boolean
	 */
	public function delete($file)
	{
	    if ($this->is_connected() == FALSE)
	    {
	    	return FALSE;
	    }
		return ftp_delete($this->_connection, $file);
	}
	
	/**
	 * Renomme un fichier.
	 * @param string $old Nom du fichier à renommer.
	 * @param string $new Nouveau Nom.
	 * @return boolean
	 */
	public function rename(string $old, string $new)
	{
	    if ($this->is_connected() == FALSE)
	    {
	    	return FALSE;
	    }
		return ftp_rename($this->_connection, $old, $new);
	}
	
	/**
	 * Vérifie si un fichier ou un dossier exists.
	 * @param string $file Nom du fichier ou dossier.
	 * @return boolean
	 */
	public function is_existed($file)
	{
	    if ($this->is_connected() == FALSE)
	    {
	    	return FALSE;
	    }
		$list = $this->ls(dirname($file));
		return in_array(basename($file), $list);
	}
	
	/**
	 * Retourne la taille d'un fichier.
	 * @param string $file Nom du fichier.
	 * @return int
	 */
	public function size($file)
	{
	    if ($this->is_connected() == FALSE)
	    {
	    	return -1;
	    }
		return ftp_size($this->_connection, $file);
	}
	
	/**
	 * Retourn l'URL pour accéder au serveur FTP.
	 * @return string
	 */
	public function url()
	{
		if ($this->is_connected() == FALSE)
		{
			return NULL;
		}
		$url = 'ftp://';
		if ($this->_login != NULL)
		{
			$url .= $this->_login;
		}
		if ($this->_pass != NULL)
		{
			$url .= ':'.$this->_pass;
		}
		return $url.'@'.$this->_host.':'.$this->_port.'/';
	}
	
	/**
	 * Retourne la date de dernière modificatino du fichier.
	 * @param string $file Chemin du fichier.
	 * @return int
	 */
	public function mdate($file)
	{
		if ($this->is_connected() == FALSE)
		{
			return FALSE;
		}
		return filemtime($this->url().$file);
	}
}

?>