<?php
/**
 * FormInputColor génère input type color pour des formulaires.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses FormInput
 */
class FormInputColor extends FormInput
{
	/**
	 * Constructeur.
	 */ 
	public function __construct()
	{
		parent::__construct();
		$this->_attrs['type'] = 'color';
	}
	
	/**
	 * Définie si on accepte l'autocomplete ou non.
	 * @param boolean $complete Si TRUE, on accepte.
	 * @return FormInputColor
	 */
	public function autocomplete($complete=TRUE)
	{
		if (is_bool($complete))
		{
			$this->autocomplete = ($complete) ? ('on') : ('off');
		}
		return $this;
	}
}
?>