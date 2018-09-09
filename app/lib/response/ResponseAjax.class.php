<?php
/**
 * Response gère le retour du serveur lors des appels Ajax.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 */
class ResponseAjax implements ResponseInterface
{
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
	 * Gestion des données de retours sur l'ajax.
	 * @var ResponseAjaxData
	 */
	protected $_data = NULL;

	/**
	 * Code retour.
	 * @var int
	 */
	protected $_status_code = 200;
	
	/**
	 * Constructeur.
	 */
	public function __construct()
	{
		$this->_body = new ResponseBody();
		$this->_header = new ResponseHeader();
		$this->_alert = new ResponseAlert();
		$this->_data = new ResponseAjaxData();
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
		return $this->_body;
	}

    /**
     * Retourne la classe de gestion données Ajax.
     * @return ResponseAjaxData
     */
	public function data() : ResponseAjaxData
	{
		return $this->_data;
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
	 * Ecrit la réponse et termine le script.
	 */
	public function send()
	{
		$msg = $this->alert()->lists();
		$count = count($msg);
		if ($count === 0)
		{
			$msg = ["success" => "1"];
		}
		else 
		{
			for($i = 0; $i < $count; $i++)
			{
				$msg[str_replace("alert_", "", $msg[$i]["type"])] = $msg[$i]["message"];
				unset($msg[$i]);
			}
		}
		echo json_encode([
			"msg" => $msg,
			"data" => $this->data()->lists()
		]);
		exit();
	}
}
?>