<?php
/**
 * Browser renseigne toute sorte d'informations sur le navigateur du client.
 * Attention les informations ne sont données qu'à titre indicatif et peuvent tout à faire être fausse. 
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 */
class Browser 
{
    /**
     * Nom du navigateur.
     * @var string
     */
	private $_name = NULL;
	
	/**
	 * Version du navigateur.
	 * @var string
	 */
	private $_version = NULL;
	
	/**
	 * Langage utilisée par le navigateur.
	 * @var string
	 */
	private $_lang = NULL;
	
	/**
	 * Nom du système d'exploitation.
	 * @var string
	 */
	private $_plateform = NULL;
	
	/**
	 * Contient les informations du browscap.ini.
	 * @var array
	 */
	private $_data = array();

    /**
     * Constructeur.
     * @param string $file Chemin du fichier de browscap.
     */
	public function __construct($file=NULL)
	{
		if (get_cfg_var('browscap'))
		{
			$data = get_browser(NULL,TRUE);
		}
		elseif (file_exists($file))
		{
			$data = $this->get_browser($file);
		}
		if (isset($data) && is_array($data))
		{
			foreach ($data as $name => $value)
			{
				$this->_data[$name] = $value; 
			}
		}
	}

	/**
	 * Récupère les informations d'un fichier browscap.
	 * @param string $filename_browscap Chemin du fichier.
	 * @return array
	 */
	private function get_browser($filename_browscap)
	{
		if (file_exists($filename_browscap) == FALSE)
		{
			return NULL;
		}
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		$browscap_ini = defined('INI_SCANNER_RAW') ? (parse_ini_file($filename_browscap,true,INI_SCANNER_RAW)) : (parse_ini_file($filename_browscap,true));
		$browscap_path = $filename_browscap;
		uksort($browscap_ini, array($this,'sort_browscap'));
		$browscap_ini = array_map(array($this,'lower_browscap'),$browscap_ini);
		
		$cap = NULL;
		$array1 = array('\\','.','?','*','^','$','[',']','|','(',')','+','{','}','%');
		$array2 = array('\\\\','\\.','.','.*','\\^','\\$','\\[','\\]','\\|','\\(','\\)','\\+','\\{','\\}','\\%');
		
		foreach ($browscap_ini as $key => $value)
		{
			if (($key == '*') || (is_array($value) && array_key_exists('parent', $value))) 
			{
				$key_ereg = '^'.str_replace($array1, $array2, $key).'$';
				if (preg_match('%'.$key_ereg.'%i', $user_agent))
				{
					$cap = array('browser_name_regex'=>strtolower($key_ereg), 'browser_name_pattern'=>$key) + $value;
					$maxDeep = 8;
					while (array_key_exists('parent',$value) && array_key_exists($parent=$value['parent'],$browscap_ini)&&(--$maxDeep>0))
					{
						$cap += ($value = $browscap_ini[$parent]);
					}
					break;
				}
			}
		}
		return $cap;
	}
	
	/**
	 * Trie spécifiquementun fichier browscap.
	 * @param string $a Valeur 1 du tableau.
	 * @param string $b Valeur 2 du tableau.
	 * @return int
	 */
	private function sort_browscap($a, $b)
	{
		$sa = strlen($a);
		$sb = strlen($b);
		if ($sa > $sb)
		{
			return -1;
		}
		elseif ($sa < $sb) 
		{
			return 1;
		}
		else 
		{
			return strcasecmp($a, $b);
		}
	}

	/**
	 * Met en minuscule les information du fichier browscap.
	 * @param string $r Information à formater.
	 * @return string
	 */
	private function lower_browscap($r) 
	{
		return array_change_key_case($r, CASE_LOWER);
	}

