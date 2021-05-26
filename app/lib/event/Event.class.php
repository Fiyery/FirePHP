<?php
namespace FirePHP\Event;
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
     * Classe qui a appeler l'événément.
     * @var mixed
     */
    protected $_caller;

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
     * @var Context
     */
    protected $_context = NULL;

    /** 
     * Contructeur d'un événement.
     * @param string $name Nom de l'événement.
     * @param mixed $caller Instance appelant l'événement.
     * @param string $msg Message descriptif de l'événement.
     * @param string $type Type de l'événement.
     */ 
    public function __construct(string $name, $caller=NULL, string $msg = NULL, string $type = NULL)
    {
        $this->_name = $name;
        $this->_caller = $caller;
        $this->_msg = $msg;
        $this->_type = $type;
        $this->_context = new Context([], 1);
    }

    /**
     * Retourne le nom de l'événement.
     * @return string
     */
    public function name() : string
    {
        return $this->_name;
    }

    /**
     * Retourne l'instance ayant appeler l'événement.
     * @return mixed
     */
    public function caller()
    {
        return $this->_caller;
    }

    /**
     * Retourne le message de l'événement.
     * @return string
     */
    public function msg() : string
    {
        return $this->_msg;
    }

    /**
     * Retourne le type de l'événement.
     * @return string
     */
    public function type() : string
    {
        return $this->_type;
    }

    /**
     * Retourne le context de l'événement.
     * @return Context
     */
    public function context() : Context
    {
        return $this->_context;
    }
}

?>