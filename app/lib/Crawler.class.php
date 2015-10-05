<?php
/**
 * Crawler est un système qui analyse les pages d'un site et récupère les codes HTTP de ces pages.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @copyright 2011-2015 Yoann Chaumin
 */
class Crawler
{
	/**
	 * Liste de toutes les adresses.
	 * @var array<string>
	 */
	private $_urls = array();
	
	/**
	 * Racine du site à crawler.
	 * @var string
	 */
	private $_root = NULL;
	
	/**
	 * Limite de pages à crawler.
	 * @var int
	 */
	private $_limit = 1;
	
	/**
	 * Liste de pages crawlées.
	 * @var array<string>
	 */
	private $_crawled = array();
	
	/**
	 * Nombre de pages crawlées.
	 * @var int
	 */
	private $_nb_crawled = 0;
	
	/**
	 * Timestamps du démarrage du scan.
	 * @var number
	 */
	private $_time_begin = NULL;
	
	/**
	 * Timestamps de la fin du scan.
	 * @var number
	 */
	private $_time_end = NULL;
	
	/**
	 * Constructeur.
	 */
	public function __construct()
	{
		
	}
	
	/**
	 * Crawled un ensemble de pages.
	 * @param string $url Adresse de la première page à crawler.
	 * @param boolean $only_link Si TRUE, seulement les liens des balises <a> seront extraits.
	 * @param boolean $external Si TRUE, tous les liens trouvés  seront parcourus, sinon, seulement ceux du site.
	 * @return string
	 */
	public function scan($url, $only_link=TRUE, $external=FALSE)
	{
		if ($this->init($url) == FALSE)
		{
			return NULL;
		}
		$count = 0;
		$limit = $this->_limit;
		$this->_time_begin = time();
		$html_content = NULL;
		while ($count < $limit && isset($this->_urls[$count]))
		{
			while (isset($this->_urls[$count]) && $this->_root != substr($this->_urls[$count],0,strlen($this->_root)))
			{
				$limit++;
				$count++;
			}
			if (isset($this->_urls[$count]))
			{
				$curl = curl_init($this->_urls[$count]);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($curl, CURLOPT_COOKIESESSION, TRUE);
				$html = curl_exec($curl);
				$html_content = $html;
				$data = curl_getinfo($curl);
				if(curl_errno($curl))
				{
					return NULL;
				}
				curl_close($curl);
				$this->_nb_crawled++;
				$http_response = ($data['http_code'] == 0) ? (404) : ($data['http_code']);
				$this->_crawled[$http_response][] = $this->_urls[$count];
				if (stripos($data['content_type'],'text/html') !== FALSE  && $http_response == 200)
				{
					$html = str_replace('xmlns="http://www.w3.org/1999/xhtml"', '', $html);
					if ($only_link)
					{
						preg_match_all('/<a[^>]*href=["|\']([^#][^"\']*)["|\']/iU',$html,$match);
						$list = $match[1];
					}
					else 
					{
						preg_match_all('/(https?:\/\/(www\.)?(([a-zA-Z0-9-]){2,}\.?){1,4}([a-z]){2,6}(\/([\w-\.#:+?%=&;]*)?)?\/?([\w-\.#:+?%=&;\/]*))/',$html,$match);
						$list = $match[0];
					}
					$max = count($list);
					$i = 0;
					while ($i < $max)
					{
						$m = str_replace('&amp;','&',$list[$i]);
						if (in_array($m,$this->_urls) == FALSE)
						{
							$this->_urls[] = $m;
						}
						$i++;
					}
				}
				$count++;
			}
		}
		$this->_time_end = time();
		return $html_content;
	}
	
	/**
	 * Initialise la connexion avec la page.
	 * @param string $url Adresse de connexion. 
	 * @return boolean
	 */
	private function init($url)
	{
		$url = (substr($url,0,7) != 'http://') ? ('http://'.$url) : ($url);
		$this->_urls[0] = $url;
		$url = parse_url($url);
		if (is_array($url) == FALSE)
		{
			return FALSE;
		}
		$this->_root = $url['scheme'].'://'.$url['host'];
		return TRUE;
	}
	
	/**
	 * Définie le nombre de pages à crawler.
	 * @param int $limit Nombre de pages.
	 */
	public function set_limit($limit)
	{
		if (is_int($limit))
		{
			$this->_limit = $limit;
		}
	}
	
	/**
	 * Retourne les pages crawlées.
	 * @return array<int,array<string>> Tableau des pages crawled avec en clé le code HTTP.
	 */
	public function get_crawled()
	{
		return $this->_crawled;
	}
	
	/**
	 * Retourne le nombre de pages crawled.
	 * @return int
	 */
	public function get_nb_crawled()
	{
		return $this->_nb_crawled;
	}
	
	/**
	 * Nombre de secondes de l'analyse.
	 * @return number
	 */
	public function get_crawl_time()
	{
		return $this->_time_end - $this->_time_begin;
	}
}

?>