	/**
	 * Récupère le système d'exploitation du visiteur.
	 * @return string
	 */
	public function get_plateform()
	{
		if ($this->_plateform == NULL)
		{
		    if (isset($this->_data['platform_description']) && $this->_data['platform_description'] != 'unknown')
		    {
		        $this->_plateform = $this->_data['platform_description'];
		    }
		    else 
		    {
		        $user_agent = $_SERVER['HTTP_USER_AGENT'];
		        $list = array(
		        	'windows nt 5.1' 			=> 'Microsoft Windows XP',
		        	'offbyone; windows 2000'	=> 'Microsoft Windows XP',
		        	'windows nt 6.1' 			=> 'Microsoft Windows 7',
		        	'windows nt 6.0; wow64'		=> 'Microsoft Windows Vista (64bits)',
		        	'windows nt 6.0; win64'		=> 'Microsoft Windows Vista (64bits)',
		        	'windows nt 6.0' 			=> 'Microsoft Windows Vista',
		        	'windows 95' 				=> 'Microsoft Windows 95',
		        	'windows nt 5.0' 			=> 'Microsoft Windows 2000',
		        	'windows nt 5.3' 			=> 'Microsoft Windows Server 2003',
		        	'windows nt' 				=> 'Microsoft Windows NT',
		        	'windows 98' 				=> 'Microsoft Windows 98',
		        	'windows ce' 				=> 'Microsoft Windows Mobile',
		        	'windows phone os' 			=> 'Microsoft Windows Phone',
		        	'cygwin_nt' 				=> 'Microsoft Windows 2000',
		        	'windows 2000' 				=> 'Microsoft Windows 2000',
		        	'os/2' 						=> 'Microsoft OS/2',
		        	'mac os x' 					=> 'Mac OS X',
		        	'mac_powerpc' 				=> 'Mac OS X',
		        	'macintosh' 				=> 'Macintosh',
		        	'linux x86_64'				=> 'Linux (64 bits)',
		        	'linux' 					=> 'Linux',
		        	'libwww-fm' 				=> 'Linux',
		        	'khtml' 					=> 'Linux',
		        	'android' 					=> 'Android',
		        	'iphone os' 				=> 'iPhone OS',
		        	'freebsd' 					=> 'FreeBSD',
		        	'haiku' 					=> 'Haiku',
		        	'symbianos' 				=> 'Symbian OS',
		        	'sunos' 					=> 'Open Solaris',
		        	'symbian-crystal' 			=> 'Symbian OS',
		        	'nintendo wii' 				=> 'Nintendo Wii',
		        	'playstation portable' 		=> 'PlayStation Portable'
		        );
		        $name_os = NULL;
		        while ((list($needle, $name) = each($list)) && $name_os == NULL)
		        {
		        	if (stripos($user_agent, $needle) !== FALSE)
		        	{
		        		$name_os = $name;
		        	}
		        }
		        $this->_plateform = $name_os;
		    }
		}
		return $this->_plateform;
	}

	/**
	 * Récupère la version du navigateur du visiteur.
	 * @return string
	 */
	public function get_version()
	{
	    if ($this->_version == NULL)
	    {
	    	if (isset($this->_data['version']) && $this->_data['version'] != '0.0')
	    	{
	    		$this->_version = $this->_data['version'];
	    	}
	    	else
	    	{
                $user_agent = $_SERVER['HTTP_USER_AGENT'];
    			if (preg_match('/.+(?:rv|version|chrome|msie)[\/: ]([\d.]+)/i', $user_agent, $matches))
    			{
    				$this->_version = $matches[1];
    			}
    			else
    			{
    				$this->_version = NULL;
    			}
	    	}
		}
		return $this->_version;
	}

