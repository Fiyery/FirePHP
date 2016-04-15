<?php
/**
 * Error est l'exception personnalisée de l'application.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @copyright 2011-2015 Yoann Chaumin
 */
class Error extends Exception
{    
    /**
     * Constructeur.
     * @param string $message Message de l'erreur.
     * @param string $file Chemin du ficher.
     * @param int $line Numéro de la ligne.
     */
    public function __construct($message, $file, $line)
    {
        parent::__construct($message);
        $this->file = $file;
        $this->line = $line;
    }
}
?>