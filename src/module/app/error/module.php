<?php
use FirePHP\Controller\Module; 

class ErrorModule extends Module
{
	/**
     * Paramétrage du Service ExceptionManager.
     */
    public function run()
    {
        $this->error->start();
        $this->error->set_file($this->config->path->root_dir.$this->config->path->log.'error.log');
        $this->error->add_data('ip',(isset($_SERVER['REMOTE_ADDR'])) ? ($_SERVER['REMOTE_ADDR']) : ('null'));
        $this->error->add_data('url', $_SERVER['REQUEST_URI']);
        $this->error->active_error();
        $this->error->active_exception();
        if ($this->config->feature->error_log)
        {
            $this->error->active_save();
        }
        if ($this->config->tpl->enable === FALSE || $this->config->feature->error_show == FALSE)
        {
            $this->error->hide();
        }
    }
	
	public function action_404()
    {

    }

    public function action_500()
    {
        if (isset($this->request->error_msg))
        {
            $this->tpl->assign('error_msg', $this->request->error_msg);
        }
    }
}
?>