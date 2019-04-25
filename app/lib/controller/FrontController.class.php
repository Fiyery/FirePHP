<?php
/**
 * FrontController est la classe de chargement des modules du site.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses ServiceContainer
 * @uses Event
 * @event Core::init Event envoyé au moment de l'initialisation.
 * @event Core::execute_before Event envoyé avant l'exécution sur module.
 * @event Core::execute_after Event envoyé après l'exécution sur module.
 * @event Core::assign Event envoyé après la génération du template du module.
 * @event Core::run Envoyé pour lancer le module normalement appelé par la requête.
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

		// Event pour lancer les actions du Hook.
		try 
		{
			$this->hook->notify(new Event('Core::init'));
		}
		catch (Throwable $t)
		{
			$this->error->handle_throwable($t);
		}
	}
	
	/**
	 * Récupère un service.
	 * @param string $name Nom du service
	 */
	public function __get($name)
	{
		try
		{
	    	return $this->_services->get($name);
		}
		catch (Throwable $t)
		{
			$this->error->handle_throwable($t);
		}
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
		
		$name = strtolower($this->router->id());
		$module = strtolower($this->router->module());
		$action = strtolower($this->router->action());
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
			if ($this->config->feature->css_module_loader)
			{
				$dir_module = $this->config->path->root_dir.$this->config->path->module.strtolower($this->router->controller())."/".$module."/".$this->config->path->css;
				if (file_exists($dir_module)) 
				{
					$files = [
						"main",
						$action
					];
					foreach ($files as $f)
					{
						$this->css->add($dir_module.$f.'.css');
					}
				}
			}
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
		
	    $name = strtolower($this->router->id());
	    $module = strtolower($this->router->module());
	    $action = strtolower($this->router->action());
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
			if ($this->config->feature->js_module_loader)
			{
				$dir_module = $this->config->path->root_dir.$this->config->path->module.strtolower($this->router->controller())."/".$module."/".$this->config->path->js;
				if (file_exists($dir_module)) 
				{
					$files = [
						"main",
						$action
					];
					foreach ($files as $f)
					{
						$this->js->add($dir_module.$f.'.js');
					}
				}
			}
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
		// Event pour lancer les actions du Hook.
		try 
		{
			$this->hook->notify(new Event('Core::execute_before'));
		}
		catch (Throwable $t)
		{
			$this->error->handle_throwable($t);
		}
		

        // Exécution des commandes spécifiques avant le chargements du modules.
	    $this->before_execute();
		
		// Initialisation des packages de ressources par défaut pour le module.
		if ($this->config->tpl->enable)
		{
			$this->js->create('main');
			$this->css->create('main');
			$name = strtolower($this->router->id());
			$this->js->create($name);
			$this->css->create($name);
		}
		
		// Exécution du module.
		try 
		{
			// Event pour lancer le module.
			if ($this->hook->notify(new Event(($this->router->module()).'::'.($this->router->action()))) === FALSE)
			{
				// Si aucun module n'a pu être déclenché, on fait appel au module d'erreur 404.
				$this->router->controller('Default');
				$this->router->module('error');
				$this->router->action('404');
				$this->response->status_code(404);
				$this->hook->notify(new Event('error::404'));
			}
		}
		catch (Throwable $t)
		{
			// En cas d'Exception
			// On log l'erreur.
			$this->error->handle_throwable($t);

			// On fait appel au module d'erreur.
			$this->router->controller('Default');
			$this->router->module('error');
			$this->router->action('500');
			$this->response->status_code(500);
			$this->tpl->assign('error_msg', $t->getMessage());
			$this->hook->notify(new Event(($this->router->module()).'::'.($this->router->action())));
		}

		// Event pour lancer les actions du Hook.
		try 
		{
			$this->hook->notify(new Event('Core::execute_after'));
		}
		catch (Throwable $t)
		{
			$this->error->handle_throwable($t);
		}
		
		// Exécution des commandes spécifiques après le chargements du modules.
		$this->after_execute();
		return TRUE;
	}
	
	/**
	 * Charge le contenu du module dans la page.
	 * @param string $var Nom de la variable du template.
	 * @return string Contenu du module.
	 */
	public function assign($var=NULL)
	{	
		try 
		{
			if ($this->hook->notify(new Event(($this->router->module()).'::'.($this->router->action()).'::tpl')) === FALSE)
			{
				$this->router->controller('Default');
				$this->router->module('error');
				$this->router->action('404');
				$this->response->status_code(404);
				$this->hook->notify(new Event(($this->router->module()).'::'.($this->router->action()).'::tpl'));
			}
		}
		catch (Throwable $t)
		{
			$this->error->handle_throwable($t);
		}
		
		// Gestionnaire de feuilles de style.
		$this->get_css();
		
		// Gestionnaire de scripts.
		$this->get_js();
		
		// Envoie des messages.
		$this->tpl->assign($this->config->tpl->message, $this->response->alert()->lists());
		$this->response->alert()->clean();

		// Event pour lancer les actions du Hook.
		try 
		{
			$this->hook->notify(new Event('Core::assign'));
		}
		catch (Throwable $t)
		{
			$this->error->handle_throwable($t);
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