<?php
/**
 * Observer définit un observateur d'événement d'une classe Observable.
 */
interface Observer
{
    /**
     * Ajout des événéments à écouter.
     * @param string $name Nom des événements.
     * @return Observer
     */
    public function listen(string $name);
   
    /**
     * Traite l'action suite à un événement généré par la classe à observer.
     * @param Event $event
     * @return bool
     */
    public function notify(Event $event) : bool;
}
?>