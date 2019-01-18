<?php
/**
 * ServiceContainer permet de charger des services et de gérer les injections de dépendance.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses Singleton
 * @uses ServiceFactory
 * @uses Event
 */
class ServiceContainer extends Singleton implements Observable
{
    /**
     * Instance de singleton
     * @var ServiceContainer
     */
    protected static $_instance = NULL;
    
	/**
	 * Liste des closures de service.
	 * @var array
	 */
	private $_call = [];
	/**
	 * Liste des services intanciés.
	 * @var array
	 */
	private $_instances = [];
	
	/**
	 * Liste des factories de service.
	 * @var array
	 */
	private $_factories = [];

	/**
	 * Liste des observeurs.
	 * @var array
	 */
	private $_observers = [];

	/**
	 * Liste des alias.
	 * @var array
	 */
	private $_alias = [];
	
	/**
	 * Constructeur.
	 */
	protected function __construct()
	{

	}

	/**
	 * Charge les services.
	 * @param string $filename Fichier JSON qui contient les services.
	 */
	public function init(string $filename)
	{
		$this->_filename = $filename;
		$services = json_decode(file_get_contents($this->_filename));
        foreach ($services as $name => $params)
        {
			$factory = new ServiceFactory($this, $name, $params);
			$this->_call[$name] = $factory->get();
			$alias = (isset($params->alias)) ? (array_merge([$name], $params->alias)) : ([$name]);
			foreach ($alias as $a) 
			{
				$this->_alias[$a] = $name;
			}
		}
	}

	/**
	 * Retourne la liste des services.
	 * @return string[] 
	 */
	public function list_all() : array	
	{
		return array_merge(array_keys($this->_instances), array_keys($this->_call), array_keys($this->_factories));
	}

	/**
	 * Retourne la liste des services intanciés.
	 * @return string[] 
	 */
	public function list_instances() : array	
	{
		return array_keys($this->_instances);
	}

	/**
	 * Retourne la liste des services à instanciés.
	 * @return string[] 
	 */
	public function list_call() : array	
	{
		return array_keys($this->_call);
	}

	/**
	 * Retourne la liste des services liés à une factory.
	 * @return string[] 
	 */
	public function list_factories() : array	
	{
		return array_merge(array_keys($this->_instances), array_keys($this->_call), array_keys($this->_factories));
	}

	/**
	 * Retourne la liste des services.
	 * @param string $name Nom du service.
	 * @return bool
	 */
	public function has($name) : bool
	{
		return in_array($name, $this->list_all());
	}
	
	/**
	 * Initialise un service d'instance unique.
	 * @param string $name Nom du service.
	 * @param callable $call Closure d'intialisation du service.
	 */
	public function set(string $name, Callable $call)
	{
		$name = strtolower($name);
		if (isset($this->_call[$name]) === FALSE)
		{
			$this->_call[$name] = $call;
		}
	}
	
	/**
	 * Initialise un service d'instance unique.
	 * @param object $name Nom du service.
	 */
	public function set_instance(string $name = NULL, $instance)
	{
		if ($instance != NULL && is_object($instance))
		{
			$name = ($name !== NULL) ? ($name) : (strtolower((new \ReflectionClass($instance))->getName()));
			if (isset($this->_instances[$name]) === FALSE)
			{
				$this->_instances[$name] = $instance;
			}
		}
	}
	
	/**
	 * Initialise un service d'instance multiple.
	 * @param string $name Nom du service.
	 * @param callable $call Closure d'intialisation du service.
	 */
	public function set_factory(string $name, Callable $call)
	{
		$name = strtolower($name);
		if (isset($this->_factories[$name]) === FALSE)
		{
			$this->_factories[$name] = $call;
		}
	}
	
	/**
	 * Récupère un service instancié.
	 * @param  $name
	 * @return Object Instance du service.
	 */
	public function get($name)
	{
		$name = (isset($this->_alias[$name])) ? ($this->_alias[$name]) : ($name);
		if (isset($this->_factories[$name]))
		{
			return $this->_factories[$name]();
		}		
		if (isset($this->_instances[$name]) || isset($this->_call[$name]))
		{
			if (isset($this->_instances[$name]) === FALSE && isset($this->_call[$name]))
			{
				$this->_instances[$name] = $this->_call[$name]();
				$this->notify(new Event('Service::config_'.$name));
			}
			return $this->_instances[$name];
		}
		try 
		{
			$reflected_class = new ReflectionClass($name);
		}
		catch (Exception $e)
		{
			throw new FireException('Unable to solve service "'.$name.'"', 1);
		}
		if ($reflected_class->isInstantiable())
		{
			$contructor = $reflected_class->getConstructor();
			if ($contructor != NULL)
			{
				$parameters = $contructor->getParameters();
				$contructor_parameters = [];
				foreach ($parameters as $param)
				{
					if ($param->getClass() != NULL)
					{
						$contructor_parameters[] = $this->get($param->getClass()->getName());
					}
					else
					{
						try 
						{
						    $contructor_parameters[] = $param->getDefaultValue();
						}
					    catch (Exception $e)
					    {
					        throw new FireException('Unable to solve service "'.$name.'" : undefined default parameter for "'.$param->getName().'"', 1);
					    }
					}
				}
				$this->_instances[$name] = $reflected_class->newInstanceArgs($contructor_parameters);
			}
			else
			{
				$this->_instances[$name] = $reflected_class->newInstance();
			}
			return $this->_instances[$name];
		}
		throw new FireException('Unable to solve service "'.$name.'"', 1); 	   
	}

	/**
     * Ajoute un observateur à l'objet.
     * @param Observer $observer
     */
    public function attach(Observer $observer)
	{
		$this->_observers[] = $observer;
	}

	/**
     * Supprime un observateur de l'objet.
     * @param Observer $observer
     */
    public function detach(Observer $observer)
	{
		unset($this->_observers[array_search($observer, $this->_observers)]);
	}

	/**
     * Génère un événement.
     * @param Event $event
     */
    public function notify(Event $event)
	{
		foreach ($this->get_observers() as $observer)
		{
			$observer->notify($event);
		}
	}
    
    /**
     * Retourne tous les observateurs de l'objet.
     * @return Observer[]
     */
    public function get_observers() : array
	{
		return $this->_observers;
	}
}
?>