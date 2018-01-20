<?php
/**
 * PersoController est la spécialisation générale au site de FrontController.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses FrontController
 */ 
class PersoController extends FrontController
{	
	/**
     * Traitement particulier à faire avant l'exécution des modules.
     */
    protected function before_execute()
    {
    	
    }
    
    /**
     * Traitement particulier à faire après l'exécution des modules.
     */
    protected function after_execute()
    {
        // Variables générales aux templates.
        if ($this->config->tpl->enable)
        {
        	$this->tpl->assign($this->config->tpl->site_title, ($this->response->title() !== "") ? ($this->response->title()) : ($this->config->site->title));
        	$this->tpl->assign($this->config->tpl->site_description, $this->config->site->description);
        	$this->tpl->assign($this->config->tpl->site_language, $this->config->site->language);
        	$this->tpl->assign($this->config->tpl->site_author, $this->config->site->author);
        	$this->tpl->assign($this->config->tpl->site_keyword, $this->config->site->keywords);
        	$this->tpl->assign($this->config->tpl->root, $this->config->path->root_url);
        	$this->tpl->assign($this->config->tpl->root_image, $this->config->path->root_url.$this->config->path->image);
            $this->tpl->assign($this->config->tpl->module_script, $this->response->script_content());
            $this->tpl->assign($this->config->tpl->module_style, $this->response->script_content());
        }
    }
}
?>