	/**
	 * Récupère le nom du navigateur.
	 * @return string
	 */
	public function get_name()
	{
	    if ($this->_name == NULL)
	    {
	    	if (isset($this->_data['browser']) && $this->_data['browser'] != 'Default Browser')
	    	{
	    		$this->_name = $this->_data['browser'];
	    	}
	    	else
	    	{
    			$user_agent = $_SERVER['HTTP_USER_AGENT'];
    			$navs = array(
    				'msie' 									=> 'IE',
    				'firefox'								=> 'Firefox',
    				'chrome'								=> 'Chrome',
    				'opera'									=> 'Opera',
    				'safari'								=> 'Safari',
    				'webkit'								=> 'Safari',
    				'netscape'								=> 'Netscape',
    				'seamonkey' 							=> 'SeaMonkey',
    				'icab'									=> 'iCab (Crystal Atari Browser)',
    				'microsoft pocket internet explorer'	=> 'Microsoft Pocket Internet Explorer',
    				'mspie' 								=> 'Microsoft Pocket Internet Explorer',
    				'konqueror' 							=> 'Konqueror',
    				'lunascape' 							=> 'Lunascape',
    				'lynx' 									=> 'Lynx',
    				'minimo'								=> 'Minimo',
    				'nokia' 								=> 'Nokia',
    				'offbyone' 								=> 'OffByOne',
    				'omniweb' 								=> 'Omniweb',
    				'w3m' 									=> 'W3m',
    				'ia_archiver' 							=> 'Alexa Bot',
    				'ask jeeves'							=> 'Ask Jeeves Bot',
    				'curl' 									=> 'Curl',
    				'exabot' 								=> 'Exaled bot',
    				'ng' 									=> 'Exaled bot',
    				'exabot-thumbnails' 					=> 'Exaled bot',
    				'gamespyhttp' 							=> 'GameSpy Industries bot',
    				'gigabot' 								=> 'Gigablast bot',
    				'googlebot' 							=> 'Google bot',
    				'googlebot-image' 						=> 'Google bot (image)',
    				'grub-client' 							=> 'LookSmart Grub bot',
    				'yahoo! slurp' 							=> 'Yahoo! Search bot',
    				'slurp' 								=> 'Inktomi Slurp bot',
    				'msnbot' 								=> 'Microsoft MSN Search bot',
    				'scooter' 								=> 'AltaVista Scooter bot',
    				'wget' 									=> 'Wget bot',
    				'w3c_validator' 						=> 'W3C validator bot'
    			);
    			$this->_name = NULL;
    			while ((list($needle,$name) = each($navs)) && $this->_name == NULL)
    			{
    				if (stripos($user_agent, $needle) !== FALSE)
    				{
    					$this->_name = $name;
    				}
    			}
	    	}
		}
		return $this->_name;
	}

