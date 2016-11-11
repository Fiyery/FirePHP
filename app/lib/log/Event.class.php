<?php
class Event
{
    /**
     * TRUE si l'évènement est déclenché.
     * @var bool
     */
    private $_pending;

    /**
     * Nom de l'événément.
     * @var string
     */
    protected $_name;

    /**
     * Message de l'événément.
     * @var string
     */
    protected $_msg;

    /**
     * Type de l'événément.
     * @var string
     */
    protected $_type;

    /**
     * Timestamp de l'événément.
     * @var number
     */
    protected $_time;

    /**
     * Timestamp de la requête.
     * @var number
     */
    protected $_request_time;

    /**
     * Fichier dans lequel l'événement est déclenché.
     * @var string
     */
    protected $_file;

    /**
     * Ligne du fichier dans lequel l'événément est déclenché.
     * @var string
     */
    protected $_line;

    /**
     * HTTP User Agent du client qui a déclenché l'événement.
     * @var string
     */
    protected $_function;
    
    /**
     * Type de la fonction ou méthode.
     * @var string
     */
    protected $_function_type;

    /**
     * Classe dans lequel l'événément est déclenché si elle existe.
     * @var string
     */
    protected $_class;

    /**
     * IP du client qui a déclenché l'événement.
     * @var string
     */
    protected $_client;

    /**
     * HTTP User Agent du client qui a déclenché l'événement.
     * @var string
     */
    protected $_client_agent;

    /**
     * Informations personnalisées envoyées au contructeur.
     * @var array
     */
    protected $_args;

    /** 
     * Contructeur d'un événément.
     * @param string $name Nom de l'événément.
     * @param string $msg Message descriptif de l'événement.
     * @param string $type Type de l'événément.
     * @param array $agrs Paramètres personnalisés.
     */ 
    public function __construct($name, $msg, $type=NULL, $args=[])
    {
        $this->_name = $name;
        $this->_msg = $msg;
        $this->_type = $type;
        $this->_pending = TRUE;
        $this->_args = $args;
    }

    /**
     * Déclenche l'événement et enregistre les informations sur l'environnement.
     */ 
    public function fire()
    {
        $this->_time = microtime(TRUE);
        $d = debug_backtrace();
        $this->_file = $d[0]['file'];
        $this->_line = $d[0]['line'];
        $this->_function = $d[1]['function'];
        $this->_class = (isset($d[1]['class'])) ? ($d[1]['class']) : (NULL);
        $this->_function_type = (isset($d[1]['type'])) ? ($d[1]['type']) : (NULL);
        $this->_client = (isset($_SERVER['REMOTE_ADDR'])) ? ($_SERVER['REMOTE_ADDR']) : (NULL);
        $this->_client_agent = (isset($_SERVER['HTTP_USER_AGENT'])) ? ($_SERVER['HTTP_USER_AGENT']) : (NULL);
        $this->_request_time = (isset($_SERVER['REQUEST_TIME_FLOAT'])) ? ($_SERVER['REQUEST_TIME_FLOAT']) : (NULL);
    }

    /**
     * Retourne TRUE si l'évènement est déclenché.
     * @return bool
     */
    public function pending()
    {
        return $this->_pending;
    }

    /**
     * Retourne le nom de l'événément.
     * @return string
     */
    public function name()
    {
        return $this->_name;
    }

    /**
     * Retourne le message de l'événément.
     * @return string
     */
    public function msg()
    {
        return $this->_msg;
    }

    /**
     * Retourne le type de l'événément.
     * @return string
     */
    public function type()
    {
        return $this->_type;
    }

    /**
     * Retourne le timestamp de l'événément.
     * @return number
     */
    public function time()
    {
        return $this->_time;
    }

    /**
     * Retourne le timestamp de la requête.
     * @return number
     */
    public function request_time()
    {
        return $this->_request_time;
    }

    /**
     * Retourne le fichier dans lequel l'événement est déclenché.
     * @return string
     */
    public function file()
    {
        return $this->_file;
    }

    /**
     * Retourne la ligne du fichier dans lequel l'événément est déclenché.
     * @return string
     */
    public function line()
    {
        return $this->_line;
    }

    /**
     * Retourne la fonction dans lequel l'événément est déclenché.
     * @return string
     */
    public function func()
    {
        return $this->_function;
    }
    
    /**
     * Retourn le type de la fonction ou méthode.
     * @return string
     */
    public function func_type()
    {
        return $this->_function_type;
    }

    /**
     * Retourne la classe dans lequel l'événément est déclenché si elle existe.
     * @return string
     */
    public function classe()
    {
        return $this->_class;
    }

    /**
     * Retourne l'IP du client qui a déclenché l'événement.
     * @return string
     */
    public function client()
    {
        return $this->_client;
    }

    /**
     * Retourne HTTP User Agent du client qui a déclenché l'événement.
     * @return string
     */
    public function client_agent()
    {
        return $this->_client_agent;
    }

    /**
     * Retourne les paramètres personnalisés.
     * @return array
     */
    public function args()
    {
        return $this->_args;
    }
}

?>