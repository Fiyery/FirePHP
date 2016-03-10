<?php
/**
 * Route permet la liaison entre l'url et le traitement spécfique du serveur.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @copyright 2011-2015 Yoann Chaumin
 * @uses Request
 * @uses Singleton
 * @uses Error
 * @uses File
 */
class Route extends Singleton
{
    /**
     * Constant qui définie le format de retoure de la racine comme adresse url.
     * @var int
     */
    const URL = 1;
    
    /**
     * Constant qui définie le format de retoure de la racine comme chemin de fichier.
     * @var int
     */
    const DIR = 2;
    
    /**
     * Instance de Request.
     * @var Request 
     */
    private $_request = NULL;
    
    /**
     * Chemin de la racine du site.
     * @var string
     */
    private $_root_dir = NULL;
    
    /**
     * Adresse URL de la racine.
     * @var string
     */
    private $_root_www = NULL;
    
    /**
     * Routage courant.
     * @var stdClass
     */
    private $_current = NULL;
    
    /**
     * Nom du controller du routage.
     * @var string
     */
    private $_controller = NULL;
    
    /**
     * Nom du module du routage.
     * @var string
     */
    private $_module = NULL;
    
    /**
     * Nom de l'action du routage.
     * @var string
     */
    private $_action = NULL;
    
    /**
     * Liste des variables définies par routage dans l'URL.
     * @var array
     */
    private $_vars = array();
    
    /**
     * Nom par défaut des variables de routage suivi du numéro de la variable.
     * @var string
     */
    private $_default_name_var = 'var';
    
    /**
     * Liste des raccourcis utilisables dans la désignation de l'url dans la table de routage.
     * @var array
     */
    private $_shortcut = array(
    	'*' 	=> '(.*)',
    	'/' 	=> '\/',
    	'[n]' 	=> '([0-9]+)',
    	'[w]' 	=> '([a-z]+)',
    	'[x]' 	=> '(\w+)'
    );
    
    /**
     * Instance de singleton.
     * @var Route
     */
    protected static $_instance = NULL;
    
    /**
     * Constructeur.
     * @param Request $req Instance de l'objet Request.
     * @param string $root_www Adresse URL de la racine.
     * @param string $root_dir Chemin de la racine du site.
     */
    protected function __construct(Request $req, $root_www, $root_dir)
    {   
        $this->_request = $req;
        $this->_root_www = $root_www;
        $this->_root_dir = $root_dir;
    }
    
    /**
     * Initialise le routage.
     * @param string $table Chemin de fichier JSON contenant la table de routage.
     */
    public function init($table)
    {
        if (file_exists($table) == FALSE)
        {
            $this->_error("Invalid file for route table");
        }
        $file = json_decode(file_get_contents($table));
        if ($file == NULL)
        {
        	$this->_error("Invalid syntaxe for route table");
        }
        $request_url = $this->_request->get_path($this->_root_dir);
        $request_method = $this->_request->get_method();
        $current = NULL;
        $current_match = NULL;
        foreach ($file as $route)
        {
            $route->method = (isset($route->method)) ? (strtoupper($route->method)) : ('*');
        	if ($route->method == '*' || $request_method == $route->method)
        	{
        	    $route->url_pattern = (isset($route->url_pattern)) ? ($route->url_pattern) : (FALSE);
        	    if ($route->url_pattern === FALSE)
        	    {
        	        $route->url_pattern = str_replace(array_keys($this->_shortcut), array_values($this->_shortcut), $route->url);
        	    }
        	    if (preg_match('#^'.$route->url_pattern.'$#Ui', $request_url, $match))
        	    {
        	        if (isset($route->vars))
        	        {
        	        	$find = TRUE;
        	            foreach ($route->vars as $v)
        	        	{
        	        	    if (array_key_exists($v, $_GET) == FALSE)
        	        	    {
        	        	        $find = FALSE;
        	        	    }
        	        	}
        	        	if ($find)
        	        	{
        	        	    $current = $route;
        	        	    $current_match = $match;
        	        	}
        	        }
        	        else
        	        {
                        $current = $route; 
                        $current_match = $match;
        	        }
        	    }
        	}
        }
        if ($current === NULL)
        {
            $this->_error('Route not found in table');
        }
        $this->_current = $current;
        $this->_generate_vars($current_match);
        $this->_generate_params();
        $this->_call_file();
    }
    
    /**
     * Génère une erreur.
     * @param string $msg Message de l'erreur.
     * @param int $level Nombre de fonction à remonter pour l'erreur.
     * @throws Error
     */
    private function _error($msg, $level=1)
    {
        $d = debug_backtrace();
        $d = $d[$level];
        throw new Error($msg, $d['file'], $d['line']);
    }
    
    /**
     * Retourne le nom du controller.
     * @return string 
     */
    public function get_controller()
    {
        if (empty($this->_controller))
        {
            if (substr($this->_current->controller, 0, 1) != '?')
            {
                $this->_controller = $this->_current->controller;
            }
            else
            {
                $var = substr($this->_current->controller, 1);
                $this->_controller = (empty($this->_request->$var) == FALSE) ? ($this->_request->$var) : (NULL);
            }
        }
        return $this->_controller;
    }
    
