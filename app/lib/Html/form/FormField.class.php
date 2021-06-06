<?php
namespace FirePHP\Html\Form;

use FirePHP\Html\HTMLElement;

/**
 * FormField est la classe mère de tous les champs d'un formulaire.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses HTMLTag
 */
abstract class FormField extends HTMLElement
{
    /**
     * Valeur du label.
     * @var string
     */
	protected $_label = NULL;
	
	/**
	 * Valeur du champ.
	 * @var string
	 */
	protected $_value = NULL;
	
	/**
	 * Contructeur.
	 * @param string $type Type du champs.
	 * @param boolean $short Si TRUE, la balise ne sera composé que d'un bloc ouvrant et fermant.
	 */
	public function __construct($type, $short=FALSE)
	{
		parent::__construct($type, $short);
	}
	
	/**
	 * Définie un label au champ.
	 * @param string $val Valeur du label.
	 * @return FormField
	 */
	public function label($val)
	{
		if (is_scalar($val))
		{
			$this->_label = $val;
		}
		return $this;
	}
	
	/**
	 * Interdit la modification du champ par readonly.
	 * @return FormField
	 */
	public function readonly()
	{
		$this->readonly = 'readonly';
		return $this;
	}
	
	/**
	 * Interdit la modification du champ par disabled.
	 * @return FormField
	 */
	public function disabled()
	{
		$this->disabled = 'disabled';
		return $this;
	}
	 
	/**
	 * Fonction qui définie le champ comme requis ou non.
	 * @param boolean $bool Si TRUE, le champs est requis.
	 * @return FormField
	 */
	public function required($bool=TRUE)
	{
		if ($bool)
		{
		    $this->required = 'required';
		}
		else
		{
		    unset($this->required);
		}
		return $this;
	}
	
	/**
	 * Définie le formulaire auquel appartient le champ.
	 * @param string $id Identifiant du formulaire.
	 * @return FormField
	 */
	public function form($id)
	{
	    $id = (substr($id,0,1) == '#') ? (substr($id,1)) : ($id);
		$this->form = $id;
		return $this;
	}

	/**
	 * Convertie l'objet en code HTML.
	 * @see HTMLTag::__toString()
	 * @return string
	 */
	public function __toString() 
	{
		$s = '';
		if ($this->_label != NULL)
		{
			$label = new HTMLElement('label');
			$label->content($this->_label);
			$s = $label->__toString();
		}
		$s .= parent::__toString(); 
		return $s;
	}
	
	/**
	 * Vérifie si la valeur du champ est valide
	 * return boolean
	 */
	public function check()
	{
	    if ($this->required != NULL && empty($this->_value))
	    {
            return FALSE;
	    }  
        elseif ($this->pattern != NULL) 
        {
            $pattern = str_replace(array('\u0022','\u0027','&amp;'), array('"','\'','&'), $this->pattern);
            return (preg_match('#'.$pattern.'#', $this->_value));
        }
	    return TRUE;
	}
}
?>