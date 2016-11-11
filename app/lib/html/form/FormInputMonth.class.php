<?php
/**
 * FormInputMonth génère input type month pour des formulaires.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses FormInput
 */
class FormInputMonth extends FormInput
{
	/**
	 * Constructeur.
	 */ 
	public function __construct()
	{
		parent::__construct();
		$this->_attrs['type'] = 'month';
	}
	
	/**
	 * Définie une valeur minimale.
	 * @param numeric $min Valeur minimale.
	 * @return FormInputMonth
	 */
	public function min($min=TRUE)
	{
		if (is_numeric($min))
		{
			$this->min = $min;
		}
		return $this;
	}
	
	/**
	 * Définie une valeur maximale.
	 * @param numeric $max Valeur maximale.
	 * @return FormInputMonth
	 */
	public function max($max=TRUE)
	{
		if (is_numeric($max))
		{
			$this->max = $max;
		}
		return $this;
	}
}
?>