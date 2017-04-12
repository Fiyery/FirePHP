<?php
/**
 * ServiceFactory créer des service.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 */
class ServiceFactory
{
    private $_name;

    private $_class;

    private $_call;

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

    private function _resolve_arg(ServiceContainer $container, string $arg) 
    {
        if (strpos($arg, '$') !== FALSE)
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

    public function get() 
    {
        return $this->_call;
    }

    public function name() : string
    {
        return $this->_name;
    }
}
?>