<?php
namespace FirePHP\Response;

/**
 * Interface sur la réponse d'une requête.
 */
interface ResponseInterface 
{
    /**
     * Définie et retourne le code retour.
     * @return int
     */
    public function status_code(int $code = NULL) : int;

    /**
     * Retourne la classe de gestion des entêtes.
     * @return ResponseHeader
     */
    public function header() : ResponseHeader;

    /**
     * Retourne la classe de gestion des messages informatifs.
     * @return ResponseAlert
     */
    public function alert() : ResponseAlert;
    
    /**
     * Retourne la classe de gestion du corps.
     * @return ResponseBody
     */
    public function body() : ResponseBody;
}
?>