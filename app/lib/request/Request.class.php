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
	 * @var string[]
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
		$this->_values = $_REQUEST;	
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
	 * Récupère un paramètre de la classe Request protégé des injections.
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
	 * Protège une valeur contre les injections de code (Faille XSS).
	 * @param string $name 
	 * @return string
	 */
	public function secure(string $name) : string
	{
		return htmlentities($this->_values[$name]);
	}

	/**
	 * Protège contre les injections de code (Faille XSS).
	 * @return Request
	 */
	public function secure_values() : Request
	{
		foreach ($this->_values as $name => $v)
		{
			$this->$name = $this->secure($name);
		}
		return $this;
	}
	
	/**
	 * Retourne la source de la requête
	 * @return string
	 */
	public function source()
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
	public function method()
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
	public function path($root=NULL)
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