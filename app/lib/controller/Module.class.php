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
	 * Event qui a déclenché le module.
	 */
	private $_event = NULL;
	
	/**
	 * Constructeur.
	 * @param object[] $config Tableau qui défini la valeur de chaque attribut de la classe.
	 */
	public function __construct(ServiceContainer $services)
	{
		$this->_services = $services;

		// Chargement des paramètres du module.
		$dirname = dirname((new ReflectionClass($this))->getFileName());
		if (file_exists($dirname.'/config.json'))
		{
			$this->_params = new Config($dirname.'/config.json');
			if (is_array($this->params()->load_on_event))
			{
				$this->_events = $this->params()->load_on_event;
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
		$dirname = dirname((new ReflectionClass($this))->getFileName());
	    $this->loader->add_dir($dirname.'/model/');	

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
	 */
	public function notify(Event $event)
	{
		if (substr($event->name(), 0, strlen($this->_name) + 2) === $this->_name.'::')
		{
			$method = $this->_services->config->system->prefix_action_function.substr($event->name(), strlen($this->_name) + 2);
			$this->init($event)->method();
		}
		elseif (in_array($event->name(), $this->_events))
		{
			$this->init($event)->run();
		}
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