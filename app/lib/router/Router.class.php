<?php
/**
 * Router permet la liaison entre l'url et le traitement spécfique du serveur.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses Request
 * @uses FireException
 * @uses File
 */
class Router 
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
    private $_root_url = NULL;
    
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
     * Constructeur.
     * @param Request $req Instance de l'objet Request.
     */
    public function __construct(Request $req)
    {   
        $this->_request = $req;
    }
    
    /**
     * Initialise le routage.
     * @param string $table Chemin de fichier JSON contenant la table de routage.
     * @param string $root_url Adresse URL de la racine.
     * @param string $root_dir Chemin de la racine du site.
     */
    public function init(string $table, string $root_url, string $root_dir)
    {
        $this->_root_url = $root_url;
        $this->_root_dir = $root_dir;
        if (file_exists($table) == FALSE)
        {
            $this->_error("Invalid file for route table");
        }
        $file = json_decode(file_get_contents($table));
        if ($file == NULL)
        {
        	$this->_error("Invalid syntaxe for route table");
        }
        $request_url = $this->_request->path($this->_root_dir);
        $request_method = $this->_request->method();
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
     * @throws FireException
     */
    private function _error(string $msg, int $level = 1)
    {
        $d = debug_backtrace();
        $d = $d[$level];
        throw new FireException($msg, $d['file'], $d['line']);
    }

    /**
     * Retourne le schéma utilisé.
     * @return string
     */
    public function scheme() 
    {
        if (isset($_SERVER["HTTP_X_FORWARDED_PROTO"])) 
        {
            return $_SERVER["HTTP_X_FORWARDED_PROTO"];
        }
        elseif (isset($_SERVER["REQUEST_SCHEME"]))
        {
            return $_SERVER["REQUEST_SCHEME"];
        }
        elseif (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on')
        {
            return "https";
        }
        return "http";
    }

    /**
     * Retourne l'url courante.
     * @return string
     */
    public function url() : string
    {
        return $this->scheme()."://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
    }

    
    /**
     * Définie et retourne le nom du controller.
     * @param string $value Nom du controller.
     * @return string 
     */
    public function controller(string $value = NULL) : ?string
    {
        if (is_string($value)) 
        {
            $this->_controller = strtolower($value);
        }
        elseif (empty($this->_controller))
        {
            if (substr($this->_current->controller, 0, 1) != '?')
            {
                $this->_controller = strtolower($this->_current->controller);
            }
            else
            {
                $var = substr($this->_current->controller, 1);
                $this->_controller = (empty($this->_request->$var) == FALSE) ? (strtolower($this->_request->$var)) : (NULL);
            }
        }
        return $this->_controller;
    }
    
    /**
     * Définie et retourne le nom du module.
     * @param string $value Nom du module.
     * @return string 
     */
    public function module(string $value = NULL) : ?string
    {
        if (is_string($value)) 
        {
            $this->_module = strtolower($value);
        }
        elseif (empty($this->_module))
        {
            if (substr($this->_current->module, 0, 1) != '?')
            {
                $this->_module = strtolower($this->_current->module);
            }
            else
            {
                $var = substr($this->_current->module, 1);
                $this->_module = (empty($this->_request->$var) == FALSE) ? (strtolower($this->_request->$var)) : (NULL);
            }
        }
        return $this->_module;
    }
    
    /**
     * Définie et retourne le nom de l'action.
     * @param string $value Nom de l'action.
     * @return string
     */
    public function action(string $value = NULL) : ?string
    {
    	if (is_string($value)) 
        {
            $this->_action = strtolower($value);
        }
        elseif (empty($this->_action))
    	{
    		if (substr($this->_current->action, 0, 1) != '?')
    		{
    			$this->_action = strtolower($this->_current->action);
    		}
    		else
    		{
    			$var = substr($this->_current->action, 1);
    			$this->_action = (empty($this->_request->$var) == FALSE) ? (strtolower($this->_request->$var)) : (NULL);
    		}
    	}
    	return $this->_action;
    }
    
    /**
     * Retourne l'identification de la page.
     * @return string
     */
    public function id() : string
    {
        return $this->controller().'-'.$this->module().'-'.$this->action();
    }
    
    /**
     * Envoie un header de redirection au navigateur et quitte le script.
     * @param string $module Nom du module cible ou Adresse URL.
     * @param string $action Nom de l'action.
     */
    public function redirect(string $module = 'index', string $action = 'index')
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
    private function _generate_vars(array $match)
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
                $type = $f->type_mime();
                header('Content-Type: '.$f->type_mime());
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
}
?>