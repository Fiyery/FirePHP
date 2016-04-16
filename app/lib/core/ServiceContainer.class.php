<?php
/**
 * ServiceContainer permet de charger des services et de gérer les injections de dépendance.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @copyright 2011-2015 Yoann Chaumin
 * @use Singleton
 */
class ServiceContainer extends Singleton
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
	private $_callables;
	/**
	 * Liste des services intanciés.
	 * @var array
	 */
	private $_instances;
	
	/**
	 * Liste des factories de service.
	 * @var array
	 */
	private $_factories;
	
	/**
	 * Liste des alias.
	 * @var Array
	 */
	private $_alias;
	
	/**
	 * Constructeur.
	 */
	protected function __construct()
	{
		$this->_callables = [];
		$this->_instances = [];
		$this->_factories = [];
		$this->_alias = [];
	}
	
	/**
	 * Initialise un service d'instance unique.
	 * @param string $name Nom du service.
	 * @param callable $call Closure d'intialisation du service.
	 */
	public function set($name, Callable $call)
	{
		$name = strtolower($name);
		if (isset($this->_call[$name]) == FALSE)
		{
			$this->_call[$name] = $call;
		}
	}
	
	/**
	 * Initialise un service d'instance unique.
	 * @param object $name Nom du service.
	 */
	public function set_instance($instance)
	{
		if ($instance != NULL && is_object($instance))
		{
			$name = strtolower((new \ReflectionClass($instance))->getName());
			if (isset($this->_instances[$name]) == FALSE)
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
	public function set_factory($name, Callable $call)
	{
		$name = strtolower($name);
		if (isset($this->_factories[$name]) == FALSE)
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
		$name = $this->_parse_alias($name);
	    $origin_name = $name;
		$name = strtolower($name);
		if (isset($this->_factories[$name]))
		{
			return $this->_factories[$name]();
		}		
		if (isset($this->_instances[$name]) || isset($this->_call[$name]))
		{
			if (isset($this->_instances[$name]) == FALSE && isset($this->_call[$name]))
			{
				$this->_instances[$name] = $this->_call[$name]();
			}
			return $this->_instances[$name];
		}
		$reflected_class = new ReflectionClass($origin_name);
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
					        $d = debug_backtrace();
					        throw new FireException('Unable to solve service "'.$name.'" : undefined default parameter for "'.$param->getName().'"', $d[1]['file'], $d[1]['line']);
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
		$d = debug_backtrace();
		throw new FireException('Unable to solve service "'.$name.'"', $d[1]['file'], $d[1]['line']); 	   
	}

	/**
	 * Charge les alias
	 * @param string $filename Chemin du fichier.
	 */
	public function load_alias($filename)
	{
	    if (file_exists($filename))
	    {
            $alias = json_decode(file_get_contents($filename));
            if ($alias != NULL) 
            {
                $this->_alias = array_merge($this->_alias, get_object_vars($alias));
            }
	    }
	}
	
	/**
	 * Ajoute un nouvel alias pour le service.
	 * @param string $alias Nom de l'alias.
	 * @param string $target Nom du service.
	 */
	public function add_alias($alias, $target)
	{
	    $this->_alias[$alias] = $target;
	}

	/**
	 * Remplace le nom par l'alias s'il existe.
	 * @param string $name Nom du service appelé.
	 * @return string
	 */
    private function _parse_alias($name)
    {
        return (isset($this->_alias[$name])) ? ($this->_alias[$name]) : ($name);
    }
}
?>