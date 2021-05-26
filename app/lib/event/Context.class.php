<?php
namespace FirePHP\Event;
/**
 * Context est une classe qui sauvegarde les informations de base de l'environnement à sa création.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses Logger
 */
class Context
{
    /**
     * Timestamp du context.
     * @var number
     */
    protected $_time;

    /**
     * Timestamp de la requête.
     * @var number
     */
    protected $_request_time;

    /**
     * Fichier dans lequel le context est sauvegardé.
     * @var string
     */
    protected $_file;

    /**
     * Ligne du fichier dans lequel le context est sauvegardé.
     * @var string
     */
    protected $_line;

    /**
     * HTTP User Agent du client sauvegardé.
     * @var string
     */
    protected $_function;
    
    /**
     * Type de la fonction ou méthode.
     * @var string
     */
    protected $_function_type;

    /**
     * Classe dans lequel le context est sauvegardé si elle existe.
     * @var string
     */
    protected $_class;

    /**
     * IP du client.
     * @var string
     */
    protected $_client;

    /**
     * HTTP User Agent du client.
     * @var string
     */
    protected $_client_agent;

    /**
     * Tampon de sortie.
     * @var string
     */
    protected $_echo;

    /**
     * Informations personnalisées envoyées au contructeur.
     * @var array
     */
    protected $_args;

    /** 
     * Contructeur d'un context.
     * @param array $agrs Paramètres personnalisés.
     * @param array $agrs Niveau de l'environnement à récupérer (1 = courant, 2 = parent, 3 = parent du parent, etc...).
     */ 
    public function __construct(array $args=[], int $level=0)
    {
        $this->_args = $args;
        $this->_time = microtime(TRUE);
        $d = debug_backtrace();
        $this->_file = $d[$level]['file'];
        $this->_line = $d[$level]['line'];
        $this->_function = $d[$level+1]['function'];
        $this->_class = (isset($d[$level+1]['class'])) ? ($d[$level+1]['class']) : (NULL);
        $this->_function_type = (isset($d[$level+1]['type'])) ? ($d[$level+1]['type']) : (NULL);
        $this->_client = (isset($_SERVER['REMOTE_ADDR'])) ? ($_SERVER['REMOTE_ADDR']) : (NULL);
        $this->_client_agent = (isset($_SERVER['HTTP_USER_AGENT'])) ? ($_SERVER['HTTP_USER_AGENT']) : (NULL);
        $this->_request_time = (isset($_SERVER['REQUEST_TIME_FLOAT'])) ? ($_SERVER['REQUEST_TIME_FLOAT']) : (NULL);
        $this->_echo = ob_get_contents();
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
     * Retourne le fichier dans lequel l'événement est sauvegardé.
     * @return string
     */
    public function file()
    {
        return $this->_file;
    }

    /**
     * Retourne la ligne du fichier dans lequel l'événément est sauvegardé.
     * @return string
     */
    public function line()
    {
        return $this->_line;
    }

    /**
     * Retourne la fonction dans lequel l'événément est sauvegardé.
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
     * Retourne la classe dans lequel l'événément est sauvegardé si elle existe.
     * @return string
     */
    public function classe()
    {
        return $this->_class;
    }

    /**
     * Retourne l'IP du client qui a sauvegardé l'événement.
     * @return string
     */
    public function client()
    {
        return $this->_client;
    }

    /**
     * Retourne HTTP User Agent du client qui a sauvegardé l'événement.
     * @return string
     */
    public function client_agent()
    {
        return $this->_client_agent;
    }

    /**
     * Retourne le tampon de sortie.
     * @return string
     */
    public function echo()
    {
        return $this->_echo;
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