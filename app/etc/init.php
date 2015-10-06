<?php
/**
 * Initialite le système.
 * @return Controller
 */
function init_core() 
{
    // Entête de la requête de retour.
    header('Content-type:text/html; charset=utf-8');
    
    require(__DIR__.'/../core/ClassLoader.class.php');
    $loader = new ClassLoader();
    $loader->set_ext('class.php');
    $loader->add_dir(__DIR__.'/../core/');
    $loader->enable();
    
    // Initialisation du service container.
    $services = ServiceContainer::get_instance();
    
    // Définition des paramètres et de la configuration.
    $services->set('config', function(){
        return Config::get_instance(__DIR__.'/../var/config.json');
    });
    $services->load_alias($services->get('config')->path->root_dir.$services->get('config')->system->service_alias);
    
    require(__DIR__.'/config.php');
   
    foreach ($services->get('config')->loader as $dir)
    {
        $loader->add_dir($services->get('config')->path->root_dir.$dir);
    }    
    
    // Fuseau horaire français.
    date_default_timezone_set($services->get('config')->system->timezone);
    
    // Initialisation de la session.
    $services->set('session', function(){
        return Session::get_instance();
    });
    
    // Paramètres de connexion à la base.
    $services->set('base', function() use ($services) {
        $service = Base::get_instance();
        $service->add_base(
            $services->get('config')->db->host, 
            $services->get('config')->db->name, 
            $services->get('config')->db->user, 
            $services->get('config')->db->pass, 
            $services->get('config')->db->charset
        );
        if ($services->get('config')->feature->cache)
        {
            $service->enable_cache($services->get('config')->path->sql_cache);
        }
        else 
        {
            $service->disable_cache();
        }
        return $service;
    });

    // Moteur de template.
    $services->set('template', function() use ($services) {
        $service = Template::get_instance($services->get('config')->path->tpl_cache);
        $service->set_syntaxe(Template::SMARTY_STRICT);
        if ($services->get('config')->feature->tpl_save)
        {
            $service->save_tpl();
        }
        return $service;
    });
    
    // Réception des données.
    $services->set('request', function() use ($services) {
        $service = Request::get_instance();
        if ($services->get('config')->feature->secure_html_post)
        {
            $service->check_html();
        }
        if ($services->get('config')->feature->multi_request == FALSE)
        {
            $service->check_multipost();
        }
        return $service;
    });
    
    // Gestion du site.
    $services->set('site', function() use ($services) {
        $service = Site::get_instance($services->get('session'), $services->get('tpl'), $services->get('request'));
        $config = $services->get('config');
        $service->set_default_module($config->system->default_module);
        $service->set_default_action($config->system->default_action);
        $service->set_tpl_name_title($config->tpl->title_site);
        $service->set_tpl_name_description($config->tpl->description_site);
        return $service;
    });
    
    // Initialisation du routage.
    $services->set('route', function() use ($services) {
        $config = $services->get('config');
        $service = Route::get_instance($services->get('request'), $config->path->root_url, $config->path->root_dir);
        $service->init($config->path->root_dir.$config->route->file);
        return $service;
    });
    
    // Gestion des Erreurs.
    $services->set('error', function() use ($services) {
        $service = ErrorManager::get_instance();
        $service->start();
        $service->set_file($services->get('config')->path->root_dir.$services->get('config')->path->var.'error.log');
        $service->add_data('ip',(isset($_SERVER['REMOTE_ADDR'])) ? ($_SERVER['REMOTE_ADDR']) : ('null'));
        $service->add_data('module',$services->get('route')->get_controller().'/'.$services->get('route')->get_module());
        $service->add_data('action',$services->get('route')->get_action());
        $service->active_error();
        $service->active_exception();
        if ($services->get('config')->feature->error_log)
        {
            $service->active_save();
        }
        if ($services->get('config')->feature->error_show == false)
        {
            $service->hide();
        }
        return $service;
    });
    
    // Gestion des singletons de session.
    if ($services->get('config')->feature->session_instance == FALSE)
    {
    	SingletonSession::disable_save();
    }
    
    // Droit d'accès.
    $services->set('access', function() use ($services) {
        $service = Access::get_instance();
        if ($services->get('config')->feature->access)
        {
            $service->enable();
        }
        else
        {
            $service->disable();
        }
        return $service;
    });
    
    // Gestion du cache.
    $services->set('cache', function() use ($services) {
        $service = Cache::get_instance($services->get('config')->path->cache);
        if ($services->get('config')->feature->cache)
        {
            $service->enable();
        }
        else
        {
            $service->disable();
        }
        return $service;
    });
    
    // Gestion du CSS.
    $services->set('css', function() use ($services) {
        $service = Css::get_instance($services->get('config')->path->css_cache);
        $service->set_cache_time(7200);
        return $service;
    });
    
    // Gestion du JavaScript.
    $services->set('javascript', function() use ($services) {
        $service = Javascript::get_instance($services->get('config')->path->js_cache);
        $service->set_cache_time(7200);
        return $service;
    });
    
    // Définition de la liaison de la base de données à la classe Model.
    Dao::set_base($services->get('base'));
    
    // Définition de la classe par défaut des formulaires.
    Form::set_default_class($services->get('config')->system->css_class_form);
  
    // Définition de la clé de cryptage par défaut.
    $services->set('cryp', function() use ($services) {
        Crypt::set_default_key($services->get('config')->security->key_crypt);
        Crypt::set_salts($services->get('config')->security->prefix_salt, $services->get('config')->security->suffix_salt);
        $service = new Crypt();
        return $service;
    });
    
    // Définition de la base de données des types mimes et des extensions de fichiers.
    $services->set('upload', function() use ($services) {
        return Upload::get_instance($services->get('config')->upload->mime_types_file);
    });
    
    // Gestion des informations sur le navigateur du client.
    $services->set('browser', function() use ($services) {
        $service = Browser::get_instance($services->get('config')->browser->file);
        return $service;
    });
    
    // Chargement du Moteur du site.
    $filename = $services->get('config')->path->root_dir.$services->get('config')->path->controller.$services->get('route')->get_controller().'.class.php';
    if (file_exists($filename) == FALSE)
    {
        throw new Error('Controller not find !', __FILE__, __LINE__);
    }
    require($filename);
    
    $controller = new Controller($services);
    $controller->init();  
    return $controller;
}
?>