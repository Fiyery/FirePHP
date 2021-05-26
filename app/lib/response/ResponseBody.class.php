<?php
namespace FirePHP\Response;

/**
 * Classe de gestion pour le corps de la réponse.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 */
class ResponseBody
{
    /**
     * Corps de la réponse.
     * @var string
     */
    protected $_content = "";

    /**
     * Définie le corps.
     * @param string $value
     */
    public function set(string $value)
    {
        $this->_content = $value;
    }

    /**
     * Retourne le corps.
     * @return string $value
     */
    public function get(string $value) : string
    {
        return $this->_content;
    }
}
?>