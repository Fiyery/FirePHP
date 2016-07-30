<?php
function debug_tool_exec($controller, $echx)
{   
    // Empêche la double exécution de la fonction.
    if (defined('DEBUG_TOOL_EXEC'))
    {
        return FALSE;
    }
    define('DEBUG_TOOL_EXEC', 1);
    
    // Affichage des erreurs résiduelles.
    $error = "<div class='debug_tool_echo'>Affichage erreur :<br/>";
    $list = array_merge($controller->error->get_all_exceptions(), $controller->error->get_all_errors());
    $error_exists = (count($list) > 0);
    foreach($list as $e)
    {
    	if ($e['type'] == 'Exception')
    	{
    		$error .= '<div style="color:red"><b>' . $e['file'] . '</b>:' . $e['line'] . ' ' . $e['string'] . '</div>';
    	}
    	else
    	{
    		$error .= '<div><b>' . $e['file'] . '</b>:' . $e['line'] . ' ' . $e['string'] . '</div>';
    	}
    }
    $error .= "</div>";
    
    // Affichages parasites.
    $echo = "<div class='debug_tool_echo'>Affichage echo / print :<br/>";
    $echo_exists = ($echx != NULL);
    $echo .= $echx;
    $echo .= "</div>";
    
    // Dossier du plugin.
    $dir = str_replace('\\', '/', __DIR__).'/';
    
    // Paramètres.
    $memory_limit = ini_get('memory_limit');
    if (is_numeric($memory_limit))
    {
    	$memory_limit = File::format_size($memory_limit);
    }
    elseif (substr($memory_limit, -1) == 'G')
    {
    	$memory_limit = File::format_size(substr($memory_limit, 0, -1)*1024*1024*1024);
    }
    elseif (substr($memory_limit, -1) == 'M')
    {
    	$memory_limit = File::format_size(substr($memory_limit, 0, -1)*1024*1024);
    }
    elseif (substr($memory_limit, -1) == 'K')
    {
    	$memory_limit = File::format_size(substr($memory_limit, 0, -1)*1024);
    }
    /*
    $apache_version = apache_get_version();
    $apache_version = substr($apache_version, strpos($apache_version, '/')+1); 
    $apache_version = substr($apache_version, 0, strpos($apache_version, ' ')); 
    */
    $apache_version = $_SERVER["SERVER_SOFTWARE"];
    $pos = strpos($apache_version, '/');
    $length = strpos($apache_version, ' ') - $pos;
    $apache_version = substr($apache_version, $pos+1, $length);
    $console_image = 'console';
    if ($error_exists)
    {
        $console_image = 'console_error';
    }
    elseif ($echo_exists)
    {
        $console_image = 'console_warning';
    }
    $sql = $controller->base->history();
    $html_sql = "<table class='table_debug'><thead><tr><th>N°</th><th>Temps (ms)</th><th>SQL</th></tr></thead><tbody>";
    foreach ($sql as $i => $s)
    {
        $html_sql .= '<tr><td>'.$i.'</td><td>';
        $time = ($s['time'] > -1) ? (number_format($s['time']*1000)) : (-1);
        $html_sql .= $time.'</td><td>'.$s['sql'].'</td></tr>';
    }
    $html_sql .= '</tbody></table>';  
    $vars = array();
    $vars['css'] = file_get_contents($dir.'debug_tool.css');
    $vars['echo'] = $echo;
    $vars['error'] = $error;
    $vars['time'] = number_format((microtime(TRUE) - ($_SERVER['REQUEST_TIME_FLOAT'])) * 1000); 
    $vars['query_count'] = $controller->base->count();
    $vars['query_time'] = str_replace(',', ' ', number_format($controller->base->time()*pow(10,3)));
    $vars['history_sql'] = $html_sql;    
    $vars['memory_limit'] = $memory_limit;    
    $vars['memory_usage'] = File::format_size(memory_get_peak_usage());    
    $vars['php_version'] = phpversion();
    $vars['apache_version'] = $apache_version;
    $vars['base_version'] = $controller->base->engine().' '.$controller->base->version();
    $vars['ip_server'] = $_SERVER['SERVER_ADDR'];
    $vars['name_server'] = $_SERVER['SERVER_NAME'];    
    $vars['cache_active'] = ($controller->config->feature->cache) ? ('On') : ('Off');
    $html_head = "<table class='table_debug'><thead><tr><th>Nom</th><th>Valeur</th><th>Type</th></tr></thead><tbody>";
    $content = '';
    foreach ($_SESSION as $n => $v)
    {
        if ($n !== '__vars')
        {
            $t = gettype($v);
            $v = (is_scalar($v)) ? ($v) : (str_replace([','], '<br/>', json_encode($v)));
            $content .= '<tr><td>'.$n.'</td><td>'.$v.'</td><td>'.$t.'</td></tr>';
        }
    }
    foreach ($_SESSION['__vars'] as $n => $v)
    {
        $t = gettype($v);
        $v = (is_scalar($v)) ? ($v) : (str_replace([','], '<br/>', json_encode($v)));
        $content .= '<tr><td>'.$n.'</td><td>'.$v.'</td><td>'.$t.'</td></tr>';
    }
    $vars['session_vars'] = $html_head.$content.'</tbody></table>'; 
    $content = '';
    foreach ($_POST as $n => $v)
    {
        $t = gettype($v);
        $v = (is_scalar($v)) ? ($v) : (str_replace([','], '<br/>', json_encode($v)));
        $content .= '<tr><td>'.$n.'</td><td>'.$v.'</td><td>'.$t.'</td></tr>';
    }
    $vars['post_vars'] = $html_head.$content.'</tbody></table>'; 
    $content = '';
    foreach ($_GET as $n => $v)
    {
        $t = gettype($v);
        $v = (is_scalar($v)) ? ($v) : (str_replace([','], '<br/>', json_encode($v)));
        $content .= '<tr><td>'.$n.'</td><td>'.$v.'</td><td>'.$t.'</td></tr>';
    }
    $vars['get_vars'] = $html_head.$content.'</tbody></table>'; 
    
    // Images.
    $vars['time_image'] = 'data:image/png;base64,'.base64_encode(file_get_contents($dir.'img/time.png'));
    $vars['base_image'] = 'data:image/png;base64,'.base64_encode(file_get_contents($dir.'img/base.png'));
    $vars['memory_image'] = 'data:image/png;base64,'.base64_encode(file_get_contents($dir.'img/memory.png'));
    $vars['server_image'] = 'data:image/png;base64,'.base64_encode(file_get_contents($dir.'img/server.png'));
    $vars['console_image'] = 'data:image/png;base64,'.base64_encode(file_get_contents($dir.'img/'.$console_image.'.png'));
    $vars['var_image'] = 'data:image/png;base64,'.base64_encode(file_get_contents($dir.'img/var.png'));
    
    
    $html = file_get_contents($dir.'debug_tool.tpl');
    foreach ($vars as $name => $value)
    {
    	$html = str_replace('{$'.$name.'}', $value, $html);
    }
    echo $html;
    return TRUE;
}

?>