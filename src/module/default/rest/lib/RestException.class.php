<?php
/**
 * HTTP REST Exception.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 */
class RestException extends FireException
{
    const ERROR_SQL = 'ERROR_SQL';
    const INVALID_FOREIGN_KEY = 'INVALID_FOREIGN_KEY';
    const INVALID_METHOD = 'INVALID_METHOD';
    const MISSING_INSTANCE = 'MISSING_INSTANCE';
    const MISSING_INSTANCE_ID = 'MISSING_INSTANCE_ID';
    const MISSING_NAME = 'MISSING_NAME';
    const NOT_FOUND_CONFIG = 'NOT_FOUND_CONFIG';
    const NOT_FOUND_TABLE = 'NOT_FOUND_TABLE';
    const UNKNOWN = 'UNKNOWN';

    /**
     * More information about exception.
     * @var string
     */
     private $_msg;


    /**
     * Construtor.
     * @param string $rest_code Error code.
     * @param string $msg More information about exception.
     */
    public function __construct ($rest_code = RestException::UNKNOWN)
    {
        parent::__construct($rest_code);
    }
}
?>