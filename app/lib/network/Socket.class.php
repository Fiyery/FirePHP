<?php

class Socket
{

	/**
	 * Instance du socket.
	 * @var Ressource
	 */
	private $_socket = NULL;

	/**
	 * Adresse du socket.
	 * @var string
	 */
	private $_address;

	/**
	 * Numéro du port.
	 * @var int
	 */
	private $_port;

	/**
	 * Taille maximale de client en attente de réponse du serveur.
	 * @var int
	 */
	private $_socket_queue = 5;

	/**
	 * Constructeur.
	 */
	public function __construct($address='', $port='')
	{
		// Vidage des buffers implicite (vue au fur et à mesure).
		ob_implicit_flush();
		$this->_address = $address;
		$this->_port = $port;
		$this->create();
	}

	/**
	 * Destructeur.
	 */
	public function __destruct()
	{
		$this->close();
	}

	/**
	 * Créer le socket.
	 * @return bool
	 */
	private function create() 
	{
		if (($this->_socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === FALSE)
		{
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Créer un Socket a partir d'une ressource de socket PHP.
	 * @param resource $socket
	 * @return Socket
	 */
	protected function set($socket)
	{
		if (is_resource($socket))
		{
			$this->_socket = $socket;
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Création du socket
	 * @return bool
	 */
	public function open()
	{
		return socket_bind($this->_socket, $this->_address, $this->_port);
	}

	/**
	 * Mise en attente de message.
	 * @param int $seconds Nombre de secondes maximales d'attente. Pour aucune limite mettre "0".
	 * @return Socket
	 */
	public function listen($seconds=0)
	{
		if (socket_listen($this->_socket, $this->_socket_queue) === FALSE)
		{
			return NULL;
		}
		set_time_limit($seconds);
		if (($remote_socket = socket_accept($this->_socket)) === FALSE) 
		{
			return NULL;
		}
		$object = new Socket();
		$object->set($remote_socket);
		return $object;
	}

	/**
	 * Établie une connexion avec un autre socket.
	 * @param string $address Adresse IP du socket distant.
	 * @param int $port NUméro de port du socket distant.
	 * @return bool
	 */
	public function connect($address, $port) 
	{
		return socket_connect($this->_socket, $address, $port);
	}

	/**
	 * Ferme le socket courant.
	 * @return bool
	 */
	public function close() 
	{
		if (is_resource($this->_socket))
		{
			return socket_close($this->_socket);
		}
		return FALSE;
	}

	/**
	 * Ecrit dans le socket.
	 * @param string $string Chaîne de caractères à envoyer.
	 * @return bool
	 */
	public function write($string)
	{
		$string .= "\n";
		$length = socket_write($this->_socket, $string, strlen($string));
		if ($length == FALSE)
		{
			return FALSE;
		}
		else
		{
			return ($length == strlen($string));
		}
	}

	/**
	 * lit un socket.
	 * @return string
	 */
	public function read()
	{
		$data = "";
		$end = '1';
		while ($end != "\n" && $end != FALSE)
		{
			$out = socket_read($this->_socket, 2048, PHP_NORMAL_READ);
			$end = substr($out, -1);
			var_dump($out);
			var_dump($end);
		    $data .= trim($out);
		}
		return $data;
	}

	/**
	 * Récupère la dernière erreur.
	 * @return string
	 */
	public function error()
	{
		return socket_strerror(socket_last_error($this->_socket));
	}
}
?>