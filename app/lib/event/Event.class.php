<?php
/**
 * Event est la classe fondamentale à la gestion des événements.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses Context
 */
class Event
{
    /**
     * Nom de l'événement.
     * @var string
     */
    protected $_name;

    /**
     * Message de l'événement.
     * @var string
     */
    protected $_msg;

    /**
     * Type de l'événement.
     * @var string
     */
    protected $_type;

    /**
     * Type de l'événement.
     * @var string
     */
    protected $_context = NULL;

    /** 
     * Contructeur d'un événement.
     * @param string $name Nom de l'événement.
     * @param string $msg Message descriptif de l'événement.
     * @param string $type Type de l'événement.
     */ 
    public function __construct(string $name, string $msg = NULL, string $type = NULL)
    {
        $this->_name = $name;
        $this->_msg = $msg;
        $this->_type = $type;
        $this->_context = new Context([], 1);
    }

    /**
     * Retourne le nom de l'événement.
     * @return string
     */
    public function name()
    {
        return $this->_name;
    }

    /**
     * Retourne le message de l'événement.
     * @return string
     */
    public function msg()
    {
        return $this->_msg;
    }

    /**
     * Retourne le type de l'événement.
     * @return string
     */
    public function type()
    {
        return $this->_type;
    }

    /**
     * Retourne le context de l'événement.
     * @return Context
     */
    public function context()
    {
        return $this->_context;
    }
}

?>