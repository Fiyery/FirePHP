<?php
namespace FirePHP\Html\Form;

/**
 * FormInputEmail génère input type email pour des formulaires.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses FormInput
 */
class FormInputEmail extends FormInput
{
	/**
	 * Constructeur.
	 */ 
	public function __construct()
	{
		parent::__construct();
		$this->_attrs['type'] = 'email';
	}
	
	/**
	 * Définie si on accepte l'autocomplete ou non.
	 * @param boolean $complete Si TRUE, on accepte.
	 * @return FormInputEmail
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
	 * Définie si on active les valeurs multiples du champ ou non.
	 * @param boolean $multiple Si TRUE, on l'active.
	 * @return FormInputEmail
	 */
	public function multiple($multiple=TRUE)
	{
		if (is_bool($multiple) && $multiple)
		{
			$this->multiple = NULL;
		}
		return $this;
	}
	
	/**
	 * Définie un masque.
	 * @param string $val Regex.
	 * @return FormInputEmail
	 */
	public function pattern($val)
	{
		$a = substr($val,0,1);
		$b = substr($val,-1);
		if ($a == $b && ($a == '/' || $a == '#'))
		{
			$val = substr($val,1,-1);
		}
		$val = str_replace(array('"','\'','&'),array('\u0022','\u0027','&amp;'),$val);
		$this->pattern = $val;
		return $this;
	}
}
?>