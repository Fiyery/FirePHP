<?php
/**
 * FormDatalist génère les datalist pour des formulaires.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @copyright 2011-2015 Yoann Chaumin
 * @uses HTMLTag
 */
class FormDatalist extends HTMLTag
{
    /**
     * Constructeur
     */
    public function __construct()
    {
    	parent::__construct('datalist', FALSE);
    }
    
    /**
     * Ajoute une nouvelle option au datalist.
     * @param array<string>|string $value Valeur de l'option ou liste de valeur.
     * @param string $label Valeur affichée dans la datalist.
     * @return FormSelect
     */
    public function add($value, $label=NULL)
    {
    	if (is_scalar($value))
    	{

    	    $op = new HTMLTag('option');
    		if (is_null($label))
    		{
    			$label = $value;
    			$op->content($label);
    		}
    		$op->value = $value;
    		$this->_content[] = $op;
    	}
    	elseif (is_array($value))
    	{
    	    foreach ($value as $v)
    	    {
    	        if (is_scalar($v))
    	        {
    	            $op = new HTMLTag('option');
    	            $op->value = $v;
    	            $this->_content[] = $op;
    	        }
    	    }
    	}
    	return $this;
    }
}

?>