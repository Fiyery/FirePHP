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
        $this->_value = $this->_parse_values($value);
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

    private function _parse_values($values) : stdClass
    {
        foreach($values as $key => &$v) 
        {
            $values->$key = (is_object($v)) ? (new self($key, $v, $this)) : ($v);
        }
        return $values;
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
        if (isset($this->_value->$name) === FALSE)
        {
            throw new FireException("Undefined var \"".$name."\" for config \"".$this->_name."\"", 1);
        }
        return $this->_value->$name;
    }

    /**
     * Définie une valeur.
     * @param string $name Nom de la variable.
     * @param mixed $value Valeur de la config.
     */
    public function __set(string $name, $value)
    {
        $this->_value->$name = (is_object($value)) ? ($this->_parse_values($value)) : ($value);
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
		return (isset($this->_value->$name));
	}

    /**
	 * Supprime un paramètre.
	 * @param string $name Nom du paramètre
	 */
	public function __unset(string $name)
	{
		if (isset($this->$name))
		{
			unset($this->_value->$name);
		}
	}

    /**
     * Retourne la liste des clés.
     * @return array
     */
    public function keys() : array
    {
        return array_keys(get_object_vars($this->_value));
    }

    /**
     * Retourne la liste des valeurs.
     * @return stdClass
     */
    public function values() : stdClass
    {
        return $this->_value;
    }

    /**
     * Retourne l'élément courant.
     * @return mixed
     */
    public function current()
    {
        return current($this->_value);
    }

    /**
     * Retournes la clé courante.
     * @return string
     */
    public function key()
    {
        return key($this->_value);
    }

    /**
     * Passe à l'élément suivant.
     */
    public function next()
    {
        next($this->_value);
    }

    /**
     * Remet l'iterateur au début.
     */
    public function rewind()
    {
        reset($this->_value);
    }

    /**
     * Vérifie si la position actuelle est valide.
     * @return bool
     */
    public function valid() : bool
    {
        return (current($this->_value) !== FALSE);
    }

    /**
     * Itère sur les valeurs en permettant lors passage par référence pour les modifier dans une boucle.
     * @return mixed
     */
    public function &iterate()
    {
        foreach ($this->_value as &$v) 
        {
            yield $v;
        }
    }
}
    