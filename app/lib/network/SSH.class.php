<?php
/**
 * SSH est une interface qui permet la connexion simple à un serveur en SSH et également de faire du SFTP avec ce serveur.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 */
class SSH
{
	/**
	 * Ressource de la connexion SSH.
	 * @var Resource
	 */
	private $_connection = NULL;
	
	/**
	 * Ressource de la connexion SFTP.
	 * @var Resource
	 */
	private $_sftp = NULL;


	/**
	 * Constructeur.
	 */
	public function __construct()
	{
		
	}
	
	/**
	 * Destructeur.
	 */
	public function __destruct()
	{
		//$this->exec('cmd \c exit');
		unset($this->_connection);
	}
	
	/**
	 * Vérifie si le module SSH est disponible.
	 * @return boolean
	 */
	public function is_enabled()
	{
		return (function_exists("ssh2_connect"));
	}
	
	/**
	 * Établie ma connexion en SSH.
	 * @param string $host Adresse du serveur cible.
	 * @param string $port Num�ro de port de la connexion.
	 * @return boolean
	 */
	public function connect($host, $port)
	{
		if ($this->is_enabled() == FALSE)
		{
			return FALSE;
		}
		$this->_connection = ssh2_connect($host, $port);
		return (is_resource($this->_connection));
	}
	
	/**
	 * Authentifie le compte d'utilisateur.
	 * @param string $user Nom de l'utilisateur.
	 * @param string $pass Mot de passe.
	 * @return boolean
	 */
	public function login($user, $pass)
	{
		if (is_resource($this->_connection) == FALSE)
		{
			return FALSE;
		}
		return ssh2_auth_password($this->_connection, $user, $pass);
	}
	
	/**
	 * Exécute une commande.
	 * Attention sur un serveur Windows, il faut précéder la commande de cmd \c.
	 * @param string $cmd Commande � ex�cuter.
	 * @return string
	 */
	public function exec($cmd)
	{
		if (is_resource($this->_connection) == FALSE)
		{
			return NULL;
		}
		$stream = ssh2_exec($this->_connection, $cmd);
		if (is_resource($stream) == FALSE)
		{
			return NULL;
		}
		stream_set_blocking($stream, TRUE);
		$data = '';
		while ($buf = fread($stream, 4096)) 
		{
			$data .= $buf;
		}
		fclose($stream);
		return $data;
	}
	
	/**
	 * Initialise le système SFTP.
	 * @return boolean
	 */
	public function init_sftp()
	{
		if (is_resource($this->_connection) == FALSE)
		{
			return FALSE;
		}
		$this->_sftp = ssh2_sftp($this->_connection);
		return (is_resource($this->_sftp));
	}
	
	/**
	 * Retourne le dossier courant du serveur.
	 * @return string
	 */
	public function pwd()
	{
		if (is_resource($this->_sftp) == FALSE)
		{
			return NULL;
		}
		return ssh2_sftp_realpath($this->_sftp, '.');;
	}
	
	/**
	 * Affiche le contenu d'un dossier.
	 * @param string $server_dir Nom du dossier sur le serveur.
	 * @return array Liste du contenu du dossier ou NULL
	 */
	public function ls($server_dir)
	{
		if (is_resource($this->_sftp) == FALSE)
		{
			return NULL;
		}
		$server_dir = (substr($server_dir, 0, 1) != '/') ? ('/'.$server_dir) : ($server_dir);
		if (is_dir('ssh2.sftp://'.$this->_sftp.$server_dir) == FALSE)
		{
			return NULL;
		}
		return scandir('ssh2.sftp://'.$this->_sftp.$server_dir); 
	}
	
	/**
	 * Récupère un fichier du serveur et l'enregistre localement.
	 * @param string $server_file Nom du fichier à télécharger.
	 * @param string $local_file Nom du fichier enregistr�.
	 * @return boolean
	 */
	public function get($server_file, $local_file)
    {
		$server_file = (substr($server_file, 0, 1) != '/') ? ('/'.$server_file) : ($server_file);
		if (is_file('ssh2.sftp://'.$this->_sftp.$server_file) == FALSE)
		{
			return FALSE;
		}
		$stream = fopen('ssh2.sftp://'.$this->_sftp.$server_file, 'r');
        if ($stream === FALSE)
        {
			return FALSE;
		}
        $contents = fread($stream, filesize('ssh2.sftp://'.$this->_sftp.$server_file));      
		fclose($stream);		
        return (file_put_contents($local_file, $contents) !== FALSE);
    }
	
	/**
	 * Envoie une ficheir sur le serveur via SCP.
	 * @param string $local_file Nom du fichier à envoyer.
	 * @param string $server_file Nouveau nom du fichier sur le serveur.
	 * @return boolean 
	 */
	public function put($local_file, $server_file)
	{
		if (is_resource($this->_sftp) == FALSE)
		{
			return FALSE;
		}
		if (file_exists($local_file) == FALSE || is_readable($local_file) == FALSE)
		{
			return FALSE;
		}
		$server_file = (substr($server_file, 0, 1) != '/') ? ('/'.$server_file) : ($server_file);
        $stream = fopen('ssh2.sftp://'.$this->_sftp.$server_file, 'w+');
        if (is_resource($stream) == FALSE)
		{
			return FALSE;
		}
        $data_to_send = file_get_contents($local_file);
		if (fwrite($stream, $data_to_send) === FALSE)
		{
			return FALSE;
		}
		fclose($stream);
		return TRUE;
	}
	
	
	/**
	 * Supprime un fichier ou un dossier.
	 * @param string $server_file Nom du fichier ou dossier sur le serveur.
	 * @return boolean
	 */
	public function rm($server_file)
	{
		if (is_resource($this->_sftp) == FALSE)
		{
			return FALSE;
		}
		$server_file = (substr($server_file, 0, 1) != '/') ? ('/'.$server_file) : ($server_file);
		if (file_exists('ssh2.sftp://'.$this->_sftp.$server_file) == FALSE)
		{
			return FALSE;
		}
		if (is_file('ssh2.sftp://'.$this->_sftp.$server_file))
		{
			return unlink('ssh2.sftp://'.$this->_sftp.$server_file);
		}
		else
		{
			return rmdir('ssh2.sftp://'.$this->_sftp.$server_file);
		}
	}

	/**
	 * Créer un dossier.
	 * @param string $server_dir Nom du dossier sur le serveur.
	 * @return boolean
	 */
	public function mkdir($server_dir)
	{
		if (is_resource($this->_sftp) == FALSE)
		{
			return FALSE;
		}
		$server_dir = (substr($server_dir, 0, 1) != '/') ? ('/'.$server_dir) : ($server_dir);
		return mkdir('ssh2.sftp://'.$this->_sftp.$server_dir);
	}
	
}
?>
