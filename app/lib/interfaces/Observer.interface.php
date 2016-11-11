<?php
/**
 * Observer définit un observateur d'événement d'une classe Observable.
 */
interface Observer
{
    /**
     * Ajout des événéments à écouter.
     * @param array $name Nom des événements.
     */
    public function listen(array $name);
   
    /**
     * Traite l'action suite à un événement généré par la classe à observer.
     * @param Event $event
     */
    public function notify(Event $event);
}
?>