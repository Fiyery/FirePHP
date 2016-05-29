<?php
/**
 * Request est un moyen de gestion des requêtes simplifié en plus d'avoir quelques fonctions utiles.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses Singleton
 */
class Request extends Singleton
{
	/**
	 * Instance de singleton.
	 * @var Request
	 */
	protected static $_instance = NULL;
	
	/**
	 * Liste des balises HTML dangereuses.
	 * @var array<string>
	 */
	private $_tags;
	
	/**
	 * Tableau des valeurs de la requête.
	 * @var array
	 */
	private $_values;
	
	/**
	 * Méthode de la requête.
	 * @var string
	 */
	private $_method = NULL;
	
	/**
	 * Chemin demandé par la requête.
	 * @var string
	 */
	private $_path = NULL;
	
	/**
	 * Source de la requête.
	 * @var string
	 */
	private $_source = NULL;
	
	/**
	 * Constructeur.
	 */
	protected function __construct()
	{
		$this->_tags = array('script','style','iframe','object','frameset','frame','img','embed');
		$this->_values = $_REQUEST;		
		$this->clean_magic_quote();
	}
	
	/**
	 * Récupère un paramètre de la requete GET.
	 * @param string $name Nom du paramètre.
	 * @return string Valeur du paramètre ou NULL.
	 */
	public function get($name)
	{
		return (isset($_GET[$name])) ? ($_GET[$name]) : (NULL);
	}

	/**
	 * Récupère un paramètre de la requete POST.
	 * @param string $name Nom du paramètre.
	 * @return string Valeur du paramètre ou NULL.
	 */
	public function post($name)
	{
		return (isset($_POST[$name])) ? ($_POST[$name]) : (NULL);
	}

	/**
	 * Récupère un paramètre de la classe request ($_REQUEST ou perso).
	 * @param string $name Nom du paramètre.
	 * @return string Valeur du paramètre ou NULL.
	 */
	public function __get($name)
	{
		return (isset($this->_values[$name])) ? ($this->_values[$name]) : (NULL) ;
	}
	
	/**
	 * Ajoute un paramètre personnalisé à la requête.
	 * @param string $name Nom du paramètre.
	 * @param string $value Valeur du paramètre.
	 */
	public function __set($name, $value)
	{
		$this->_values[$name] = $value;
	}
	
    /**
     * Vérifie si une variable existe dans la requête.
     * @param string $name Nom de la variable.
     * @return boolean
     */
	public function __isset($name)
	{
	    return isset($this->_values[$name]);
	}
	
