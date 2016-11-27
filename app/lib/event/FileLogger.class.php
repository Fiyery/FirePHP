<?php
/**
 * FileLogger enregistre les événéments dans des fichiers.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses Logger
 */
class FileLogger extends Logger
{
    /**
     * Niveau de ganularité.
     */
    const TIME_DAY = 1;
    const TIME_MONTH = 2;
    const TIME_YEAR = 3;
    const TIME_NONE = 4;
    
    /**
     * Chemin du dossier où seront stocké les fichiers de log.
     * @var string
     */
    protected $_path;

    /**
     * Définie la répartition des fichiers de log s'ils doivent être séparés par jour, mois ou année.
     * @var integer
     */
    protected $_granularity;

    /**
     * Constructeur.
     */
    public function __contruct($path = '.', $granularity = self::TIME_MONTH)
    {
        parent::__contruct();
        $this->_granularity = $granularity;
        $this->path($path);
    }

    /**
     * Défini le niveau de granularité à prendre en compte dans le fichier.
     * @param int @int Niveau de détail.
     * @return FileLogger
     */
    public function granularity($int)
    {
        if (is_numeric($int) === FALSE || $int <= 0 || $int >= 4)
        {
            return FALSE;
        }
        $this->_granularity = $int;
        return $this;
    }

    /**
     * Défini le dossier des logs.
     * @param string $path Chemin du dossier.
     * @return FileLogger
     */
    public function path($path)
    {
        if (is_string($path) === FALSE || (file_exists($path) && is_dir($path) === FALSE))
        {
            return FALSE;
        }
        if (file_exists($path) === FALSE)
        {
            mkdir($path, 0775, TRUE);
        }
        $this->_path = $path;
        return $this;
    }

    /**
     * Sauvegarde les informations récupérées.
     */
    public function log()
    {
        $this->_path = (substr(str_replace('\\', '/', $this->_path), -1) !== '/') ? ($this->_path.'/') : ($this->_path);
        foreach ($this->_events as $e)
        {
            $filename = $this->_path;
            if ($e->type() !== NULL)
            {
                $filename .= $e->type().'/';
                if (file_exists($filename) === FALSE)
                { 
                    mkdir($filename, 0775, TRUE);
                }
            }
            switch ($this->_granularity)
            {
                case self::TIME_DAY : $filename .= date('Y-m-d', $e->context()->time()).'.log'; break;
                case self::TIME_MONTH : $filename .= date('Y-m', $e->context()->time()).'.log'; break;
                case self::TIME_YEAR : $filename .= date('Y', $e->context()->time()).'.log'; break;
                default:$filename .= 'default.log';
            }
            $f = fopen($filename, 'a+');
            $content = date('Y-m-d H:i:s', $e->context()->time()).' '.$e->msg().
                "\n\tTime : ".$e->context()->time().' ms'.
                "\n\tRequest time : ".$e->context()->request_time().' ms'.
                "\n\tFile : ".$e->context()->file().':'.$e->context()->line().
                "\n\tFunction : ".(($e->context()->classe() !== NULL) ? ($e->context()->classe().$e->context()->func_type().$e->context()->func()) : ($e->context()->func())).'()'.
                "\n\tClient : ".$e->context()->client().' ('.$e->context()->client_agent().')';
            foreach ($e->context()->args() as $name => $value)
            {
                if (is_scalar($value) === FALSE)
                {
                    $value = json_encode($value);
                }
                $content .= "\n\t".$name.' : '.$value;
            }
            fwrite($f, $content."\n");
            fclose($f);
        }
    }

    /**
     * Ajout des événéments à écouter.
     * @param array $name Nom des événements.
     */
    public function listen(array $name)
    {

    }
}
?>