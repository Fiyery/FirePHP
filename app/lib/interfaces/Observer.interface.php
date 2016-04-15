<?php
/**
 * Observer définit un observateur d'événement d'une classe Observable.
 */
interface Observer
{
    /**
     * Liste des événements à écouter.
     * @var array<string>
     */
    private $_listen = [];
   
    /**
     * Ajout des événéments à écouter.
     * @param array $name Nom des événements.
     */
    public function listen(array $name);
   
    /**
     * Traite l'action suite à un événement généré par la classe à observer.
     * @param unknown $event
     */
    public function notify($event);
}
?>