	/**
	 * Supprime les caractères "\" ajouter par magic_quote si cette dernière est activée.
	 * @param string $methode Méthode à purger parmi get, post et request. Par défaut, toutes les méthodes seront purgées.
	 * @param string $name Nom du paramètre à nettoyer. Par défaut, tous les paramètres seront nettoyés.
	 * @return boolean
	 */
	public function clean_magic_quote($methode=NULL,$name=NULL)
	{
		if (get_magic_quotes_gpc())
		{
			if ($name != NULL && $methode != NULL)
			{
				switch (strolower($methode))
				{
					case 'get' :
						{
							$_GET[$name] = array_map('Request::clean_quote',$_GET[$name]);
							break;
						}
					case 'post' :
						{
							$_POST[$name] = array_map('Request::clean_quote',$_POST[$name]);
							break;
						}
					case 'request' :
						{
							$_REQUEST[$name] = array_map('Request::clean_quote', $_REQUEST[$name]);
							break;
						}
				}
			}
			elseif ($methode != NULL)
			{
				switch (strolower($methode))
				{
					case 'get' :
						{
							$_GET = array_map('Request::clean_quote',$_GET);
							break;
						}
					case 'post' :
						{
							$_POST = array_map('Request::clean_quote', $_POST);
							break;
						}
					case 'request' :
						{
							$_REQUEST = array_map('Request::clean_quote', $_REQUEST);
							break;
						}
				}
			}
			else
			{
				$_GET = array_map('Request::clean_quote',$_GET);
				$_POST = array_map('Request::clean_quote',$_POST);
				$_REQUEST = array_map('Request::clean_quote',$_REQUEST);
			}
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Supprime les balises HTML dangereuses du texte.
	 * @param string $text Texte à nettoyer.
	 * @return string Texte filtré.
	 */
	public function suppr_html_dangerous($text)
	{
		$continue = FALSE;
		foreach ($this->_tags as $t)
		{
			if (preg_match('/<\/?(\s)*'.$t.'(\s)*(.*=(\'|").*(\'|"))*\/?>/',$text))
			{
				$text = preg_replace('/<\/?(\s)*'.$t.'(\s)*(.*=(\'|").*(\'|"))*\/?>/','',$text);
				$continue = TRUE;
			}
		}
		if ($continue)
		{
			$text = $this->suppr_html_dangerous($text);
		}
		return $text;
	}

	/**
	 * Protège contre les injections de code (Faille XSS).
	 * @param boolean $all TRUE pour supprimer toutes les balises HTML ou FALSE pour supprimer seulement les dangereuses.
	 */
	public function secure_html($all=FALSE)
	{
		if ($all)
		{
			foreach ($_POST as $n=>$p)
			{
				$_POST[$n] = htmlspecialchars($p,ENT_QUOTES);
			}
			foreach ($_GET as $n=>$p)
			{
				$_GET[$n] = htmlspecialchars($p,ENT_QUOTES);
			}
			foreach ($_REQUEST as $n=>$p)
			{
				$_REQUEST[$n] = htmlspecialchars($p,ENT_QUOTES);
			}
		}
		else
		{
			foreach ($_POST as $n=>$p)
			{
				$_POST[$n] = $this->suppr_html_dangerous($p);
			}
			foreach ($_GET as $n=>$p)
			{
				$_GET[$n] = $this->suppr_html_dangerous($p);
			}
			foreach ($_REQUEST as $n=>$p)
			{
				$_REQUEST[$n] = $this->suppr_html_dangerous($p);
			}
		}
	}


	/**
	 * Initialise la protection contre l'insertion de HTML dans la requête.
	 */
	public function check_html()
	{
		return $this->secure_html();
	}

	/**
	 * Vérifie si la requête à déjà été envoyé.
	 * @return boolean
	 */
	public function check_multipost()
	{	
		$array = &$_REQUEST;
		if (isset($_SESSION['__request']) && $_SESSION['__request'] == $array)
		{
			$array = array();
			return TRUE;
		}
		else
		{
			$_SESSION['__request'] = $array;
			return FALSE;
		}
	}
	
	/**
	 * Supprime les quotes d'une chaine ou d'un tableau.
	 * @param array|string $value Tableau ou chaîne à purger.
	 * @return array|string Tableau ou chaine purgée.
	 */
	public static function clean_quote($value)
	{
		return (is_array($value)) ? (array_map('Request::clean_quote', $value)) : (stripslashes($value));
	}
	
	/**
	 * Retourne la source de la requête
	 * @return string
	 */
	public function get_source()
	{
		if ($this->_source == NULL)
		{
		    $this->_source = (array_key_exists('HTTP_REFERER', $_SERVER)) ? ($_SERVER['HTTP_REFERER']) : (NULL);
		}
	    return $this->_source;
	}
	
	/**
	 * Retourne la méthode de la requête
	 * @return string
	 */
	public function get_method()
	{
		if ($this->_method == NULL)
		{
			$this->_method = (array_key_exists('REQUEST_METHOD', $_SERVER)) ? ($_SERVER['REQUEST_METHOD']) : (NULL);
		}
		return $this->_method;
	}
	
	/**
	 * Retourne le chemin demandé de la requête
	 * @param string $root Dossier racine du projet.
	 * @return string
	 */
	public function get_path($root=NULL)
	{
	    if ($this->_path == NULL)
		{
		    $path = strtolower(str_replace('?'.$_SERVER['QUERY_STRING' ], '', $_SERVER['REQUEST_URI']));
		    if (is_string($root))
		    {
    		    if (file_exists($root) == FALSE)
    		    {
    		        return FALSE;
    		    }
    		    $root = strtolower($root);
                while (stripos($path, basename($root)) !== FALSE)
                {
                    $path = str_replace(basename($root).'/', '', $path);
                    $root = dirname($root);
                }    
		    }
		    $this->_path = $path;
		    
		}
		return $this->_path;
	}
}