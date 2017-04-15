<?php
/**
 * Module est la classe mère de l'ensemble des actions de traitement de données de chaque page.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses ServiceContainer
 * @uses Config
 * @uses Event
 */
abstract class Module implements Observer
{
	/**
	 * Instance du containeur de services.
	 * @var ServiceContainer
	 */
	private $_services;

	/**
	 * Nom du module.
	 * @var string
	 */
	private $_name = NULL;

	/**
	 * Configuration propre au module.
	 * @var stdClass
	 */
	private $_params = NULL;

	/**
	 * Liste d'Event à écouter.
	 * @var string[]
	 */
	private $_events = [];

	/**
	 * Dossier du module.
	 * @var string
	 */
	private $_dir = NULL;

	/**
	 * Event qui a déclenché le module.
	 */
	private $_event = NULL;
	
	/**
	 * Constructeur.
	 * @param object[] $config Tableau qui défini la valeur de chaque attribut de la classe.
	 */
	public function __construct(ServiceContainer $services)
	{
		// Récupération des services.
		$this->_services = $services;

		// Nom du module.
		$this->_name = strtolower(get_called_class());
		$prefix = $this->_services->get('config')->system->prefix_module_class;
		$suffix = $this->_services->get('config')->system->suffix_module_class;
		$this->_name = substr($this->_name, strlen($prefix), - strlen($suffix));

		// Récupération du dossier du module.
		$this->_dir = dirname((new ReflectionClass($this))->getFileName());
		
		// Chargement des paramètres du module.
		if (file_exists($this->_dir.'/config.json'))
		{
			$this->_params = new Config($this->_dir.'/config.json');
			if (isset($this->params()->load_on_event))
			{
				$this->_events = $this->params()->load_on_event->values();
			}
		}
	}

	/**
	 * Initialise les paramètres du module.
	 * @return Module
	 */
	protected function init(Event $event)
	{
		// Récupération du dossier model pour l'import des classes privées.
	    $this->loader->add_dir($this->_dir.'/model/');	
		$this->loader->add_dir($this->_dir.'/lib/');

		// Récupération de l'événement déclencheur.
		$this->_event = $event;

		return $this;
	}

	/**
	 * Retourne les paramètres du module.
	 * @return Config
	 */
	public function params()
	{
		return $this->_params;
	}

	/**
	 * Retourne les rapamètres du module.
	 * @return ServiceContainer
	 */
	public function services()
	{
		return $this->_services;
	}

	/**
	 * Retourne l'événement déclencheur du module.
	 * @return ServiceContainer
	 */
	public function event()
	{
		return $this->_event;
	}

	/**
	 * Emplenche l'exécution du module à l'écoute d'un Event.
	 * @param string $name Nom de l'Event.
	 * @return Module
	 */
	public function listen(string $name)
	{
		$this->_events[] = $name;
		return $this;
	}

	/**
	 * Emplenche l'exécution du module à l'écoute d'un Event.
	 * @param Event $event
	 * @return bool
	 */
	public function notify(Event $event) : bool
	{
		// Si le nom de l'Event est le même que celui du module, on l'analyse.
		if (($this->params() === NULL || $this->params()->http_allow !== FALSE) && substr(strtolower($event->name()), 0, strlen($this->_name) + 2) === $this->_name.'::')
		{
			// Evenement de type génération du tpl du module pour une action donnée.
			if (substr($event->name(), -5) === '::tpl')
			{
				$method = strtolower(substr($event->name(), strlen($this->_name) + 2, -5));
				$filename = $this->_dir.'/view/'.$method.'.tpl';
				if (file_exists($filename))
				{
					$this->tpl->assign($this->_services->get('config')->tpl->module, $this->tpl->fetch($filename));
					return TRUE;
				}
			}
			else // Evenement de type exécution de la logique du module.
			{
				$method = strtolower($this->_services->get('config')->system->prefix_action_function.substr($event->name(), strlen($this->_name) + 2));
				if (method_exists($this, $method))
				{
					$this->init($event)->$method();
					return TRUE;
				}
			}
		}
		elseif (in_array($event->name(), $this->_events))
		{
			$this->init($event)->run();
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * Récupère un service.
	 * @param string $name Nom du service
	 */
	public function __get(string $name)
	{
	    return $this->_services->get($name);
	}
}
?>