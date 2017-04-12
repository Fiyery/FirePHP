<?php
/**
 * Observable définit un objet qui peut être suivi par un Observer.
 */
interface Observable 
{
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
     * Retourne tous les observateurs de l'objet.
     * @return Observer[]
     */
    public function get_observers();
}
?>