<?php
/**
 * Session est l'interface de gestion des la session.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @copyright 2011-2015 Yoann Chaumin
 */
class Session extends Singleton
{
	/**
	 * Variable d'instance de singleton.
	 * @var Base
	 */
	protected static $_instance = NULL;
	
	/**
	 * Temps maximal en secondes d'une session. 
	 * @var int
	 */
	private static $_limit_time = 0;
	
	/**
	 * Utilisateur connecté avec ses informations.
	 * @var Object
	 */
	public $user;
	
	/**
	 * Statut de reprise de session.
	 * @var int
	 */
	private $_status = 0;
	
	/**
	 * Constructeur.
	 */
	protected function __construct()
	{
	    $this->start();
		if ($this->is_open())
		{
			if (self::$_limit_time != 0 && time() - $this->get_time() > self::$_limit_time)
			{
				$this->close();
				$this->_status = -1;
			}
			else 
			{
				$id = $this->get_id();
				if ($id != $this->set_id())
				{
					$this->close();
					$this->_status = -2;
				}
			}
		}
		session_regenerate_id();
		$this->set_time();
		$this->restore();
	}
	
	/**
	 * Destructeur.
	 */
	public function __destruct()
	{
        
	}
	
	/**
	 * Démarre une session.
	 */
	public function start()
	{
	    // La Session accessible sur tout le site.
		session_set_cookie_params('0', '/');
		// L'identifiant de session est transmis uniquement par un cookie.
		ini_set('session.use_only_cookies', 1);
		// Le cookie n'est accessible que par les requêtes HTTP et non les scripts comme JavaScript.
		ini_set('session.cookie_httponly', 1);
		// Le cookie n'est pas détruit en fonction du temps mais mannuellement.
		ini_set('session.cookie_lifetime', 0);
		// Le nombre de seconde d'une session.
		ini_set('session.gc_maxlifetime', 1800); 		
		// Si pas de session déjà chargée, on charge la session.
		if (session_id() == '')
		{
			session_start();
		}
	}

	/**
	 * Ouvre une session utilisateur.
	 * @param mixed $user Information sur le membre à enregistrer.
	 */
	public function open($user)
	{
		$_SESSION["__user"] = $user;
		$this->set_time();
		$this->set_id();
		$this->restore();
	}

	/**
	 * Ferme une session utilisateur.
	 */
	public function close()
	{
		unset($_SESSION["__user"]);
		unset($_SESSION['__time']);
	}

	/**
	 * Supprime toutes les variables de session.
	 */
	public function clean()
	{
		$_SESSION = array();
	}
	
	/**
	 * Vérifie si une session utilisateur est ouverte.
	 * @return boolean
	 */
	public function is_open()
	{
		return (isset($_SESSION['__user']));
	}
	
	/**
	 * Retourne le statut de la reprise se session.
	 * @return int Statut de reprise : 0 pour ok, -1 pour expiration de session, -2 pour session invalide. 
	 */
	public function get_status()
	{
		return $this->_status;
	}

	/**
	 * Restaure la session courrante.
	 */
	private function restore()
	{
		if ($this->is_open())
		{
			$this->user = $_SESSION['__user'];
		}
	}

	/**
	 * Récupère une variable personnalisée de session.
	 * @param string $name Nom de la variable.
	 * @return mixed Valeur de la variable ou NULL.
	 */
	public function __get($name)
	{
		return (isset($_SESSION['__vars'][$name])) ? ($_SESSION['__vars'][$name]) : (NULL);
	}

	/**
	 * Définie une variable personnalisée de session.
	 * @param string $name Nom de la variable.
	 * @param string $value Valeur de la variable.
	 */
	public function __set($name,$value)
	{
		$_SESSION['__vars'][$name] = $value;
	}

	/**
	 * Supprime une variable personnalisée de session.
	 * @param string $name Nom de la variable.
	 */
	public function __unset($name)
	{
		unset($_SESSION['__vars'][$name]);
	}
	
	/**
	 * Vérifie l'existance d'une variable personnalisée de session.
	 * @param string $name Nom de la variable.
	 */
	public function __isset($name)
	{
		return (isset($_SESSION['__vars'][$name]));
	}	

	/**
	 * Récupère le moment de génération de la dernière page.
	 * @return int Timestamp
	 */
	private function get_time()
	{
		return (isset($_SESSION['__time'])) ? ($_SESSION['__time']) : (0);
	}
	
	/**
	 * Sauvegarde le moment de génération de la page.
	 */
	private function set_time()
	{
		$_SESSION['__time'] = time();
	}
	
	/**
	 * Retourne l'identifiant de session.
	 * @return string Identifiant de session.
	 */
	private function get_id()
	{
		return (isset($_SESSION['__id'])) ? ($_SESSION['__id']) : (NULL);
	}
	
	/**
	 * Fournie un identifiant de session pour éviter la faille de fixation de session.
	 * @return string Nouvel identifiant.
	 */
	private function set_id()
	{
		$_SESSION['__id'] = md5($_SERVER['REMOTE_ADDR']);
		return $_SESSION['__id'];
	}
	
	/**
	 * Retourne l'identifiant de la session courrante généré par php.
	 * @return string
	 */
	public function get_session_id()
	{
		return session_id();
	}
	
	/**
	 * Definie le temps maximal d'une session pour un utilisateur.
	 * @param int $seconds Temps en secondes.
	 */
	public static function set_limit_time($seconds=0)
	{
		if (is_numeric($seconds))
		{
			self::$_limit_time = $seconds;
		}
	}
}
?>