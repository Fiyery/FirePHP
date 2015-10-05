<?php
/**
 * Thread gère l'exécution de script en parallèle (multiplexage) par le biais de l'extension CURL.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @copyright 2011-2015 Yoann Chaumin
 */
class Thread extends Singleton
{

	protected static $_instance = NULL;

	private $_threads = array();

	private $_responses = array();

	private $_multi_handler = NULL;

	private $_header = array();
	
	private $_cookie = NULL;

	protected function __construct()
	{
		$this->multi_handler = curl_multi_init();
	}

	/**
	 * Transmettre la session dans les scripts cibles. Les scripts seront exécutés séquentiellement sauf si session_write_close() est appêlée en début de script.
	 */
	public function load_session()
	{
		$this->_cookie = 'PHPSESSID=' . session_id() . '; path=/';
	}

	public function curl($url)
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_multi_add_handle($this->multi_handler, $ch);
		$i = count($this->_post);
		if (count($this->_post[$i - 1]) > 0)
		{
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_post[$i - 1]);
		}
		if (count($this->_header) > 0)
		{
			curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_header);
		}
		if ($this->_cookie != NULL)
		{
			curl_setopt($ch, CURLOPT_COOKIE, $this->_cookie);
			session_write_close();
		}
		curl_close($ch);
		return $ch;
	}

	public function add($callable, $post = array())
	{
		$this->_post[] = $post;
		$this->_threads[] = $this->curl($callable);
	}

	public function run()
	{
		$running = NULL;
		do
		{
			curl_multi_exec($this->multi_handler, $running);
		}
		while($running > 0);
		$i = 0;
		foreach($this->_threads as $thread)
		{
			$this->_responses[$i] = json_decode(curl_multi_getcontent($thread));
			$i ++;
		}
		if ($this->_cookie != NULL)
		{
			session_start();
		}
		foreach($this->_threads as $thread)
		{
			curl_multi_remove_handle($this->multi_handler, $thread);
		}
		curl_multi_close($this->multi_handler);
	}

	public static function send($var)
	{
		echo json_encode($var);
	}

	public function response($id)
	{
		return (isset($this->_responses[$id])) ? ($this->_responses[$id]) : (NULL);
	}
}
?>