<?php
/**
 * FormInputDateTime génère input type datetime pour des formulaires.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses FormInput
 */
class FormInputDateTime extends FormInput
{
	/**
	 * Constructeur.
	 */ 
	public function __construct()
	{
		parent::__construct();
		$this->_attrs['type'] = 'datetime';
	}
	
	/**
	 * Définie une valeur minimale.
	 * @param numeric $min Valeur minimale.
	 * @return FormInputDateTime
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
	 * @return FormInputDateTime
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