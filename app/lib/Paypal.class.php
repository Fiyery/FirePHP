<?php
/**
 * PayPal est une interface simplifiée de gestion de paiement en ligne via l'API NVP de PayPal.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @copyright 2011-2015 Yoann Chaumin
 */
class PayPal
{	
	/**
	 * Nom de la monnaie (EUR/USD)
	 * @var string
	 */
	const CURRENCY = 'EUR';
	
	/**
	 * Si TRUE, on utilise la sandbox de PayPal.
	 * @var boolean
	 */
	private $_sandbox;
	
	/**
	 * Identifiant de l'utilisateur de l'API.
	 * @var string
	 */
	private $_user = 'yoann.chaumin-facilitator_api1.gmail.com';
	
	/**
	 * Mot de passe de l'API.
	 * @var string
	 */
	private $_pass = '1401790958';
	
	/**
	 * Signature de l'API.
	 * @var string
	 */
	private $_signature = 'AiPC9BjkCyDFQXbSkoZcgqH3hpacAL0T9oU-ePRPpRkgjfibnKZTU1bu';
	
	/**
	 * Adresse de retour.
	 * @var string
	 */
	private $_return_url;
	
	/**
	 * Adresse d'annulation.
	 * @var unknown
	 */
	private $_cancel_url;
	
	/**
	 * Liste des produits de la transaction.
	 * @var unknown
	 */
	private $_products;
	
	/**
	 * Numéro de la version.
	 * @var string
	 */
	private $_version = 114.0;
	
	/**
	 * Message d'erreur.
	 * @var string
	 */
	private $_err_msg = NULL;
	
	/**
	 * Liste des paramètres de retour de la connexion.
	 * @var array<string>
	 */
	private $_response;
	
	/**
	 * Constructeur.
	 * @param string $user Identifiant de l'utilisateur de l'API.
	 * @param string $pass Mot de passe de l'API.
	 * @param string $signature Signature de l'API.
	 * @param boolean $sandbox Si TRUE, on utilise la sandbox de PayPal.
	 */
	public function __construct($user=NULL, $pass=NULL, $signature=NULL, $sandbox=TRUE)
	{
		$this->_sandbox = $sandbox;
		if ($user != NULL)
		{
			$this->_user = $user;
		}
		if ($pass != NULL)
		{
			$this->_pass = $pass;
		}
		if ($signature != NULL)
		{
			$this->_signature = $signature;
		}
		$this->_price = 0;
		$this->_response = array();
	}
	
	/**
	 * Retourne l'URL de l'API PayPal.
	 * @return string
	 */
	private function get_url_api()
	{
		if ($this->_sandbox)
		{
			return 'https://api-3t.sandbox.paypal.com/nvp'; 
		}
		else 
		{
			return 'https://api-3t.paypal.com/nvp'; 
		}
	}
	
	/**
	 * Retourne l'URL de paiement PayPal.
	 * @param string $token Token reçu par l'API NVP.
	 * @return string
	 */
	public function get_url_payment()
	{
		if (isset($this->_response['TOKEN']) == FALSE)
		{
			return NULL;
		}
		if ($this->_sandbox)
		{
			return 'https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&useraction=commit&token='.$this->_response['TOKEN'];
		}
		else
		{
			return 'https://www.paypal.com/webscr?cmd=_express-checkout&useraction=commit&token='.$this->_response['TOKEN'];
		}
	}
	
	/**
	 * Retourne la liste des informations obtenues de PayPal.
	 * @return array<string> 
	 */
	public function get_data()
	{
		return $this->_response;
	}
	
	/**
	 * Retourne l'identifiant de transaction.
	 * @return string
	 */
	public function get_transaction_id()
	{
		return (isset($this->_response['PAYMENTINFO_0_TRANSACTIONID'])) ? ($this->_response['PAYMENTINFO_0_TRANSACTIONID']) : (NULL);
	}
	
	/**
	 * Retourne le message de la dernière erreur.
	 * @return string
	 */
	public function get_error()
	{
		return $this->_err_msg;
	}
	
	/**
	 * Retourne le montant de la transaction.
	 * @return number Montant ou 0 si la transaction n'a pas été effectuée.
	 */
	public function get_amount()
	{
		return (isset($this->_response['PAYMENTINFO_0_AMT'])) ? ($this->_response['PAYMENTINFO_0_AMT']) : (0);
	}
	
	/**
	 * Définie les pages cibles.
	 * @param string $return_url Adresse de la page de retour.
	 * @param string $cancel_url Adresse de la page de retour en cas d'annulation.
	 */
	public function set_targets($return_url, $cancel_url)
	{
		$this->_return_url = $return_url;
		$this->_cancel_url = $cancel_url;
	}
	
	/**
	 * Ajoute un produit à la transaction
	 * @param number $price Prix TTC.
	 * @param string $name Nom du produit.
	 * @param string $desc Description du produit.
	 * @param int $quantity Nombre de produit.
	 */
	public function add_product($price, $name, $desc, $quantity=1)
	{
		if (is_numeric($price) && $price > 0 && is_scalar($name) && is_scalar($desc) && is_numeric($quantity))
		{
			$product = array(
				'price' => $price,
				'name' => $name,
				'desc' => $desc,
				'quantity' => $quantity
			);
			$this->_products[] = $product;
		}
	}
	
