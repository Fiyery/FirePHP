<?php
/**
 * DaoObservable permet d'observer les événements de Dao même les méthodes statiques.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses Event
 * @uses Observable
 * @uses Observer
 */
class DaoObservable implements Observable
{
    /**
	 * Liste des observeurs.
	 * @var array
	 */
	private $_observers = [];
	
	/**
	 * Constructeur.
	 */
	public function __construct()
	{

	}

    /**
     * Ajoute un observateur à l'objet.
     * @param Observer $observer
     */
    public function attach(Observer $observer)
	{
		$this->_observers[] = $observer;
	}

	/**
     * Supprime un observateur de l'objet.
     * @param Observer $observer
     */
    public function detach(Observer $observer)
	{
		unset($this->_observers[array_search($observer, $this->_observers)]);
	}

	/**
     * Génère un événement.
     * @param Event $event
     */
    public function notify(Event $event)
	{
		foreach ($this->get_observers() as $observer)
		{
			$observer->notify($event);
		}
	}
    
    /**
     * Retourne tous les observateurs de l'objet.
     * @return Observer[]
     */
    public function get_observers() : array
	{
		return $this->_observers;
	}
}
?>