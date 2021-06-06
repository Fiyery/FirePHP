<?php
namespace FirePHP\Html\Form;

/**
 * FormInputTime génère input type time pour des formulaires.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses FormInput
 */
class FormInputTime extends FormInput
{
	/**
	 * Constructeur.
	 */ 
	public function __construct()
	{
		parent::__construct();
		$this->_attrs['type'] = 'time';
	}
	
	/**
	 * Définie une valeur minimale.
	 * @param numeric $min Valeur minimale.
	 * @return FormInputTime
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
	 * @return FormInputTime
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