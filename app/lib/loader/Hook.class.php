<?php
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
        foreach ($this->_modules as $m)
        {
            $return = $return || $m->notify($e);
        }
        return $return;
    }

    /**
     * Ecoute tous les événéments qu'elle reçoit donc elle n'a pas besoin de définir cette fonction.
     */
    public function listen(string $event)
    {

    }
}
?>