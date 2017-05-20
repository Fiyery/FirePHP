<?php
/**
 * Thread gère l'exécution de script en parallèle (multiplexage) par le biais de l'extension cURL.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 */
class Thread 
{
	/**
	 * Contient les ressource cURL.
	 * @var array
	 */
	private $_threads = [];

	/**
	 * Liste des retours des appels.
	 * @var array
	 */
	private $_responses = [];

	/**
	 * Gestionnaire d'appels multi cURL.
	 * @var ressource
	 */
	private $_multi_handler = NULL;

	/**
	 * Liste des paramètres à passer en POST.
	 * @var array
	 */
	private $_post = [];

	/**
	 * Constructeur.
	 */
	public function __construct()
	{
		$this->_multi_handler = curl_multi_init();
	}

	/**
	 * Génère la ressource cURL.
	 * @param string $url URL à appeler.
	 * @return ressource
	 */
	private function _curl(string $url) 
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_multi_add_handle($this->_multi_handler, $ch);
		$i = count($this->_post);
		if (isset($this->_post[$i - 1]) && count($this->_post[$i - 1]) > 0)
		{
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_post[$i - 1]);
		}
		if (isset($_COOKIE[session_name()]))
		{
			curl_setopt($ch, CURLOPT_COOKIESESSION, TRUE);
			curl_setopt($ch, CURLOPT_COOKIE, session_name().'='.$_COOKIE[session_name()].'; path=/;');
		}
		return $ch;
	}

	/**
	 * Ajoute un appel URL au Thread.
	 * @param string $url Adresse URL.
	 * @param array $post Variables à passer.
	 */
	public function add(string $url, $post = [])
	{
		$this->_post[] = $post;
		$this->_threads[] = $this->_curl($url);
	}

	/**
	 * Lance les appels en simultané.
	 */
	public function run()
	{
		session_write_close();
		$running = NULL;
		do
		{
			curl_multi_exec($this->_multi_handler, $running);
		}
		while($running > 0);
		$i = 0;
		foreach($this->_threads as $thread)
		{
			$this->_responses[$i] = json_decode(curl_multi_getcontent($thread));
			curl_multi_remove_handle($this->_multi_handler, $thread);
			$i++;
		}
		curl_multi_close($this->_multi_handler);
		session_start();
	}

	/**
	 * Retourne le résultat d'un appel.
	 * @param int $index Numéro de l'appel.
	 * @return stdClass 
	 */
	public function response(int $index) : stdClass
	{
		return (isset($this->_responses[$index])) ? ($this->_responses[$index]) : (NULL);
	}
}
?>