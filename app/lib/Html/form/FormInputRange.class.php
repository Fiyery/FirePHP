<?php
namespace FirePHP\Html\Form;

/**
 * FormInputRange génère input type range pour des formulaires.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses FormInput
 */
class FormInputRange extends FormInput
{
	/**
	 * Constructeur.
	 */ 
	public function __construct()
	{
		parent::__construct();
		$this->_attrs['type'] = 'range';
	}
	
	/**
	 * Définie si on accepte l'autocomplete ou non.
	 * @param boolean $complete Si TRUE, on accepte.
	 * @return FormInputRange
	 */
	public function autocomplete($complete=TRUE)
	{
		if (is_bool($complete))
		{
			$this->autocomplete = ($complete) ? ('on') : ('off');
		}
		return $this;
	}
	
	/**
	 * Définie une valeur minimale.
	 * @param numeric $min Valeur minimale.
	 * @return FormInputRange
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
	 * @return FormInputRange
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