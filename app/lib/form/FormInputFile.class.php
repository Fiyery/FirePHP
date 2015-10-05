<?php
/**
 * FormInputFile génère input type file pour des formulaires.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @copyright 2011-2015 Yoann Chaumin
 * @uses FormInput
 */
class FormInputFile extends FormInput
{
	/**
	 * Constructeur.
	 */ 
	public function __construct()
	{
		parent::__construct();
		$this->_attrs['type'] = 'file';
	}
	
	/**
	 * Définie si on active les valeurs multiples du champ ou non.
	 * @param boolean $multiple Si TRUE, on l'active.
	 * @return FormInputFile
	 */
	public function multiple($multiple=TRUE)
	{
		if (is_bool($multiple) && $multiple)
		{
			$this->multiple = NULL;
		}
		return $this;
	}
}
?>