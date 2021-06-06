<?php
namespace FirePHP\Html\Form;

/**
 * FormInputReset génère input type reset pour des formulaires.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses FormInput
 */
class FormInputReset extends FormInput
{
	/**
	 * Constructeur.
	 */ 
	public function __construct()
	{
		parent::__construct();
		$this->_attrs['type'] = 'reset';
	}
}
?>