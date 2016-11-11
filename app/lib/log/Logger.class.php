<?php
/**
 * Logger défini le comportement minimal d'un d'une classe qui traite les journaux d'événements.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses Observer
 */
abstract class Logger implements Observer
{
    /**
     * Liste des événements reçus.
     * @var array
     */
    protected $_events = [];

    /**
     * Constructeur.
     */
    public function __contruct()
    {

    }

    /**
     * Traite l'action suite à un événement généré par la classe à observer.
     * @param Event $event
     */
    public function notify(Event $event)
    {
        $this->_events[] = $event;
    }

    /**
     * Sauvegarde les informations récupérées.
     */
    public abstract function write();
}
?>