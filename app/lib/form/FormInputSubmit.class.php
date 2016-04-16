<?php
/**
 * FormInputSubmit génère input type submit pour des formulaires.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses FormInput
 */
class FormInputSubmit extends FormInput
{
	/**
	 * Constructeur.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->_attrs['type'] = 'submit';
	}
}
?>