<?php
/**
 * Core intialise les dépendances fondamentales du framework.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses Access
 * @uses Base
 * @uses Browser
 * @uses Cache
 * @uses ClassLoader
 * @uses Config
 * @uses Controller
 * @uses Crypt
 * @uses Css
 * @uses Dao
 * @uses Debug
 * @uses ExceptionManager
 * @uses FileLogger
 * @uses Form
 * @uses Hook
 * @uses Javascript
 * @uses Request
 * @uses Ressource
 * @uses Route
 * @uses ServiceContainer
 * @uses Session
 * @uses Site
 * @uses Templace
 * @uses Upload
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
        // Début de la capture du tampon de sortie.
        ob_start();

        // Dossier applicatif pour le paramétrage.
        $this->_dir = str_replace('\\', '/', realpath(__DIR__.'/../../')).'/';

        // Initialise les services.
        $this->_init();
    }

    /**
     * Retourne le Controller.
     * @return Controller
     */
    public function get_controller() : Controller
    {
        // Chargement du Moteur du site.
        $filename = $this->_services->get('config')->path->root_dir.$this->_services->get('config')->path->controller.$this->_services->get('route')->get_controller().'.class.php';
        if (file_exists($filename) == FALSE)
        {
            throw new FireException('Controller introuvable', __FILE__, __LINE__);
        }
        require($filename);
        
        $controller = new Controller($this->_services);
        $controller->init();  
        return $controller;
    }

    /**
     * Initialise tous les services.
     */
    private function _init()
    {
        $this->_init_header();
        $loader = $this->_init_class_loader();
        $this->_init_service_container($loader);
        $this->_init_config();
        $this->_init_log();
        $this->_init_session();
        $this->_init_base();
        $this->_init_tpl();
        $this->_init_request();
        $this->_init_site();
        $this->_init_route();
        $this->_init_error_manager();
        $this->_init_access();
        $this->_init_cache();
        $this->_init_res();
        $this->_init_crypt();
        $this->_init_upload();
        $this->_init_browser();
        $this->_init_hook();
    }

    /**
     * Définie les entête HTTP du framework.
     */
    private function _init_header()
    {
        // Entête de la requête de retour.
        header('Content-type:text/html; charset=utf-8');

        // Protection contre l'iframe.
        header('X-Frame-Options: DENY');
    }

    /**
     * Initialise le ClassLoader.
     */
    private function _init_class_loader() : ClassLoader
    {
        // Recherche du loader.
        require($this->_dir.'lib/loader/ClassLoader.class.php');
        $loader = new ClassLoader();

        // Extention des classes.
        $loader->set_ext('class.php');

        // Chargement des classes du framework.
        $loader->add_dir_recursive($this->_dir.'lib', ['obsolete']);
        $loader->enable();

        return $loader;
    }

    /**
     * Initialise le ServiceContainer.
     * @param ClassLoader $loader
     */
    private function _init_service_container(ClassLoader $loader)
    {
        $this->_services = ServiceContainer::get_instance();

        // Changement du class loader en tant que service.
        $this->_services->set('loader', function() use ($loader) {
            return $loader;
        });
    }

    /**
     * Initialise le Gestionnaire de Config.
     */
    private function _init_config()
    {
        // Définition des paramètres et de la configuration.
        $this->_services->set('config', function(){
            $service = new Config($this->_dir.'var/config.json');
            $service->path->root_dir = str_replace('\\', '/', realpath($this->_dir.'/../')).'/';
            $root = (substr($_SERVER['DOCUMENT_ROOT'], -1) == '/') ? (substr($_SERVER['DOCUMENT_ROOT'], 0, -1)) : ($_SERVER['DOCUMENT_ROOT']);
            $root = str_replace($root, 'http://' . $_SERVER['SERVER_NAME'], $service->path->root_dir);
            $service->path->root_url = (substr($root, - 1) != '/') ? ($root . '/') : ($root);
            return $service;
        });

        // Ajoute des dossiers de classe pour le class loader.
        foreach ($this->_services->get('config')->class_dirs as $dir)
        {
            $this->_services->get('loader')->add_dir($this->_services->get('config')->path->root_dir.$dir);
        }  

        // Importe les interfaces.
        $this->_services->get('loader')->import($this->_dir.'/lib/interfaces/*.interface.php');
        
        // Fuseau horaire français.
        date_default_timezone_set($this->_services->get('config')->system->timezone); 

        // Dossier pour log debug.
        Debug::dir($this->_services->get('config')->path->root_dir.$this->_services->get('config')->path->log); 

        // Définition de la classe par défaut des formulaires.
        Form::set_default_class($this->_services->get('config')->system->css_class_form);
    }

    /**
     * Initialise le Gestionnaire de Log.
     */
    private function _init_log()
    {
        // Paramétrage des logs.
        $this->_services->set('log', function() {
            $service = new FileLogger($this->_services->get('config')->path->log, $this->_services->get('config')->log->granularity);
            return $service;
        });
    }

    /**
     * Initialise la Session.
     */
    private function _init_session()
    {
        $this->_services->set('session', function(){
            return Session::get_instance();
        });

        // Gestion des singletons de session.
        if ($this->_services->get('config')->feature->session_instance == FALSE)
        {
            SingletonSession::disable_save();
        }
    }

    /**
     * Initialise la Connexion à la base de données.
     */
    private function _init_base()
    {
        $this->_services->set('base', function() {
            $service = new Base();
            $service->connect(
                $this->_services->get('config')->db->host, 
                $this->_services->get('config')->db->name, 
                $this->_services->get('config')->db->user, 
                $this->_services->get('config')->db->pass, 
                $this->_services->get('config')->db->charset
            );
            if ($this->_services->get('config')->feature->cache && $this->_services->get('config')->db->cache)
            {
                $service->set_cache(TRUE, $this->_services->get('config')->path->sql_cache, $this->_services->get('config')->db->cache_time);
            }
            return $service;
        });

        // Définition de la liaison de la base de données à la classe Model.
        Dao::set_base($this->_services->get('base'));
    }

    /**
     * Initialise le Moteur de Templates.
     */
    private function _init_tpl()
    {
        if ($this->_services->get('config')->tpl->enable)
        {
            $this->_services->set('tpl', function() {
                $service = new Template($this->_services->get('config')->path->tpl_cache);
                $service->set_syntaxe(Template::SMARTY_STRICT);
                if ($this->_services->get('config')->feature->tpl_save)
                {
                    $service->save_tpl();
                }
                return $service;
            });
        }
    }

    /**
     * Initialise la réception des données.
     */
    private function _init_request()
    {
        $this->_services->set('req', function() {
            $service = Request::get_instance();
            if ($this->_services->get('config')->feature->secure_html_post)
            {
                $service->check_html();
            }
            if ($this->_services->get('config')->feature->multi_request == FALSE)
            {
                $service->check_multipost();
            }
            return $service;
        });
    }

    /**
     * Initialise le Gestionnaire du site.
     */
    private function _init_site()
    {
        $this->_services->set('site', function() {
            $service = Site::get_instance($this->_services->get('session'), $this->_services->get('tpl'), $this->_services->get('req'));
            $config = $this->_services->get('config');
            $service->set_default_module($config->system->default_module);
            $service->set_default_action($config->system->default_action);
            if ($this->_services->get('config')->tpl->enable)
            {
                $service->set_tpl_name_title($config->tpl->title_site);
                $service->set_tpl_name_description($config->tpl->description_site);
            }
            return $service;
        });
    }

    /**
     * Initialise le Routage.
     */
    private function _init_route()
    {
        $this->_services->set('route', function() {
            $service = Route::get_instance($this->_services->get('req'), $this->_services->get('config')->path->root_url, $this->_services->get('config')->path->root_dir);
            $service->init($this->_services->get('config')->path->root_dir.$this->_services->get('config')->route->file);
            return $service;
        });
    }

    /**
     * Initialise le Gestionnaire d'erreurs.
     */
    private function _init_error_manager()
    {
        $this->_services->set('error', function() {
            $service = ExceptionManager::get_instance();
            $service->start();
            $service->set_file($this->_services->get('config')->path->root_dir.$this->_services->get('config')->path->log.'error.log');
            $service->add_data('ip',(isset($_SERVER['REMOTE_ADDR'])) ? ($_SERVER['REMOTE_ADDR']) : ('null'));
            $service->add_data('module',$this->_services->get('route')->get_controller().'/'.$this->_services->get('route')->get_module());
            $service->add_data('action',$this->_services->get('route')->get_action());
            $service->active_error();
            $service->active_exception();
            if ($this->_services->get('config')->feature->error_log)
            {
                $service->active_save();
            }
            if ($this->_services->get('config')->tpl->enable === FALSE || $this->_services->get('config')->feature->error_show == FALSE)
            {
                $service->hide();
            }
            return $service;
        });
    }
    
    /**
     * Initialise le Gestionnaire des droits d'accès.
     */
    private function _init_access()
    {
        $this->_services->set('access', function() {
            $service = new Access();
            if ($this->_services->get('config')->feature->access)
            {
                $service->enable();
            }
            else
            {
                $service->disable();
            }
            return $service;
        });
    }

    /**
     * Initialise le Gestionnaire du Cache.
     */
    private function _init_cache()
    {
        $this->_services->set('cache', function() {
            $service = Cache::get_instance($this->_services->get('config')->path->root_dir.$this->_services->get('config')->path->cache);
            if ($this->_services->get('config')->feature->cache)
            {
                $service->enable();
            }
            else
            {
                $service->disable();
            }
            return $service;
        });
    }
    
    /**
     * Initialise le Gestionnaire des ressources envoyées au client.
     */
    private function _init_res()
    {
        // Gestion des ressources si le templeting est activé.
        if ($this->_services->get('config')->tpl->enable)
        {	
            // Gestion du CSS.
            $this->_services->set('css', function() {
                $service = Css::get_instance($this->_services->get('config')->path->css_cache);
                $service->set_cache_time(7200);
                $service->enable_minification($this->_services->get('config')->feature->minify_ressource);
                return $service;
            });
            
            // Gestion du JavaScript.
            $this->_services->set('js', function() {
                $service = Javascript::get_instance($this->_services->get('config')->path->js_cache);
                $service->set_cache_time(7200);
                $service->enable_minification($this->_services->get('config')->feature->minify_ressource);
                return $service;
            });
        }
    }
	
    /**
     * Initialise le Outil de Cryptage.
     */
    private function _init_crypt()
    {
        // Définition de la clé de cryptage par défaut.
        $this->_services->set('crypt', function() {
            Crypt::set_default_key($this->_services->get('config')->security->key_crypt);
            Crypt::set_salts($this->_services->get('config')->security->prefix_salt, $this->_services->get('config')->security->suffix_salt);
            $service = new Crypt();
            return $service;
        });
    }
    
    /**
     * Initialise le Gestionnaire de téléchargement.
     */
    private function _init_upload()
    {
        // Définition de la base de données des types mimes et des extensions de fichiers.
        $this->_services->set('upload', function() {
            return Upload::get_instance($this->_services->get('config')->path->root_dir.$this->_services->get('config')->upload->mime_types_file);
        });
    }
    
    /**
     * Initialise l'Outil d'information sur le navigateur.
     */
    private function _init_browser()
    {
        // Gestion des informations sur le navigateur du client.
        $this->_services->set('browser', function() {
            $service = Browser::get_instance($this->_services->get('config')->browser->file);
            return $service;
        });
    }  

    /**
     * Initialise le gestionnaire d'appel des modules.
     */
    private function _init_hook()
    {
        // Gestion des informations sur le navigateur du client.
        $this->_services->set('hook', function() {
            $service = new Hook();
            return $service;
        });

        // Récupération de tous les modules.
        $dir = dirname($this->_dir).'/'.$this->_services->get('config')->path->module.'*/*/module.php';
        $module_files = glob($dir); 
        foreach ($module_files as $file)
        {
            require($file);
            $class = basename(dirname($file));
            $this->_services->get('hook')->add(new $class($this->_services));
        }
    }  
}
?>