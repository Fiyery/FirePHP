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
	public function get_alerts() : array
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
	public function get_scripts() : array
	{
		return $this->_scripts;
	}

	/**
	 * Retourne le contenu des scripts.
	 * @return string
	 */
	public function get_script_content() : string
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
	public function get_styles() : array
	{
		return $this->_styles;
	}

	/**
	 * Retourne le contenu des styles.
	 * @return string
	 */
	public function get_style_content() : string
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
		if ($code !== NULL)
		{
			switch ($code)
			{
				case 200 : header("HTTP/1.1 200 Ok"); break;
				case 201 : header("HTTP/1.1 201 Created"); break;
				case 202 : header("HTTP/1.1 202 Accepted"); break;
				case 203 : header("HTTP/1.1 203 Nonauthoritative information"); break;
				case 204 : header("HTTP/1.1 204 No content"); break;
				case 205 : header("HTTP/1.1 205 Reset content"); break;
				case 206 : header("HTTP/1.1 206 Partial content"); break;
				case 300 : header("HTTP/1.1 300 Multiple Choices"); break; 	
				case 301 : header("HTTP/1.1 301 Moved Permanently"); break;
				case 302 : header("HTTP/1.1 302 Moved Temporarily"); break; 
				case 303 : header("HTTP/1.1 303 See Other"); break;
				case 304 : header("HTTP/1.1 304 Not Modified"); break; 	
				case 305 : header("HTTP/1.1 305 Use Proxy"); break;	
				case 306 : header("HTTP/1.1 306 (aucun)"); break;	
				case 307 : header("HTTP/1.1 307 Temporary Redirect"); break;	
				case 308 : header("HTTP/1.1 308 Permanent Redirect"); break;
				case 310 : header("HTTP/1.1 310 Too many Redirectscase"); break;
				case 400 : header("HTTP/1.1 400 Bad Request"); break;
				case 401 : header("HTTP/1.1 401 Unauthorized"); break; 	
				case 402 : header("HTTP/1.1 402 Payment Required"); break;	
				case 403 : header("HTTP/1.1 403 Forbidden"); break; 	
				case 404 : header("HTTP/1.1 404 Not Found"); break;	
				case 405 : header("HTTP/1.1 405 Method Not Allowed"); break; 	
				case 406 : header("HTTP/1.1 406 Not Acceptable"); break;	
				case 407 : header("HTTP/1.1 407 Proxy Authentication Required"); break; 	
				case 408 : header("HTTP/1.1 408 Request Time-out"); break;	
				case 409 : header("HTTP/1.1 409 Conflict"); break; 
				case 410 : header("HTTP/1.1 410 Gone"); break;	
				case 411 : header("HTTP/1.1 411 Length Required"); break; 	
				case 412 : header("HTTP/1.1 412 Precondition Failed"); break;	
				case 413 : header("HTTP/1.1 413 Request Entity Too Large"); break; 
				case 414 : header("HTTP/1.1 414 Request-URI Too Long"); break;
				case 415 : header("HTTP/1.1 415 Unsupported Media Type"); break; 	
				case 416 : header("HTTP/1.1 416 Requested range unsatisfiable"); break;
				case 417 : header("HTTP/1.1 417 Expectation failed"); break; 
				case 421 : header("HTTP/1.1 421 Bad mapping / Misdirected Request"); break;	
				case 422 : header("HTTP/1.1 422 Unprocessable entity"); break; 	
				case 500 : header("HTTP/1.1 500 Internal Server Error"); break;	
				case 501 : header("HTTP/1.1 501 Not Implemented"); break; 	
				case 502 : header("HTTP/1.1 502 Bad Gateway ou Proxy Error"); break;	
				case 503 : header("HTTP/1.1 503 Service Unavailable"); break; 	
				case 504 : header("HTTP/1.1 504 Gateway Time-out"); break;	
				case 505 : header("HTTP/1.1 505 HTTP Version not supported"); break; 	
				case 506 : header("HTTP/1.1 506 Variant also negociate"); break;
				case 507 : header("HTTP/1.1 507 Insufficient storage"); break; 	
				case 508 : header("HTTP/1.1 508 Loop detected"); break;
				case 509 : header("HTTP/1.1 509 Bandwidth Limit Exceeded"); break; 	
				case 510 : header("HTTP/1.1 510 Not extended"); break;
				case 511 : header("HTTP/1.1 511 Network authentication required"); break; 
				case 520 : header("HTTP/1.1 520 Web server is returning an unknown error"); break;
			}
			$this->_status_code = $code;
		}
		return $this->_status_code;
	}
}
?>