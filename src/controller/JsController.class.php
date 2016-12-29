<?php
class Controller extends FrontController
{
	public function init()
	{
	    $this->req->file = str_replace('..', '.', $this->req->file);
        $file = $this->site->get_root(Site::DIR).$this->config->path->js_cache.$this->req->file;
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