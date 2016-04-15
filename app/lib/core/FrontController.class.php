<?php
/**
 * FrontController est la classe de chargement des modules du site.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @copyright 2011-2015 Yoann Chaumin
 * @uses ServiceContainer
 */
class FrontController
{         
    /**
     * Variable de requête de service.
     * @var ServiceContainer
     */
    protected $_services;
    
	/**
	 * Constructeur.
	 */
	public function __construct(ServiceContainer $services)
	{
        $this->_services = $services;
	}
	
	/**
	 * Récupère un service.
	 * @param string $name Nom du service
	 */
	public function __get($name)
	{
	    return $this->_services->get($name);
	}
	
	/**
	 * Vérifie les accès.
	 * @param boolean $redirect Si TRUE, la redirection sera faite.
	 */
	public function get_access($redirect=TRUE)
	{
	    // Todo access
		return TRUE;
	}
	
	/**
	 * Configure les feuilles de styles CSS pour l'envoie au template.
	 * @return string Contenu HTML.
	 */
	public function get_css()
	{
		if ($this->config->tpl->enable === FALSE)
		{
			return FALSE;
		}
		
		$name = strtolower($this->route->get_id());
		$module = strtolower($this->route->get_module());
		$action = strtolower($this->route->get_action());
		$var = $this->cache->read('css-'.$name, Cache::WEEK);
		if ($var == NULL)
		{
			$dir = $this->config->path->root_dir.$this->config->path->css;
			$this->css->add_package('main', $dir.'main/');
			$this->css->get();
			if ($this->css->select($name) == FALSE)
			{
				$this->css->create($name);
			}
			$dir .= strtolower($this->route->get_controller()).'/modules/'.$module.'/';
			$this->css->add($dir.$module.'.css');
			$this->css->add($dir.$module.'-'.$action.'.css');
			$this->css->get();
			$html = $this->css->get_html($this->config->path->root_dir, $this->config->path->root_url);
			$html = str_replace($this->config->path->css_cache, 'css/', $html);
			$this->cache->add('html', $html);
			$this->cache->write();
		}
		else
		{
			$html = $var['html'];
		}
		$this->tpl->assign($this->config->tpl->css, $html);
		return $html;
	}
	
	/**
	 * Configure les scripts JS pour l'envoie au template.
	 * @return string Contenu HTML.
	 */
	public function get_js()
	{
		if ($this->config->tpl->enable === FALSE)
		{
			return FALSE;
		}
		
	    $name = strtolower($this->route->get_id());
	    $module = strtolower($this->route->get_module());
	    $action = strtolower($this->route->get_action());
		$var = $this->cache->read('js-'.$name, Cache::WEEK);
		if ($var == NULL)
		{
		    $dir = $this->config->path->js;
		    $this->js->select('main');
		    $this->js->add($dir.'main/jquery.js');
		    $this->js->add($dir.'main/library.js');
			$this->js->add_package('main', $dir.'main/');
			$this->js->get();
			if ($this->js->select($name) == FALSE)
			{
				$this->js->create($name);
			}
			$dir .= strtolower($this->route->get_controller()).'/modules/'.$module.'/';
			$this->js->add($dir.$module.'.js');
			$this->js->add($dir.$module.'-'.$action.'.js');
			$this->js->get();
			$html = $this->js->get_html($this->config->path->root_dir, $this->config->path->root_url);
			$html = str_replace($this->config->path->js_cache, 'js/', $html);
			$this->cache->add('html', $html);
			$this->cache->write();
		}
		else
		{
			$html = $var['html'];
		}
		$this->tpl->assign($this->config->tpl->js, $html);
		return $html;
	}

