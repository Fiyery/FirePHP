<?php
use FirePHP\Controller\Module; 
use FirePHP\Debug\Debug; 
use FirePHP\Html\Form\Form; 
use FirePHP\Database\Dao; 
use FirePHP\Database\DaoInitializer; 
use FirePHP\Architecture\SingletonSession;
use FirePHP\Html\Template;

class ServiceModule extends Module
{
    /**
     * Paramétrage du Service Config.
     */
    public function action_config_config()
    {
        // Paramétrage dy chemin de dossier de l'application.
        $this->config->path->root_dir = str_replace('\\', '/', getcwd()).'/';
        $root = (substr($_SERVER['DOCUMENT_ROOT'], -1) == '/') ? (substr($_SERVER['DOCUMENT_ROOT'], 0, -1)) : ($_SERVER['DOCUMENT_ROOT']);
        $http = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? ('https') : ('http');
        $root = str_replace($root, $http.'://' . $_SERVER['SERVER_NAME'], $this->config->path->root_dir);
        $this->config->path->root_url = (substr($root, - 1) != '/') ? ($root . '/') : ($root);
        
        // Ajoute des dossiers de classe pour le class loader.
        foreach ($this->config->class_dirs as $dir)
        {
            $this->loader->add_dir_recursive($this->config->path->root_dir.$dir);
        }
        
        // Fuseau horaire français.
        date_default_timezone_set($this->config->system->timezone); 

        // Dossier pour log debug.
        Debug::dir($this->config->path->root_dir.$this->config->path->log); 

        // Définition de la classe par défaut des formulaires.
        Form::set_default_class($this->config->system->css_class_form);

        // Définition des préfixs de table.
        Dao::table_prefix($this->config->db->table_prefix);
    }

    /**
     * Paramétrage du Service Session.
     */
    public function action_config_session()
    {
        // Gestion des singletons de session.
        if ($this->config->feature->session_instance == FALSE)
        {
            SingletonSession::disable_save();
        }
    }

    /**
     * Paramétrage du Service Database.
     */
    public function action_config_database()
    {
        if ($this->request->_service_config_database !== NULL)
        {
            return TRUE;
        }
        $this->request->_service_config_database = time();

        // Connexion à la base de données.
        try 
        {
            $this->database->connect(
                $this->config->db->host, 
                $this->config->db->name, 
                $this->config->db->user, 
                $this->config->db->pass, 
                $this->config->db->charset
            );
        }
        catch (Throwable $t)
        {
            $this->error->handle_throwable($t);
        }
        if ($this->config->feature->cache && $this->config->db->cache)
        {
            $this->database->set_cache(TRUE, $this->config->path->sql_cache, $this->config->db->cache_time);
        }

        // Définition de la liaison de la base de données à la classe Model.
        Dao::base($this->database);
    }

    /**
     * Paramétrage du Service Template.
     */
    public function action_config_template()
    {
        $this->tpl->set_syntaxe(Template::SMARTY_STRICT);
        if ($this->config->feature->tpl_save)
        {
            $this->tpl->save_tpl();
        }        
    }

    /**
     * Paramétrage du Service Request.
     */
    public function action_config_request()
    {
        if ($this->config->feature->secure_html_post)
        {
            $this->request->check_html();
        }
        if ($this->config->feature->multi_request == FALSE)
        {
            $this->request->check_multipost();
        }     
    }

    /**
     * Paramétrage du Service Router.
     */
    public function action_config_router()
    {
        $this->router->init(
            $this->config->path->root_dir.$this->config->route->file,
            $this->config->path->root_url,
            $this->config->path->root_dir
        );

        // Formatage du nom de module et de l'action.
        $this->router->module(preg_replace("#\W#", "_", $this->router->module()));
        $this->router->action(preg_replace("#\W#", "_", $this->router->action()));
    }

    /**
     * Paramétrage du Service Access.
     */
    public function action_config_access()
    {
        if ($this->config->feature->access === FALSE)
        {
            $this->access->disable();
        }
    }

    /**
     * Paramétrage du Service cache.
     */
    public function action_config_cache()
    {
        if ($this->config->feature->cache === FALSE)
        {
            $this->cache->disable();
        }
    }

    /**
     * Paramétrage du Service Css.
     */
    public function action_config_css()
    {
        $this->css->set_cache_time(7200);
        $this->css->enable_minification($this->config->feature->minify_ressource);
    }

    /**
     * Paramétrage du Service Js.
     */
    public function action_config_js()
    {
        $this->js->set_cache_time(7200);
        $this->js->enable_minification($this->config->feature->minify_ressource);
    }

    /**
     * Paramétrage du Service Crypt.
     */
    public function action_config_crypt()
    {
        $this->crypt->key($this->config->security->key_crypt);
        $this->crypt->salts($this->config->security->prefix_salt, $this->config->security->suffix_salt);
    }

    /**
     * Paramétrage du Service Mail.
     */
    public function action_config_mail()
    {
        if ($this->config->mail->enable === FALSE)
        {
            $this->mail->disable();
        }
        $this->mail->sender($this->config->mail->sender_mail, $this->config->mail->sender_name);
    }

    /**
     * Paramétrage du Service Hook.
     */
    public function action_config_hook()
    {
        // Défini l'observable du Dao pour lancer la config de la Database si un appel est lancé.
        $initializer = new DaoInitializer($this->hook);
        Dao::observable()->attach($initializer);
    }

}
?>