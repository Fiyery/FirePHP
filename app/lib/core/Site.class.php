<?php
/**
 * Site est la class qui gère les actions primaires du site telles que l'affichage de messages, la modification du titre de la page ou les redirections.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses Session
 * @uses Template
 * @uses Singleton
 * @uses Request
 */
class Site extends Singleton
{
	/**
	 * Constant qui définie le format d'un message.
	 * @var int
	 */
	const ALERT_INFO = 1;
	
	/**
	 * Constant qui définie le format d'un message.
	 * @var int
	 */
	const ALERT_OK = 2;
	
	/**
	 * Constant qui définie le format d'un message.
	 * @var int
	 */
	const ALERT_WARNING = 3;
	
	/**
	 * Constant qui définie le format d'un message.
	 * @var int
	 */
	const ALERT_ERROR = 4;

	/**
	 * Variable d'instance de singleton.
	 * @var Site
	 */
	protected static $_instance = NULL;

	/**
	 * Tableau contenant les racines URL et DIR du site.
	 * @var array<string>
	 */
	private static $_root = NULL;

	/**
	 * Variable de session pour les messages.
	 * @var Session
	 */
	private $_session = NULL;
	
	/**
	 * Variable de template pour l'affichage de message.
	 * @var Template
	 */
	private $_template = NULL;
	
	/**
	 * Variable de requête.
	 * @var Request
	 */
	private $_request = NULL;

	/**
	 * Nom du module du site.
	 * @var string
	 */
	private $_module = NULL;

	/**
	 * Nom de l'action du site.
	 * @var string
	 */
	private $_action = NULL;

	/**
	 * Nom par défaut du module.
	 * @var string
	 */
	private $_default_module = NULL;

	/**
	 * Nom par défaut de l'action
	 * @var string
	 */
	private $_default_action = NULL;
	
	/**
	 * Nom de la variable du template pour le titre du site.
	 * @var string
	 */
	private $_tpl_name_title = NULL;
	
	/**
	 * Nom de la variable du template pour la description du site.
	 * @var string
	 */
	private $_tpl_name_description = NULL;
	
	/**
	 * Constructeur.
	 */
	protected function __construct(Session $session, Template $template, Request $request)
	{
	    $this->_session = $session;
	    $this->_template = $template;
	    $this->_request = $request;
		self::$_root = array();
	}
	
	/**
	 * Définie le titre de la page.
	 * @param string $title Titre de la page.
	 * @param string $name Nom de la variable du template.
	 */
	public function set_title($title, $name=NULL)
	{
	    if ($name == NULL)
	    {
	        $name = $this->_tpl_name_title;
	    }
		$this->_template->assign($name, $title);
	}
	
	/**
	 * Définie la description de la page.
	 * @param string $description Description de la page.
	 * @param string $name Nom de la variable du template.
	 */
	public function set_description($description, $name=NULL)
	{
	    if ($name == NULL)
	    {
	    	$name = $this->_tpl_name_description;
	    }
		$this->_template->assign($name, $description);
	}
	
	/**
	 * Définie le module par défaut.
	 * @param string $module Nom du module par défaut.
	 */
	public function set_default_module($module)
	{
		$this->_default_module = $module;
	}
	
	/**
	 * Définie l'action par défaut.
	 * @param string $action Nom du l'action par défaut.
	 */
	public function set_default_action($action)
	{
		$this->_default_action = $action;
	}
	
	/**
	 * Définie le nom de la variable du template pour le titre.
	 * @param string $name Nom de la variable.
	 */
	public function set_tpl_name_title($name)
	{
	    $this->_tpl_name_title = $name;
	}
	
	/**
	 * Définie le nom de la variable du template pour la description.
	 * @param string $name Nom de la variable.
	 */
	public function set_tpl_name_description($name)
	{
		$this->_tpl_name_description = $name;
	}
	
	/**
	 * Retourne l'arborescence de la page actuelle.
	 * @param array $name Tableau contenant la concordance entre les modules et actions et leur nouveau nom.
	 * @return string
	 */
	public function get_tree($name, $module, $action)
	{
		$sepatator = "<span class='separator'>></span>";
		$root = $this->get_root();
		$way = "<a href='" . $root . "' title='Accueil du site'>Accueil</a>";
		$position = strpos($module, '/');
		if ($position === FALSE) // Cas normal.
		{
			if ($module != $this->_default_module || $action != $this->_default_action)
			{
				$name_module = (isset($name[$module]['name'])) ? ($name[$module]['name']) : ($module);
				$way .= ' ' . $sepatator . " <a href='" . $root . "?module=" . $module . "'>" . ucfirst($name_module) . "</a>";
				if ($action != $this->_default_action)
				{
					$name_action = (isset($name[$module]['modules'][$action])) ? ($name[$module]['modules'][$action]) : ($action);
					$way .= ' ' . $sepatator . " <a href='" . $root . "?module=" . $module . "&amp;action=" . $action . "'>" . ucfirst($name_action) . "</a>";
				}
			}
		}
		else // Cas sous domaine.
		{
			$complete_name = $module;
			$subdomains = substr($module, 0, $position);
			$module = $name_module = (isset($name[$complete_name]['name'])) ? ($name[$module]['name']) : (substr($module, $position + 1));
			$way .= ' ' . $sepatator . " <a href='" . $root . $subdomains . "/'>" . ucfirst($subdomains) . "</a>";
			if ($module != $this->_default_module || $action != $this->_default_action)
			{
				$way .= ' ' . $sepatator . " <a href='" . $root . "?module=" . $module . "'>" . $module . "</a>";
				if ($action != $this->_default_action)
				{
					$way .= ' ' . $sepatator . " <a href='" . $root . "?module=" . $module . "&amp;action=" . $action . "'>" . ucfirst($action) . "</a>";
				}
			}
		}
		return $way;
	    return NULL;
	}
	
