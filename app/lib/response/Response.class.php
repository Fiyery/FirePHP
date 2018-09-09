<?php
/**
 * Response gère le retour du serveur au travers du code retour, des entêtes et des messages informatifs.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses Session
 * @uses Request
 */
class Response implements ResponseInterface
{
	/**
	 * Variable d'instance de singleton.
	 * @var Site
	 */
	protected static $_instance = NULL;

	/**
	 * Gestion d'e l'entête.
	 * @var ResponseHeader
	 */
	protected $_header = NULL;

	/**
	 * Gestion du corps.
	 * @var ResponseBody
	 */
	protected $_body = NULL;

	/**
	 * Gestion des messages informatifs.
	 * @var ResponseAlert
	 */
	protected $_alert = NULL;

	/**
	 * Code retour.
	 * @var int
	 */
	protected $_status_code = 200;

	/**
	 * Variable de session pour les messages.
	 * @var Session
	 */
	protected $_session = NULL;
	
	/**
	 * Variable de requête.
	 * @var Request
	 */
	protected $_request = NULL;

	/**
	 * Titre de la réponse.
	 * @var string
	 */
	protected $_title = "";

	/**
	 * Liste des scripts.
	 * @var array
	 */
	protected $_scripts = [];

	/**
	 * Liste des styles.
	 * @var array
	 */
	protected $_styles = [];
	
	/**
	 * Constructeur.
	 */
	public function __construct(Session $session, Template $request)
	{
	    $this->_session = $session;
		$this->_request = $request;
		$this->_body = new ResponseBody();
		$this->_header = new ResponseHeader();
		
		$messages = (is_array($this->_session->__messages)) ? ($this->_session->__messages) : ([]);
		$this->_alert = new ResponseAlert($messages);
	}

	/**
	 * Destructeur.
	 */
	public function __destruct()
	{
		$this->_session->__messages = $this->_alert->lists();
	}

    /**
     * Retourne la classe de gestion des entêtes.
     * @return ResponseHeader
     */
	public function header() : ResponseHeader
	{
		return $this->_header;
	}

    /**
     * Retourne la classe de gestion des messages informatifs.
     * @return ResponseAlert
     */
	public function alert() : ResponseAlert
	{
		return $this->_alert;
	}
    
    /**
     * Retourne la classe de gestion du corps.
     * @return ResponseBody
     */
	public function body() : ResponseBody
	{
		return $this->_body();
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
}
?>