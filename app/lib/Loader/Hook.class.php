<?php
namespace FirePHP\Loader;

use Throwable;
use FirePHP\Event\Observer;
use FirePHP\Event\Event;
use FirePHP\Controller\Module;
/**
 * Hook permet l'importation et l'exécution de module.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses Event
 * @uses Module
 */
class Hook implements Observer
{	
    /**
     * Liste des modules à déclencher.
     * @var Module[]
     */
    protected $_modules = [];

    /**
     * Dernier module déclenché.
     * @var Module
     */
    protected $_last_loaded = NULL;

    /**
     * Constructeur.
     */
    public function __construct()
    {

    }

    /**
     * Ajoute un module au gestionnaire.
     * @param Module $module
     */
    public function add(Module $module)
    {
        $this->_modules[] = $module;
    }

    /**
     * Informe les modules du déclenchement d'un événement.
     * @param Module $module
     * @return bool
     */
    public function notify(Event $e) : bool
    {
        $return = FALSE;
        $throwables = [];
        foreach ($this->_modules as $m)
        {
            // On entoure la notification d'un try catch pour permettre l'execution des autres modules en cas d'arrêt.
            try
            {
                if ($m->notify($e))
                {
                    $this->_last_loaded = $m;
                    $return = TRUE;
                }
            }
            catch (Throwable $t)
            {
                $throwables[] = $t;
            }
        }
        
        // Lance les Throwable à la fin de la notification de tous les modules.
        if (count($throwables) > 0)
        {
            throw $throwables[0];
        }
        
        return $return;
    }

    /**
     * Retourne le dernier module lancé.
     * @return Module
     */
    public function last_loaded() : Module
    {
        return $this->_last_loaded;
    }

    /**
     * Ecoute tous les événéments qu'elle reçoit donc elle n'a pas besoin de définir cette fonction.
     */
    public function listen(string $event)
    {

    }
}
?>