	/**
	 * Récupère la langue du navigateur.
	 * @return string
	 */
	public function get_lang()
	{
		if ($this->_lang == NULL) 
		{
			$user_agent = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
			$list = array(
				'af' => 'Africain',
				'sq' => 'Albanais',
				'ar-dz' => 'Algérien',
				'de' => 'Allemand',
				'de-at' => 'Allemand (Austrian)',
				'de-li' => 'Allemand (Liechtenstein)',
				'de-lu' => 'Allemand (Luxembourg)',
				'de-ch' => 'Allemand (Suisse)',
				'en-us' => 'Américain',
				'en' => 'Anglais',
				'en-za' => 'Anglais (Afrique du sud)',
				'en-bz' => 'Anglais (Bélize)',
				'en-gb' => 'Anglais (Grande Bretagne)',
				'ar' => 'Arabe',
				'ar-sa' => 'Arabe (Arabie Saoudite)',
				'ar-bh' => 'Arabe (Bahreïn)',
				'ar-ae' => 'Arabe (Emirat arabe uni)',
				'en-au' => 'Australien',
				'eu' => 'Basque',
				'nl-be' => 'Belge',
				'be' => 'Biélorussie',
				'bg' => 'Bulgarre',
				'en-ca' => 'Canadien',
				'ca' => 'Catalan',
				'zh' => 'Chinois',
				'zh-hk' => 'Chinois (Hong-Kong)',
				'zh-cn' => 'Chinois (PRC)',
				'zh-sg' => 'Chinois (Singapourg)',
				'zh-tw' => 'Chinois (Taïwan)',
				'ko' => 'Coréein',
				'cs' => 'Crète',
				'hr' => 'Croate',
				'da' => 'Danois',
				'ar-eg' => 'Egyptien',
				'es' => 'Espagnol',
				'es-ar' => 'Espagnol (Argentine)',
				'es-bo' => 'Espagnol (Bolivie)',
				'es-cl' => 'Espagnol (Chilie)',
				'es-co' => 'Espagnol (Colombie)',
				'es-cr' => 'Espagnol (Costa Rica)',
				'es-sv' => 'Espagnol (El Salvador)',
				'es-ec' => 'Espagnol (Equateur)',
				'es-gt' => 'Espagnol (Guatemala)',
				'es-hn' => 'Espagnol (Honduras)',
				'es-mx' => 'Espagnol (Mexique)',
				'es-ni' => 'Espagnol (Nicaragua)',
				'es-pa' => 'Espagnol (Panama)',
				'es-py' => 'Espagnol (Paraguay)',
				'es-pe' => 'Espagnol (Pérou)',
				'es-pr' => 'Espagnol (Puerto Rico)',
				'en-tt' => 'Espagnol (Trinidad)',
				'es-uy' => 'Espagnol (Uruguay)',
				'es-ve' => 'Espagnol (Venezuela)',
				'et' => 'Estonien',
				'sx' => 'Estonien',
				'fo' => 'Faeroese',
				'fi' => 'Finlandais',
				'fr' => 'Français',
				'fr-fr' => 'Français',
				'fr-be' => 'Français (Belgique)',
				'fr-ca' => 'Français (Canada)',
				'fr-lu' => 'Français (Luxembourg)',
				'fr-ch' => 'Français (Suisse)',
				'gd' => 'Galicien',
				'el' => 'Gréc',
				'he' => 'Hébreux',
				'nl' => 'Hollandais',
				'hu' => 'Hongrois',
				'in' => 'Indonésien',
				'hi' => 'Indou',
				'fa' => 'Iranien',
				'ar-iq' => 'Iraquien',
				'en-ie' => 'Irlandais',
				'is' => 'Islandais',
				'it' => 'Italien',
				'it-ch' => 'Italien (Suisse)',
				'en-jm' => 'Jamaicain',
				'ja' => 'Japonais',
				'ar-jo' => 'Jordanien',
				'ar-kw' => 'Koweitien',
				'lv' => 'Lettische',
				'ar-lb' => 'Libanais',
				'lt' => 'Littuanien',
				'ar-ly' => 'Lybien',
				'mk' => 'Macédoine',
				'ms' => 'Malésien',
				'mt' => 'Maltais',
				'ar-ma' => 'Marocain',
				'en-nz' => 'Néo-zélandais',
				'no' => 'Norvégien (bokmal)',
				'no' => 'Norvégien (Nynorsk)',
				'ar-om' => 'Oman',
				'pl' => 'Polonais',
				'pt' => 'Portugais',
				'pt-br' => 'Portugais (Brésil)',
				'ar-qa' => 'Quatar',
				'rm' => 'Rhaeto-Romanic',
				'ro' => 'Roumain (Moldavie)',
				'ro-mo' => 'Roumain (Moldavie)',
				'ru' => 'Russe',
				'ru-mo' => 'Russe (Moldavie)',
				'sr' => 'Serbe (Cyrillic)',
				'sr' => 'Serbe (Latin)',
				'sk' => 'Slovaque',
				'sl' => 'Slovéne',
				'sb' => 'Sorbian',
				'sv' => 'Suèdois',
				'sv-fi' => 'Suèdois (Finlande)',
				'ar-sy' => 'Syrien',
				'th' => 'Thaïlandais',
				'ts' => 'Tsonga (Afrique du sud)',
				'tn' => 'Tswana (Afrique du sud)',
				'ar-tn' => 'Tunisien',
				'tr' => 'Turc',
				'uk' => 'Ukrainien',
				'ur' => 'Urdu',
				'vi' => 'Vietnamien',
				'xh' => 'Xhosa (Afrique)',
				'ar-ye' => 'Yémen',
				'ji' => 'Yiddish',
				'zu' => 'Zulu (Afrique)'
			);
			$langs = strtolower(preg_replace('#^([a-z\-,]+).*#i','$1', $user_agent));
			$langs = explode(',',$langs);
			$this->lang = 'Unknown';
			foreach ($langs as $l)
			{
				if (array_key_exists($l, $list)) 
				{
					$this->_lang = $list[$l]; 
				}
			}
		}
		return $this->_lang;
	}
	
	/**
	 * Vérifie si le visiteur est un robot. 
	 * @return boolean NULL si aucune information.
	 */
	public function is_crawler()
	{
		return (isset($this->_data['crawler'])) ? ($this->_data['crawler']) : (NULL);
	}
	
	/**
	 * Vérifie si le navigateur accepte les cookies.
	 * @return boolean NULL si aucune information.
	 */
	public function accept_cookies()
	{
		return (isset($this->_data['cookies'])) ? ($this->_data['cookies']) : (NULL);
	}
	
	/**
	 * Vérifie si le navigateur accepte le javascript.
	 * @return boolean NULL si aucune information.
	 */
	public function accept_javascript()
	{
		return (isset($this->_data['javascript'])) ? ($this->_data['javascript']) : (NULL);
	}
	
	/**
	 * Vérifie si le navigateur accepte les applets java.
	 * @return boolean NULL si aucune information.
	 */
	public function accept_java()
	{
		return (isset($this->_data['javaapplets'])) ? ($this->_data['javaapplets']) : (NULL);
	}
}
?>