	/**
	 * Ajoute un message.
	 * @param string $message Message à afficher.
	 * @param int $type Type de message parmi les constantes ALERT_*.
	 */
	public function add_message($message, $type = self::ALERT_INFO)
	{
		$temp = $this->_session->__messages;
		$temp[$message] = $type;
		$this->_session->__messages = $temp;
	}
	
	/**
	 * Retourne les éventuels messages d'information stockés et les supprime.
	 * @return string
	 */
	public function list_messages()
	{
		if ($this->_session->__messages == FALSE)
		{
			return NULL;
		}
		$str = "";
		foreach($this->_session->__messages as $message => $type)
		{
			$str .= $this->format($message, $type);
		}
		$this->clean_messages();
		return $str;
	}
	
	/**
	 * Supprime tous les messages.
	 */
	public function clean_messages()
	{
		unset($this->_session->__messages);
	}
	
	/**
	 * Parse un message en HTML.
	 * @param string $message Message à afficher.
	 * @param int $type Type de message parmi les constantes ALERT_*.
	 * @return string Message parsé.
	 */
	private function format($message, $type = self::ALERT_INFO)
	{
		switch($type)
		{
			case self::ALERT_INFO:
			{
				$class = 'alert_info';
				break;
			}
			case self::ALERT_OK:
			{
				$class = 'alert_ok';
				break;
			}
			case self::ALERT_WARNING:
			{
				$class = 'alert_warning';
				break;
			}
			case self::ALERT_ERROR:
			{
				$class = 'alert_error';
				break;
			}
			default:
			{
				$class = 'alert_info';
			}
		}
		return '<div class="' . $class . '">' . $message . '</div>';
	}

	/**
	 * Charge les paramètres de la page en fonction d'une adresse URL donnée.
	 * @param string $url Adresse URL.
	 */
	public function load($url)
	{
		$arg = parse_url($url);
		$query = explode('&', $arg['query']);
		foreach($query as $v)
		{
			$k = explode('=', $v);
			if (count($k) == 2)
			{
				$this->_request->$k[0] = $k[1];
			}
		}
	}

	/**
	 * Formate les urls en urls absolues.
	 * @param array|string $url Liste ou élément unique contenant l'adresse URL à parser.
	 * @return array|string URL parsée.
	 */
	public function parse_url($url)
	{
		$list_url = NULL;
		if (is_array($url))
		{
			$list_url = array();
			foreach($url as $u)
			{
				$list_url[] = $this->parse_url_unique($u);
			}
		}
		elseif (is_string($url))
		{
			$list_url = $this->parse_url_unique($url);
		}
		return $list_url;
	}
	
	/**
	 * Formate une URL en URL absolue.
	 * @param astring $url L'adresse URL à parser.
	 * @return string URL parsée.
	 */
	private function parse_url_unique($url)
	{
		if (substr($url, 0, 7) != 'http://')
		{
			$pos = strpos($url, '?');
			if ($pos !== FALSE)
			{
				$get = substr($url, $pos);
				$url = substr($url, 0, $pos);
			}
			$url = (substr($url, - 1) == '/') ? (realpath($url) . '/') : (realpath($url));
			if (DIRECTORY_SEPARATOR != '/')
			{
				$url = str_replace(DIRECTORY_SEPARATOR, '/', $url);
			}
			$url = str_replace($this->get_root(self::DIR), $this->get_root(), $url);
			if ($pos !== FALSE)
			{
				$url = $url . $get;
			}
		}
		// Norme W3C & = &amp;.
		$url = str_replace('&amp;', '&', $url);
		$url = str_replace('&', '&amp;', $url);
		return $url;
	}

	/**
	 * Définie le Code HTTP de retour.
	 * @param int @number Numéro du code de retour.
	 */
	public function status_code($number)
	{
		switch ($number)
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
	}
	
	/**
	 * Vérifie que la request envoyé au site provienne bien de ce même site.
	 * @return boolean
	 */
	public static function check_source()
	{
		$root = self::get_root();
		$request = (array_key_exists('HTTP_REFERER',$_SERVER)) ? ($_SERVER['HTTP_REFERER']) : (NULL);
		return ($root == substr($request,0,strlen($root)));
	}
	
	/**
	 * Retourne une instance de la classe avec les arguments correctement ordonnés selon le constructeur de la classe.
	 * @param array $args Tableau d'arguments du constructeur.
	 * @return Site
	 */
	protected static function __create($args)
	{
	    return new self($args[0], $args[1], $args[2]);
	}
}
?>