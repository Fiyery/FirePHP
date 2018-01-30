<?php
/**
 * ServiceFactory créer des service.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 */
class ServiceFactory
{
    /**
     * Nom du service.
     * @var string
     */
    private $_name;

    /**
     * Nom de la classe du service.
     * @var string
     */
    private $_class;

    /**
     * Méthode d'appel pour la création du service.
     * @var string
     */
    private $_call;

    /**
     * Constructeur.
     * @param ServiceContainer Injecteur de dépendance.
     * @param string Nom du service.
     * @param stdClass Paramètre du service.
     */
    public function __construct(ServiceContainer $container, string $name, stdClass $params)
    {
        $this->_name = $name;
        $this->_class = $params->class;
        $reflect = new ReflectionClass($this->_class);
        if ($reflect->getConstructor() === NULL) 
        {
            $this->_instance = new $this->_class();
        }
        else
        {
            $args = [];
            if (isset($params->args))
            {
                foreach ($params->args as $arg)
                {
                    $args[] = $this->_resolve_arg($container, $arg);
                }
            }
            $this->_call = function () use ($reflect, $args) {
                return $reflect->newInstanceArgs($args); 
            };
            
        }
    }

    /**
     * Résous et retourne la valeur de l'argument.
     * @param ServiceContainer Injecteur de dépendance.
     * @param string $arg Chaîne de l'argument.
     * @return mixed Valeur de l'argument.
     */
    private function _resolve_arg(ServiceContainer $container, string $arg) 
    {
        $arg_updated = $this->_resolve_arg_value($container, $arg);
        if ($arg_updated === $arg)
        {
            $arg_updated = $this->_resolve_arg_template($container, $arg);
        }
        return $arg_updated;
    }

    /**
     * Résous et retourne la valeur de l'argument si c'est une variable uniquement.
     * @param ServiceContainer Injecteur de dépendance.
     * @param string $arg Chaîne de l'argument.
     * @return mixed Valeur de l'argument.
     */
    private function _resolve_arg_value(ServiceContainer $container, string $arg)
    {
        if (strpos($arg, '$') === 0)
        {
            $parts = explode('->', $arg);
            foreach ($parts as $part)
            {
                if (substr($part, 0, 1) === '$')
                {
                    $arg = $container->get(substr($part, 1));
                }
                else
                {
                    $func = substr($part, 0, -2);
                    $arg = (substr($part, -2) === '()') ? ($arg->$func()) : ($arg->$part);
                }
            }
        }
        return $arg;
    }

    /**
     * Résous et retourne la valeur de l'argument si c'est un chaîne de caractères avec des variables intégrées.
     * @param ServiceContainer Injecteur de dépendance.
     * @param string $arg Chaîne de l'argument.
     * @return mixed Valeur de l'argument.
     */
    private function _resolve_arg_template(ServiceContainer $container, string $arg)
    {
        preg_match_all("/{([^\}]+)}/", $arg, $match);
        if (isset($match[1]) && count($match[1]) > 0)
        {
            foreach ($match[1] as $var)
            {
                $parts = explode('->', $var);
                foreach ($parts as $part)
                {
                    if (substr($part, 0, 1) === '$')
                    {
                        $value = $container->get(substr($part, 1));
                    }
                    elseif ($arg !== NULL)
                    {
                        $func = substr($part, 0, -2);
                        $value = (substr($part, -2) === '()') ? ($value->$func()) : ($value->$part);
                    }
                }
                if (is_scalar($value))
                {
                    $arg = str_replace("{".$var."}", $value, $arg);
                }
            }
        }
        return $arg;
    }

    /**
     * Retourne la fonction de création du service.
     * @return mixed 
     */
    public function get() 
    {
        return $this->_call;
    }

    /**
     * Retourne le nom du service.
     * @return string 
     */
    public function name() : string
    {
        return $this->_name;
    }
}
?>