	/**
	 * Lance le module.
	 * @param boolean $redirect Si TRUE, une redirection est effectuée si le module n'est pas trouvé.
	 * @return boolean
	 */
	public function execute($redirect=FALSE)
	{
        // Exécution des commandes spécifiques avant le chargements du modules.
	    $this->before_execute();
	    
	    $return = TRUE;
	    
		$module = $this->route->get_module(); 
		$action = $this->route->get_action();
		
		// Initialisation des packages de ressources par défaut pour le module.
		if ($this->config->tpl->enable)
		{
			$this->js->create('main');
			$this->css->create('main');
			$name = strtolower($this->route->get_id());
			$this->js->create($name);
			$this->css->create($name);
		}
		
		if(class_exists($module) == FALSE)
		{
		    $dir_module = $this->config->path->module.strtolower($this->route->get_controller()).'/';
		    $filename = $dir_module.strtolower($module).'/'.$this->config->system->name_file_module;
			if(file_exists($filename))
			{
				require($filename);
				if (class_exists($module) == FALSE && $redirect)
				{
					$this->site->add_message($this->config->msg->error_404);
					$this->route->prev();
					$return = FALSE;
				}
			}
			elseif ($redirect)
			{
				$this->site->add_message($this->config->msg->error_404);
				$this->route->prev();
				$return = FALSE;
			}
		}
		if (class_exists($module))
		{
		    $m = new $module($this->_services, get_object_vars($this));
    		$called_action = $this->config->system->prefix_action_function.$action;
    		if(method_exists($module, $called_action))
    		{
    		    // Exécution du module.
    		    try 
    		    {
    		    	$m->$called_action();
    		    }
    			catch (Exception $e)
    			{
    				$this->error->handle_exception($e);
    				$this->route->set_controller('Default');
    				$this->route->set_module('Erreur');
    				$this->route->set_action('index');
    				if ($this->config->tpl->enable)
    				{
    					$this->tpl->assign('error_msg', $e->getMessage());
    				}
    			}
    		}
    		elseif ($redirect)
    		{
    			$this->site->add_message($this->config->msg->error_404);
    			$this->route->redirect();
    			$return = FALSE;
    		}
		}
		
		// Exécution des commandes spécifiques après le chargements du modules.
		$this->after_execute();
		return $return;
	}
	
	/**
	 * Charge le contenu du module dans la page.
	 * @param string $var Nom de la variable du template.
	 * @return $string Contenu du module.
	 */
	public function assign($var=NULL)
	{	
	    if ($var == NULL)
	    {
	        $var = $this->config->tpl->module;
	    }
	    $controller = strtolower($this->route->get_controller());
	    $module = strtolower($this->route->get_module());
	    $action = strtolower($this->route->get_action());
	    $root = $this->config->path->root_dir.$this->config->path->tpl;
	    
	    $tpl_file = $root.$controller.'/'.$module.'/'.$module.'-'.$action.'.tpl';
	    if ($this->config->tpl->enable === FALSE)
	    {
	    	if (file_get_contents($tpl_file) == FALSE)
	    	{
	    		$this->route->set_controller('Default');
	    		$this->route->set_module('Erreur');
	    		$this->route->set_action('404');
	    		$controller = strtolower($this->route->get_controller());
	    		$module = strtolower($this->route->get_module());
	    		$action = strtolower($this->route->get_action());
	    		return file_get_contents($root.$controller.'/'.$module.'/'.$module.'-'.$action.'.tpl');
	    	}
	    	else 
	    	{
				return file_get_contents($root.$controller.'/'.$module.'/'.$module.'-'.$action.'.tpl');
	    	}
	    }
	    else 
	    {
	    	$html = $this->tpl->fetch($root.$controller.'/'.$module.'/'.$module.'-'.$action.'.tpl');
	    	if (empty($html))
	    	{
	    		$this->route->set_controller('Default');
	    		$this->route->set_module('Erreur');
	    		$this->route->set_action('404');
	    		$controller = strtolower($this->route->get_controller());
	    		$module = strtolower($this->route->get_module());
	    		$action = strtolower($this->route->get_action());
	    		$html = $this->tpl->fetch($root.$controller.'/'.$module.'/'.$module.'-'.$action.'.tpl');
	    	}
	    	
	    	// Gestionnaire de feuilles de style.
	    	$this->get_css();
	    	
	    	// Gestionnaire de scripts.
	    	$this->get_js();
	    	
	    	$this->tpl->assign($var, $html);
	    	$this->tpl->assign($this->config->tpl->message, $this->site->list_messages());
	    }
	}
	
	/**
	 * Traitement spécifique à la réception de tous les paramètres.
	 */
	public function init()
	{
	    
	}

	/**
	 * Traitement particulier à faire avant l'exécution des modules.
	 */
	protected function before_execute()
	{ 

	}
	
	/**
	 * Traitement particulier à faire après l'exécution des modules.
	 */
	protected function after_execute()
	{

	}
}
?>