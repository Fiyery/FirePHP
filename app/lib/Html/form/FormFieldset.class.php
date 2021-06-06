<?php
namespace FirePHP\Html\Form;

use FirePHP\Html\HTMLElement;

/**
 * FormFieldset génère un fieldset pour des formulaires.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses HTMLTag
 */
class FormFieldset extends HTMLElement
{
    /**
     * Nom du legend.
     * @var string
     */
    protected $_legend;
    
    /**
     * Constructeur
     */
    public function __construct()
    {
    	parent::__construct('fieldset', FALSE);
    }

    /**
	 * Définie une legende au fieldset.
	 * @param string $name Valeur du fieldset
	 * @return FormField
	 */
	public function legend($name)
	{
		if (is_scalar($name))
		{
			$this->_legend = $name;
		}
		return $this;
	}
	
	/**
	 * Convertie l'objet en code HTML.
	 * @return string
	 */
	public function __toString()
	{
	    $s = '<'.$this->_name;
	    foreach($this->_attrs as $n => $v)
	    {
	    	$s .= (empty($v) == FALSE) ? (' '.$n.'="'.$v.'"') : (' '.$n);
	    }
    	$s .= '>';
    	if (empty($this->_legend) == FALSE)
    	{
    		$legend = new HTMLElement('legend');
    		$legend->content($this->_legend);
    		$s .= $legend->__toString();
    	}
    	foreach($this->_content as $b)
    	{
    		$s .= $b;
    	}
    	$s .= '</'.$this->_name.'>';
	    return $s;
	}
}
?>