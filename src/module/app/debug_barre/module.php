<?php
class DebugBarreModule extends Module
{
    /**
     * Récupère les informations et initialise la barre de débug.
     * @return bool
     */
    public function run()
    {
        if ($this->config->feature->debug === FALSE)
        {
            return TRUE;
        }
        // Affichage des erreurs.
        $errors = array_merge($this->error->get_all_exceptions(), $this->error->get_all_errors());
        $tpl = new Template($this->config->path->tpl_cache);
        $tpl->assign('errors', $errors);
        
        // Affichages parasites.
        $echos = ob_get_contents();
        $tpl->assign('echos', $echos);

        // Requête SQL.
        $sql = $this->base->history();
        $queries = [];
        foreach ($sql as $i => $s)
        {
            $queries[] = [
                'num'   => $i,
                'time'  => ($s['time'] > -1) ? (number_format($s['time']*1000)) : (-1),
                'sql'   => $s['sql']
            ];
        }
        $tpl->assign('queries', $queries);

        // Variable de session.
        $session = [];
        foreach ($_SESSION as $n => $v)
        {
            if ($n !== '__vars')
            {
                $session[] = [
                    'name'  => $n,
                    'type'  => gettype($v),
                    'value' => (is_scalar($v)) ? ($v) : (str_replace([','], '<br/>', json_encode($v)))
                ];
            }
        }
        if (isset($_SESSION['__vars']))
        {
            foreach ($_SESSION['__vars'] as $n => $v)
            {
                $session[] = [
                    'name'  => $n,
                    'type'  => gettype($v),
                    'value' => (is_scalar($v)) ? ($v) : (str_replace([','], '<br/>', json_encode($v)))
                ];
            }
        }
        $tpl->assign('session', $session);

        // Variable de GET.
        $get = [];
        foreach ($_GET as $n => $v)
        {
            $get[] = [
                'name'  => $n,
                'type'  => gettype($v),
                'value' => (is_scalar($v)) ? ($v) : (str_replace([','], '<br/>', json_encode($v)))
            ];
        }
        $tpl->assign('get', $get);
        
        // Variable de POST.
        $post = [];
        foreach ($_POST as $n => $v)
        {
            $post[] = [
                'name'  => $n,
                'type'  => gettype($v),
                'value' => (is_scalar($v)) ? ($v) : (str_replace([','], '<br/>', json_encode($v)))
            ];
        }
        $tpl->assign('post', $post);

        // Mémoire utilisée.
        $vars = [];
        $memory_limit = ini_get('memory_limit');
        if (is_numeric($memory_limit))
        {
            $memory_limit = File::format_size($memory_limit);
        }
        else
        {
            switch (substr($memory_limit, -1))
            {
                case 'G' : 
                {
                    $memory_limit = File::format_size(substr($memory_limit, 0, -1)*1024*1024*1024); 
                    break;
                }   
                case 'M' : 
                {
                    $memory_limit = File::format_size(substr($memory_limit, 0, -1)*1024*1024); 
                    break;
                }   
                case 'K' : 
                {
                    $memory_limit = File::format_size(substr($memory_limit, 0, -1)*1024);
                    break;
                }
            }
        }
        $vars['memory_limit'] = $memory_limit;

        // Paramètre du serveur.
        $apache_version = $_SERVER["SERVER_SOFTWARE"];
        $pos = strpos($apache_version, '/');
        $length = strpos($apache_version, ' ') - $pos;
        $apache_version = substr($apache_version, $pos+1, $length);
        $console_image = 'console';
        if (count($errors) > 0)
        {
            $console_image = 'console_error';
        }
        elseif ($echos !== '')
        {
            $console_image = 'console_warning';
        }
        $vars['css'] = file_get_contents(__DIR__.'/res/css/default.css');
        $vars['time'] = number_format((microtime(TRUE) - ($_SERVER['REQUEST_TIME_FLOAT'])) * 1000); 
        $vars['query_count'] = $this->base->count();
        $vars['query_time'] = str_replace(',', ' ', number_format($this->base->time()*pow(10,3)));
        $vars['memory_limit'] = $memory_limit;    
        $vars['memory_usage'] = File::format_size(memory_get_peak_usage());    
        $vars['php_version'] = phpversion();
        $vars['apache_version'] = $apache_version;
        $vars['base_version'] = $this->base->engine().' '.$this->base->version();
        $vars['ip_server'] = $_SERVER['SERVER_ADDR'];
        $vars['name_server'] = $_SERVER['SERVER_NAME'];    
        $vars['cache_active'] = ($this->config->feature->cache) ? ('On') : ('Off');
        $vars['app_controller'] = $this->router->get_controller();
        $vars['app_module'] = $this->router->get_module();
        $vars['app_action'] = $this->router->get_action();

        // Images
        $vars['time_image'] = 'data:image/png;base64,'.base64_encode(file_get_contents(__DIR__.'/res/img/time.png'));
        $vars['base_image'] = 'data:image/png;base64,'.base64_encode(file_get_contents(__DIR__.'/res/img/base.png'));
        $vars['memory_image'] = 'data:image/png;base64,'.base64_encode(file_get_contents(__DIR__.'/res/img/memory.png'));
        $vars['server_image'] = 'data:image/png;base64,'.base64_encode(file_get_contents(__DIR__.'/res/img/server.png'));
        $vars['console_image'] = 'data:image/png;base64,'.base64_encode(file_get_contents(__DIR__.'/res/img/'.$console_image.'.png'));
        $vars['var_image'] = 'data:image/png;base64,'.base64_encode(file_get_contents(__DIR__.'/res/img/var.png'));
        $tpl->assign($vars);

        $this->tpl->assign('debug_barre', $tpl->fetch(__DIR__.'/view/default.tpl'));
    }
}
?>