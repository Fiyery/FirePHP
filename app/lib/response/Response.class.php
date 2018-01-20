<?php
/**
 * Site est la class qui gère les actions primaires du site telles que l'affichage de messages, la modification du titre de la page ou les redirections.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses Session
 * @uses Request
 */
class Response 
{
	/**
	 * Constant qui définie le format d'un message.
	 * @var string
	 */
	const ALERT_INFO = 'alert_info';
	
	/**
	 * Constant qui définie le format d'un message.
	 * @var string
	 */
	const ALERT_SUCCESS = 'alert_success';
	
	/**
	 * Constant qui définie le format d'un message.
	 * @var string
	 */
	const ALERT_WARNING = 'alert_warning';
	
	/**
	 * Constant qui définie le format d'un message.
	 * @var string
	 */
	const ALERT_ERROR = 'alert_error';

	/**
	 * Variable d'instance de singleton.
	 * @var Site
	 */
	protected static $_instance = NULL;

	/**
	 * Variable de session pour les messages.
	 * @var Session
	 */
	private $_session = NULL;
	
	/**
	 * Variable de requête.
	 * @var Request
	 */
	private $_request = NULL;

	/**
	 * Titre de la réponse.
	 * @var string
	 */
	private $_title = "";

	/**
	 * Code HTTP de retour.
	 * @var number
	 */
	private $_status_code = 200;

	/**
	 * Liste des scripts.
	 * @var array
	 */
	private $_scripts = [];

	/**
	 * Liste des styles.
	 * @var array
	 */
	private $_styles = [];
	
	/**
	 * Constructeur.
	 */
	public function __construct(Session $session, Template $request)
	{
	    $this->_session = $session;
	    $this->_request = $request;
	}

	/**
	 * Définie et retourne le titre de la page.
	 * @param string $title Titre de la page
	 * @return string
	 */
	public function title(string $title = NULL) : string
	{
		if ($title !== NULL)
		{
			$this->_title = $title;
		}
		return $this->_title;
	}
	
	/**
	 * Ajoute un message.
	 * @param string $message Message à afficher.
	 * @param int $type Type de message parmi les constantes ALERT_*.
	 */
	public function add_alert(string $message, string $type = self::ALERT_INFO)
	{
		if (is_array($this->_session->__messages) === FALSE)
		{
			$this->_session->__messages = [];
		}
		$list = $this->_session->__messages;
		$list[] = [
			'message' 	=> $message,
			'type' 		=> $type
		];
		$this->_session->__messages = $list;
	}
	
	/**
	 * Retourne les éventuels messages d'information stockés et les supprime.
	 * @return array
	 */
	public function alerts() : array
	{
		if (is_array($this->_session->__messages) === FALSE)
		{
			return [];
		}
		else
		{
			$tmp = $this->_session->__messages;
			$this->clean_alerts();
			return $tmp;
		}
	}
	
	/**
	 * Supprime tous les messages.
	 */
	public function clean_alerts()
	{
		unset($this->_session->__messages);
	}

	/**
	 * Ajoute un script à la réponse.
	 * @param string $src Adresse du script.
	 * @return void
	 */
	public function add_script(string $src)
	{
		$this->_scripts[] = $src;
	}

	/**
	 * Retourne la liste des scripts.
	 * @return array
	 */
	public function scripts() : array
	{
		return $this->_scripts;
	}

	/**
	 * Retourne le contenu des scripts.
	 * @return string
	 */
	public function script_content() : string
	{
		$content = "";
		foreach ($this->_scripts as $s)
		{
			if (file_exists($s))
			{
				$content .= file_get_contents($s);
			}
		}
		return $content;
	}

	/**
	 * Ajoute un style à la réponse.
	 * @param string $src Adresse du style.
	 * @return void
	 */
	public function add_style(string $src)
	{
		$this->_styles[] = $src;
	}

	/**
	 * Retourne la liste des styles.
	 * @return array
	 */
	public function styles() : array
	{
		return $this->_styles;
	}

	/**
	 * Retourne le contenu des styles.
	 * @return string
	 */
	public function style_content() : string
	{
		$content = "";
		foreach ($this->_styles as $s)
		{
			if (file_exists($s))
			{
				$content = file_get_contents($s);
			}
		}
		return $content;
	}

	/**
	 * Définie le Code HTTP de retour.
	 * @param int @code Numéro du code de retour.
	 * @return int 
	 */
	public function status_code(int $code = NULL) : int
	{
		if ($code !== NULL && ($code == http_response_code($code)))
		{
			$this->_status_code = $code;
		}
		return $this->_status_code;
	}
}
?>