	/**
	 * Définie la version de l'API à utiliser.
	 * @param string $version Num�ro de la version.
	 */
	public function set_version($version)
	{
		$this->_version = $version;
	}
	
	/**
	 * Envoie une requêtes à l'API.
	 * @param array<string> $params
	 * @return string
	 */
	private function request($params)
	{
		$params = array_merge($params, array(
			'USER' => $this->_user,
			'PWD' => $this->_pass,
			'SIGNATURE' => $this->_signature,
			'VERSION' => $this->_version
		));
		$params = http_build_query($params);
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $this->get_url_api(),
			CURLOPT_POST => TRUE,
			CURLOPT_POSTFIELDS => $params,
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_SSL_VERIFYPEER => FALSE,
			CURLOPT_SSL_VERIFYHOST => FALSE,
			CURLOPT_VERBOSE => TRUE
		));
		$response = curl_exec($curl);
		if (curl_errno($curl))
		{
			$this->_err_msg = curl_error($curl);
			curl_close($curl);
			return FALSE;
		}
		curl_close($curl);
		parse_str($response, $this->_response);
		if ($this->_response['ACK'] != 'Success')
		{
			$this->_err_msg = $this->_response['L_ERRORCODE0'].' : '.$this->_response['L_LONGMESSAGE0'];
			return FALSE;
		}
		return TRUE;
	}
	
	/**
	 * Etablie la connexion avec l'API et permet de générer l'URL du bouton de paiement.
	 * @return boolean
	 */
	public function connect()
	{
		if (count($this->_products) == 0)
		{
			$this->_err_msg = 'No product found';
			return FALSE;
		}
		$params = array(
			'METHOD' => 'SetExpressCheckout',
			'RETURNURL' => $this->_return_url,
			'CANCELURL' => $this->_cancel_url,
			'PAYMENTREQUEST_0_CURRENCYCODE' => self::CURRENCY
		);
		$amount = 0;
		foreach ($this->_products as $n => $p)
		{
			$params['L_PAYMENTREQUEST_0_AMT'.$n] = $p['price'];
			$params['L_PAYMENTREQUEST_0_NAME'.$n] = $p['name'];
			$params['L_PAYMENTREQUEST_0_DESC'.$n] = $p['desc'];
			$params['L_PAYMENTREQUEST_0_QTY'.$n] = $p['quantity'];
			$amount += $p['price'] * $p['quantity'];
		}
		$params['PAYMENTREQUEST_0_AMT'] = $amount;
		if ($this->request($params) == FALSE)
		{
			return FALSE;
		}
		$url_payment = $this->get_url_payment($this->_response['TOKEN']);
		return TRUE;
	}
	
	/**
	 * Vérifie le paiement a déjà été effectué avant le commit ou effectué après.
	 * @return boolean
	 */
	public function is_paid()
	{
		$already = (isset($this->_response['CHECKOUTSTATUS']) && $this->_response['CHECKOUTSTATUS'] == 'PaymentActionCompleted');
		$commited = (isset($this->_response['PAYMENTINFO_0_PAYMENTSTATUS']) && $this->_response['PAYMENTINFO_0_PAYMENTSTATUS'] == 'Completed');
		return ($already || $commited);
	}
	
	/**
	 * Recoit une requete et la traite.
	 * @param string $token Token reçu de l'API PayPal.
	 * @return boolean 
	 */
	public function receive($token)
	{
		$params = array(
			'METHOD' => 'GetExpressCheckoutDetails',
			'TOKEN' => $token
		);
		if ($this->request($params) == FALSE)
		{
			return NULL;
		}
		return TRUE;
	}
	
	/**
	 * Effectue le paiment après avoir reçu les informations avec receive().
	 * @return boolean.
	 */
	public function commit()
	{
		if (isset($this->_response['PAYERID']) == FALSE)
		{
			$this->_err_msg = 'Invalid information for commit transaction';
			return FALSE;
		}
		$params = array(
			'METHOD' => 'DoExpressCheckoutPayment',
			'TOKEN' => $this->_response['TOKEN'],
			'PAYERID' => $this->_response['PAYERID'],
			'PAYMENTACTION' => 'Sale',
			'PAYMENTREQUEST_0_AMT' => $this->_response['PAYMENTREQUEST_0_AMT'],
			'PAYMENTREQUEST_0_CURRENCYCODE' => $this->_response['PAYMENTREQUEST_0_CURRENCYCODE'],
		);
		if ($this->request($params) == FALSE && $this->_response['PAYMENTINFO_0_ACK'] != 'Success')
		{
			return FALSE;
		}
		if ($this->_response['PAYMENTINFO_0_PAYMENTSTATUS'] != 'Completed')
		{
			$this->_err_msg = 'Paiement failed';
			return FALSE;
		}
		return TRUE;
	}
}
?>