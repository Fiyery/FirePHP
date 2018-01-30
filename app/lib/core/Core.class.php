<?php
/**
 * Core intialise les dépendances fondamentales du framework.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses Hook
 * @uses ServiceContainer
 */
class Core
{
    /**
     * Instance du ServiceContainer.
     * @var ServiceContainer
     */
    private $_services = NULL;

    /**
     * Dossier appplicatif du framework.
     * @var string
     */
    private $_dir = NULL;

    /**
     * Constructeur.
     */
    public function __construct()
    {
        // Dossier applicatif pour le paramétrage.
        $this->_dir = str_replace("\\", "/", realpath(__DIR__."/../../"))."/";

        // Initialise les services.
        $this->_init();
    }

    /**
     * Initialise tous les services.
     */
    private function _init()
    {
        $loader = $this->_init_class_loader();
        $env = $this->_init_env();
        $this->_init_service_container($loader, $env);
        $this->_init_hook();
    }


    /**
     * Initialise le ClassLoader.
     */
    private function _init_class_loader() : ClassLoader
    {
        // Recherche du loader.
        require($this->_dir."lib/loader/ClassLoader.class.php");
        $loader = new ClassLoader();

        // Extention des classes.
        $loader->set_ext("class.php");

        // Chargement des classes du framework.
        $loader->add_dir_recursive($this->_dir."lib", ["obsolete"]);
        $loader->enable();

        // Importe les interfaces.
        $loader->import($this->_dir."/lib/interfaces/*.interface.php");

        return $loader;
    }

    /**
     * Initialise l'Environnement.
     */
    private function _init_env() : Environment
    {
        // Gestion des environnements.
        $env = new Environment($this->_dir."var/env.json");
        return $env;
    }

    /**
     * Initialise le ServiceContainer.
     * @param ClassLoader $loader
     */
    private function _init_service_container(ClassLoader $loader, Environment $env)
    {
        $this->_services = ServiceContainer::get_instance();

        // Changement du ClassLoader en tant que service.
        $this->_services->set("loader", function() use ($loader) {
            return $loader;
        });

        // Changement du Environment en tant que service.
        $this->_services->set("env", function() use ($env) {
            return $env;
        });

        // Instanciation des services.
        $this->_services->init($this->_dir."var/service.json");
    }

    /**
     * Initialise le gestionnaire d'appel des modules.
     */
    private function _init_hook()
    {
        // Instance du Hook.
        $this->_services->set_instance("hook", new Hook());

        // Récupération de tous les modules.
        $dir = dirname($this->_dir)."/".$this->_services->get("config")->path->module."*/*/module.php";
        $module_files = glob($dir); 
        foreach ($module_files as $file)
        {
            require($file);
            $class = preg_replace("#[^[a-zA-Z0-9]]*#", "", basename(dirname($file)));
            $class = $this->_services->get("config")->system->prefix_module_class.$class.$this->_services->get("config")->system->suffix_module_class;
            $this->_services->get("hook")->add(new $class($this->_services));
        }

        // Le Hook observe le ServiceContainer pour configurer les services.
        $this->_services->attach($this->_services->get("hook"));

        // Configuration des instances de services déjà appelées. 
        foreach ($this->_services->list_instances() as $service)
        {
            $this->_services->get("hook")->notify(new Event("Service::config_".$service));
        }
    }  

    /**
     * Retourne le Controller.
     * @return Controller
     */
    public function get_controller() : Controller
    {
        // Chargement du Moteur du site.
        $filename = $this->_services->get("config")->path->root_dir.$this->_services->get("config")->path->controller.$this->_services->get("router")->get_controller().".class.php";
        if (file_exists($filename) === FALSE)
        {
            throw new FireException("Controller introuvable", __FILE__, __LINE__);
        }
        require($filename);
        
        $controller = new Controller($this->_services);
        $controller->init();  
        return $controller;
    }
}
?>