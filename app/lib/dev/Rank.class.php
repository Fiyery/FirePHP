<?php
class Rank
{
	private $words;
	private $url;
	private $max_page;
	private $search_engine;
	private $ranking;
	private $page;
	
	// Constructeur.
	public function __construct($url,$words,$search_engine='GOOGLE',$max_page=10)
	{
		$url = str_replace('http://','',$url);
		$url = str_replace('www.','',$url);
		$url = (substr($url,-1) == '/') ? (substr($url,0,-1)) : ($url);
		$this->url = $url;
		$this->words = (is_array($words)) ? ($words) : (explode(' ',trim($words)));
		$this->search_engine = (in_array($search_engine,array('GOOGLE','BING'))) ? ($search_engine) : ('GOOGLE');
		$this->max_page = $max_page;
		$this->page = FALSE;
		$this->ranking = FALSE;
		$this->search_position();
	}
	
	// Fonction qui retourne l'url de la page voulu.
	public function get_url($page=0)
	{
		if ($this->search_engine == 'GOOGLE')
		{
			$words = implode('+',$this->words);
			$start = $page * 10;
			$params_url = array();
			$params_url[] = 'q='.$words;					// Mots de la recherche.
			$params_url[] = 'oq='.$words;					// Mots de la recherche en mode OR.
			$params_url[] = 'start='.$start;				// Premier résultat à afficher.
			$params_url[] = 'ie=utf-8';						// Encodage entré.
			$params_url[] = 'oe=utf-8';	 					// Encodage sortie.
			$params_url[] = 'hl=fr';	 					// Langage du navigateur.
			$params_url[] = 'lr=lang_fr';					// Langage de la recherche.
			$params_url[] = 'client=firefox-beta';			// Client de la recherche.
			$params_url[] = 'rls=org.mozilla:fr:official';	// Client de la recherche.
			$params_url[] = 'biw=1600';						// Pixel largeur de l'écrant du client.
			$params_url[] = 'bih=753';						// Pixel hauteur de l'écrant du client.
			return 'http://www.google.fr/search?'.implode('&',$params_url);
		}
		elseif ($this->search_engine == 'BING')
		{
			$words = implode('+',$this->words);
			$start = $page * 10 + 1;
			$params_url = array();
			$params_url[] = 'q='.$words;					// Mots de la recherche.
			$params_url[] = 'first='.$start;				// Premier résultat à afficher. Commence par 1.
			return 'http://www.bing.com/search?'.implode('&',$params_url);
		}
		return NULL;
	}
	
	// Fonction qui retourne le contenu de la page voulu.
	public function get_content($page=0)
	{
		return file_get_contents($this->get_url($page));
	}
	
	// Fonction qui met à jour le classement du site.
	public function search_position()
	{
		$find = 0;
		for($i=0;$i<$this->max_page && $find == FALSE;$i++)
		{
			$content = $this->get_content($i);
			if ($this->search_engine == 'GOOGLE' || $this->search_engine == 'BING')
			{
				preg_match_all('/<cite>(.*)<\/cite>/iU',$content,$positions);
				for($j=0;$j<10 && $find == FALSE;$j++)
				{
					$current_url = strip_tags($positions[0][$j]);
					if (stripos($current_url,$this->url) !== FALSE)
					{
						$find = TRUE;
						$this->page = $i + 1;
						$this->ranking = $i * 10 + $j + 1;
					}
				}
			}
		}
	}

	// Fonction qui retourne le classement.
	public function get_ranking()
	{
		return $this->ranking;
	}
	
	// Fonction qui retourne la page.
	public function get_page()
	{
		return $this->page;
	}
}
?>