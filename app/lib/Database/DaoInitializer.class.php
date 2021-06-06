<?php
namespace FirePHP\Database;

use FirePHP\Event\Observer;
use FirePHP\Event\Event;
use FirePHP\Loader\Hook;
/**
 * DaoInitializer permet d'instancier la Database si un appel est fait.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses Event
 * @uses Observable
 * @uses Observer
 * @uses Hook
 */
class DaoInitializer implements Observer
{
    /**
	 * Instance du Hook.
	 * @var Hook
	 */
	private $_hook;

    /**
	 * Constructeur.
     * @param Hook $hook Instance du Hook.
	 */
	public function __construct(Hook $hook)
	{
        $this->_hook = $hook;
	}

    /**
     * Ajout des événéments à écouter.
     * @param string $name Nom des événements.
     * @return Observer
     */
    public function listen(string $name)
    {

    }
   
    /**
     * Traite l'action suite à un événement généré par la classe à observer.
     * @param Event $event
     * @return bool
     */
    public function notify(Event $event) : bool
    {
        // Détachement du parent : On n'initialise que la première fois.
        $event->caller()->detach($this);


        // Lancement de l'event pour configuer le service Database.
        $this->_hook->notify(new Event('Service::config_database'));
        
        return TRUE;
    }
}