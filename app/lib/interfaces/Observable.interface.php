<?php
/**
 * Observable définit un objet qui peut être suivi par un Observer.
 */
interface Observable 
{
    /**
     * Premier état de l'objet.
     * @var int
     */
    const BEGIN = 0;
    
    /**
     * Dernier état de l'objet.
     * @var int
     */
    const END = -1;
    
    /**
     * Etat de l'objet.
     * @var int
     */
    private $_state = 0;

    /**
     * Liste des noms des événements que la classe peut émettre.
     * @var array<string>
     */
    private $_events = [];
    
    /**
     * Ajoute un observateur à l'objet.
     * @param Observer $observer
     */
    public function attach(Observer $observer);
    
    /**
     * Supprime un observateur de l'objet.
     * @param Observer $observer
     */
    public function detach(Observer $observer);
    
    /**
     * Génère un événement.
     * @param Event $event
     */
    public function notify(Event $event);
    
    /**
     * Définit et retourne l'état de l'objet.
     * @param string $state
     */
    public function state($state=NULL);
    
    /**
     * Retourne tous les observateurs de l'objet.
     * @return array<Observer>
     */
    public function get_observers();
}
?>