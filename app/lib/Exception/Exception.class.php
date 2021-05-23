<?php
namespace FirePHP\Exception;

use Exception as PHPException;
/**
 * FireException est l'exception personnalisée de l'application.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 */
class Exception extends PHPException
{    
    /**
     * Constructeur.
     * @param string $message Message de l'erreur.
     * @param string $file Chemin du ficher.
     */
    public function __construct($message, $backtrace_level=1)
    {
        parent::__construct($message);
        $d = debug_backtrace();
        if (isset($d[$backtrace_level]))
        {
        	$this->file = $d[$backtrace_level]['file'];
        	$this->line = $d[$backtrace_level]['line'];
        }
    }
}
?>