<?php
class Controller extends FrontController
{
	public function init()
	{
	    $this->request->file = str_replace('..', '.', $this->request->file);
        $file = $this->config->path->root_dir.$this->config->path->js_cache.$this->request->file;
        if (file_exists($file) == FALSE)
        {
            header('HTTP/1.1 404 Not Found');
        }
        else
        {
            include($file);
        }
	    exit();
	}
}
?>