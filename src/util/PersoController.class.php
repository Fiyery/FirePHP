<?php
/**
 * PersoController est la spécialisation générale au site de FrontController.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @copyright 2011-2015 Yoann Chaumin
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
        	$this->site->set_title($this->config->site->title);
        	$this->site->set_description($this->config->site->description);
        	$this->tpl->assign($this->config->tpl->language, $this->config->site->author);
        	$this->tpl->assign($this->config->tpl->author_site, $this->config->site->author);
        	$this->tpl->assign($this->config->tpl->keyword_site, $this->config->site->keywords);
        	$this->tpl->assign($this->config->tpl->root, $this->config->path->root_url);
        	$this->tpl->assign($this->config->tpl->root_image, $this->config->path->root_dir.$this->config->path->image);
        }
    }
}
?>