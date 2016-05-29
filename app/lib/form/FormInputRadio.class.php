<?php
/**
 * FormInputRadio génère input type radio pour des formulaires.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses FormInput
 */
class FormInputRadio extends FormInput
{
	/**
	 * Constructeur.
	 */ 
	public function __construct()
	{
		parent::__construct();
		$this->_attrs['type'] = 'radio';
	}
	
	/**
	 * Définie si on le champ est coché ou non.
	 * @param boolean $checked Si TRUE, il est coché.
	 * @return FormInputRadio
	 */
	public function checked($checked=TRUE)
	{
		if (is_bool($checked) && $checked)
		{
			$this->checked = NULL;
			$this->_value = TRUE;
		}
		else 
		{
		    unset($this->checked);
		    $this->_value = FALSE;
		}
		return $this;
	}
}
?>