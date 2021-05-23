<?php
namespace FirePHP\Response;

/**
 * Classe de gestion pour l'entête de la réponse.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 */
class ResponseHeader
{
    /**
     * Liste des entêtes de la réponse.
     * @var array
     */
    protected $_list = [];

    /**
     * Définie l'entête.
     * @param string $name
     * @param string $value
     */
    public function set(string $name, string $value)
    {
        $this->_list[$name] = $value;
        header($name.": ".$value);
    }

    /**
     * Supprime un entête.
     * @param string $name
     */
    public function remove(string $name)
    {
        if ($this->has($name))
        {
            header_remove($name.": ".$this->_list[$name]);
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