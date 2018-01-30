<?php
/**
 * Environment permet la différentiation de l'application selon les plateformes.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses ConfigValue
 */
class Environment 
{
    /**
     * Port par défaut si inexistant dans la config.
     */
    const DEFAULT_PORT = 80;

    /**
     * Environnement par défaut.
     */
    const DEFAULT_ENV = "default";
    
    /**
     * Liste des environnements.
     * @var array
     */
    protected $_plateformes = [];

    /**
     * Nom de l'environnement sélectionné.
     * @var string
     */
    protected $_selected = self::DEFAULT_ENV;

    /**
     * Ip du serveur.
     * @var string
     */
    protected $_ip = NULL;

    /**
     * Nom de domaine du serveur.
     * @var string
     */
    protected $_dns = NULL;

    /**
     * Numéro de port du serveur.
     * @var string
     */
    protected $_port = NULL;

    /**
     * Constructeur.
     * @param string $file Chemin du fichier de configuration.
     */
    public function __construct(string $file) 
    {
        if (file_exists($file))
        {
            $env = json_decode(file_get_contents($file));
            foreach ($env as $name => $config)
            {
                $this->_plateformes[$name] = $config;
            }
        }
        $this->_init();
    }

    /**
     * Défini l'environnement sélectionné.
     */
    public function _init()
    {
        $env_ip = strtolower($this->ip().":".$this->port());
        $env_dns = strtolower($this->dns().":".$this->port());
        foreach ($this->_plateformes as $name => $p)
        {
            $match = FALSE;
            if (isset($p->port) && is_array($p->port))
            {
                $ports = $p->port;
            }
            else 
            {
                $ports = [self::DEFAULT_PORT];
            }
            foreach ($ports as $port)
            {
                if (isset($p->ip))
                {
                    foreach ($p->ip as $ip)
                    {
                        if (strtolower($ip.":".$port) === $env_ip)
                        {
                            $match = TRUE;
                        }
                    }
                }
                if (isset($p->dns) && $match === FALSE)
                {
                    foreach ($p->dns as $dns)
                    {
                        if (strtolower($dns.":".$port) === $env_dns)
                        {
                            $match = TRUE;
                        }
                    }
                }
            }
            if ($match) 
            {
                $this->_selected = $name;
            }
        }
    }

    /**
     * Retourne l'ip du serveur.
     * @return string
     */
    public function ip() : string
    {
        if ($this->_ip === NULL)
        {
            $this->_ip = (isset($_SERVER["SERVER_ADDR"])) ? ($_SERVER["SERVER_ADDR"]) : ("");
        }
        return $this->_ip;
    }

    /**
     * Retourne le nom de domaine du serveur.
     * @return string
     */
    public function dns() : string
    {
        if ($this->_dns === NULL)
        {
            $this->_dns = (isset($_SERVER["SERVER_NAME"])) ? ($_SERVER["SERVER_NAME"]) : ("");
        }
        return $this->_dns;
    }

    /**
     * Retourne le numéro de port du serveur.
     * @return string
     */
    public function port() : string
    {
        if ($this->_port === NULL)
        {
            $this->_port = (isset($_SERVER["SERVER_PORT"])) ? ($_SERVER["SERVER_PORT"]) : ("");
        }
        return $this->_port;
    }

    /**
     * Retourne l'environnement défini.
     * @return string
     */
    public function get() : string
    {
        return $this->_selected;
    }
}
?>