    /**
     * Retourne le nom du module.
     * @return string 
     */
    public function get_module()
    {
        if (empty($this->_module))
        {
            if (substr($this->_current->module, 0, 1) != '?')
            {
                $this->_module = $this->_current->module;
            }
            else
            {
                $var = substr($this->_current->module, 1);
                $this->_module = (empty($this->_request->$var) == FALSE) ? ($this->_request->$var) : (NULL);
            }
        }
        return $this->_module;
    }
    
    /**
     * Retourne le nom de l'action.
     * @return string
     */
    public function get_action()
    {
    	if (empty($this->_action))
    	{
    		if (substr($this->_current->action, 0, 1) != '?')
    		{
    			$this->_action = $this->_current->action;
    		}
    		else
    		{
    			$var = substr($this->_current->action, 1);
    			$this->_action = (empty($this->_request->$var) == FALSE) ? ($this->_request->$var) : (NULL);
    		}
    	}
    	return $this->_action;
    }
    
    /**
     * Retourne l'identification de la page.
     * @return string
     */
    public function get_id()
    {
        return $this->get_controller().'-'.$this->get_module().'-'.$this->get_action();
    }
    
    /**
     * Définie le controller.
     * @param string $name Nom du controller.
     */
    public function set_controller($name)
    {
        if (is_string($name))
        {
            $this->_controller = $name;
        }
    }
    
    /**
     * Définie le module.
     * @param string $name Nom du module.
     */
    public function set_module($name)
    {
        if (is_string($name))
        {
        	$this->_module = $name;
        }
    }
    
    /**
     * Définie l'action.
     * @param string $name Nom du action.
     */
    public function set_action($name)
    {
        if (is_string($name))
        {
        	$this->_action = $name;
        }
    }
    
    /**
     * Envoie un header de redirection au navigateur et quitte le script.
     * @param string $module Nom du module cible ou Adresse URL.
     * @param string $action Nom de l'action.
     */
    public function redirect($module = 'Index', $action = 'index')
    {
    	if (strpos($module, '.') === FALSE && strpos($module, '/') === FALSE)
    	{
    		header("Location: ?module=$module&action=$action");
    	}
    	else
    	{
    		header("Location: $module");
    	}
    	exit();
    }
    
    /**
     * Fait une redirection vers la page précédente ou l'index si elle n'est pas trouvé
     */
    public function prev()
    {
        $default = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_NAME'];
    	$url = (isset($_SERVER['HTTP_REFERER'])) ? ($_SERVER['HTTP_REFERER']) : ($default);
    	header('Location: '.$url);
    	exit();
    }
    
    /**
     * Génère des variables par routage si le format de l'URL et sa définition le permettent.
     * @param array $match Résultat du preg_match lors de l'initialisation.
     */
    private function _generate_vars($match)
    {
        array_shift($match);
        $count = count($match);
        if ($count > 0)
        {
            if (isset($this->_current->names) && is_array($this->_current->names))
            {
                for($i = 0; $i < $count; $i++)
                {
                    $name = (isset($this->_current->names[$i])) ? ($this->_current->names[$i]) : ($this->_default_name_var.$i);
                    $this->_vars[$name] = $match[$i];
                    $this->_request->$name = $match[$i];
                }
            }
            else
            {
                for($i = 0; $i < $count; $i++)
                {
                	$name = $this->_default_name_var.$i;
                    $this->_vars[$name] = $match[$i];
                    $this->_request->$name = $match[$i];
                }              
            }    
        }
    }
    
    /**
     * Génère des paramètres supplémentaires dans l'URL grâce à la clause params du routage.
     */
    private function _generate_params()
    {
    	if (isset($this->_current->params))
    	{
    		foreach ($this->_current->params as $n => $v)
    		{
    			$this->_request->$n = $v;
    		}
    	}
    }
    
    /**
     * Appelle directement un fichier si le routage le demande.
     * Le routage doit impérativement définir l'option "dir". 
     * Pour le choix du fichier une variable "names" : {"file"} pour identifier le fichier ou l'option "file" avec le nom du fichier.
     */
    private function _call_file()
    {
        if (isset($this->_current->dir))
        {
            $dir = $this->_current->dir;
            $dir = (substr($dir, 0, 1) == '/') ? ($this->_root_dir.substr($dir, 1)) : ($dir);
            $dir = (substr($dir, -1) != '/') ? ($dir.'/') : ($dir);
            $file = (isset($this->_current->file)) ? ($this->_current->file) : ($this->_request->file);
            if (file_exists($dir.$file))
            {
                $f = new File($dir.$file);
                $type = $f->get_type_mime();
                header('Content-Type: '.$f->get_type_mime());
                if (substr($type, 0, 4) == 'text')
                {
                    include($dir.$file);
                }
                else
                {
                    readfile($dir.$file);
                }
            }
            else
            {
                header("HTTP/1.0 404 Not Found");
            }
            exit();
        }
    }
    
    /**
     * Retourne une instance de la classe avec les arguments correctement ordonnés selon le constructeur de la classe.
     * @param array $args Tableau d'arguments du constructeur.
     * @return Route
     */
    protected static function __create($args)
    {
    	return new self($args[0], $args[1], $args[2]);
    }
}
?>