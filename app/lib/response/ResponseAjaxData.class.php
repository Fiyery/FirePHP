<?php
namespace FirePHP\Response;

/**
 * Classe de gestion des données à retourner en Ajax.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 */
class ResponseAjaxData
{
    /**
     * Liste des données.
     * @var array
     */
    protected $_list = [];

    
    /**
     * Vérifie l'existence d'un attribut.
     * @param string $name Valeur de l'attribut.
     */
    public function __isset(string $name)
    {
        return (isset($this->_list[$name]));
    }
    
    /**
     * Supprime un attribut.
     * @param string $name Valeur de l'attribut.
     */
    public function __unset(string $name)
    {
        unset($this->_list[$name]);
    }
    
    /**
     * Retourne un attribut.
     * @param string $name Valeur de l'attribut.
     */
    public function __get(string $name)
    {
        return (isset($this->$name)) ? ($this->$name) : (NULL);
    }
    
    /**
     * Définie un attribut.
     * @param string $name Nom de l'attribut.
     * @param mixed $value Valeur de l'attribut.
     */
    public function __set(string $name, $value = NULL)
    {
        return $this->_list[$name] = $value;
    }

    /**
     * Définie un attribut.
     * @param string $name Nom de l'attribut.
     * @param mixed $value Valeur de l'attribut.
     */
    public function __toString()
    {
        return json_encode($this->_list);
    }
    
    /**
     * Définie une donnée.
     * @param string|array $name
     * @param mixed $value
     */
    public function set($name, $value = NULL)
    {
        if (is_array($name)) 
        {
            $this->_list = array_merge($this->_list, $name);
        }
        elseif (is_scalar($name))
        {
            $this->_list[$name] = $value;
        }
    }

    /**
     * Ajotue une valeur sans préciser de clé.
     * @param mixed $value
     */
    public function add($value)
    {
            $this->_list[] = $value;
    }

    /**
     * Supprime une donnée.
     * @param string $name
     */
    public function remove(string $name)
    {
        if ($this->has($name))
        {
            unset($this->_list[$name]);
        }
    }

    /**
     * Retourne la valeur de l'entête.
     * @param string $name
     * @return string $value
     */
    public function get(string $name) : string
    {
        return ($this->has($name)) ? ($this->_list[$name]) : (NULL);
    }

    /**
     * Retourne toutes les valeurs de l'entête.
     * @return string
     */
    public function lists() : array
    {
        return $this->_list;
    }

    /**
     * Vérifie l'existence d'un paramètre de l'entête.
     * @param string $name
     * @return bool
     */
    public function has(string $name) : bool
    {
        return (isset($this->_list[$name]));
    }
}
?>