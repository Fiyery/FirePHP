<?php
/**
 * ConfigValue est une valeur de la config qui génère une Exception en cas d'erreur.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 */
class ConfigValue implements Iterator
{   
    /**
     * Valeur de la config.
     * @var mixed
     */
    private $_value = NULL;

    /**
     * Nom de la variable.
     * @var string
     */
    private $_name = NULL;

    /**
     * Parent de la Config.
     * @var ConfigValue
     */
    private $_parent = NULL;

    /**
     * Constructeur
     * @param string $name Nom de la variable.
     * @param mixed $value Valeur de la config.
     */
    public function __construct(string $name, $value, ConfigValue $parent = NULL)
    {
        $this->_value = (is_object($value)) ? ($this->_object_to_array($value)) : ($value);
        $this->_name = $name;
        $this->_parent = $parent;
    }

    /**
     * Convertie un object récursivement en tableau.
     * @param object $object 
     * @return array
     */
    private function _object_to_array($object) : array
    {
        $list = (is_object($object)) ? (get_object_vars($object)) : ($object);
        $array = [];
        foreach ($list as $key => $val) 
        {
            $val = (is_array($val) || is_object($val)) ? ($this->_object_to_array($val)) : ($val);
            $array[$key] = $val;
        }
        return $array;
    }

    /**
     * Retourne la valeur recherchée.
     * @param string $name Nom de la variable.
     * @return ConfigValue|string
     */
    public function __get(string $name)
    {
        if ($this->_value === NULL)
        {
            return NULL;
        }
        if (isset($this->_value[$name]) === FALSE)
        {
            throw new FireException("Undefined var \"".$name."\" for config \"".$this->_name."\"", 1);
        }
        if (is_array($this->_value[$name])) 
        {
            return new self($name, $this->_value[$name], $this);
        }
        return $this->_value[$name];
    }

    /**
     * Définie une valeur.
     * @param string $name Nom de la variable.
     * @param mixed $value Valeur de la config.
     */
    public function __set(string $name, $value)
    {
        $this->_value[$name] = (is_object($value)) ? ($this->_object_to_array($value)) : ($value);
        if ($this->_parent !== NULL)
        {
            $name = $this->_name;
            $this->_parent->$name = $this->_value;
        }
    }

    /**
	 * Vérifie l'existance d'un paramètre.
	 * @param string $name Nom du paramètre
	 * @return boolean
	 */
	public function __isset(string $name) : bool
	{
		return (isset($this->_value, $name));
	}

    /**
	 * Supprime un paramètre.
	 * @param string $name Nom du paramètre
	 */
	public function __unset(string $name)
	{
		if (isset($this->$name))
		{
			unset($this->_values[$name]);
		}
	}

    /**
     * Retourne la liste des clés.
     * @return array
     */
    public function keys() : array
    {
        return (is_array($this->_value)) ? (array_keys($this->_value)) : ([]);
    }

    /**
     * Retourne la liste des valeurs.
     * @return array
     */
    public function values() : array
    {
        return (is_array($this->_value)) ? ($this->_value) : ([]);
    }

    /**
     * Retourne l'élément courant.
     * @return mixed
     */
    public function current()
    {
        if (is_array(current($this->_value)))
        {
            return new self($this->_name.'->'.key($this->_value), current($this->_value)); 
        }  
        return current($this->_value);
    }

    /**
     * Retournes la clé courante.
     * @return string
     */
    public function key()
    {
        if (is_array($this->_value) === FALSE)
        {
            return FALSE; 
        }  
        return key($this->_value);
    }

    /**
     * Passe à l'élément suivant.
     */
    public function next()
    {
        if (is_array($this->_value))
        {
            next($this->_value);
        }
    }

    /**
     * Remet l'iterateur au début.
     */
    public function rewind()
    {
        if (is_array($this->_value) === FALSE)
        {
            return FALSE; 
        }  
        reset($this->_value);
    }

    /**
     * Vérifie si la position actuelle est valide.
     * @return bool
     */
    public function valid() : bool
    {
        if (is_array($this->_value) === FALSE)
        {
            return FALSE; 
        }   
        return (current($this->_value) !== FALSE);
    }
}
    