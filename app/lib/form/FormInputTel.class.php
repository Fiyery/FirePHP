<?php
/**
 * FormInputTel génère input type tel pour des formulaires.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @copyright 2011-2015 Yoann Chaumin
 * @uses FormInput
 */
class FormInputTel extends FormInput
{
	/**
	 * Constructeur.
	 */ 
	public function __construct()
	{
		parent::__construct();
		$this->_attrs['type'] = 'tel';
	}
	
	/**
	 * Définie si on accepte l'autocomplete ou non.
	 * @param boolean $complete Si TRUE, on accepte.
	 * @return FormInputTel
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
	 * Définie un masque.
	 * @param string $val Regex.
	 * @return FormInputTel
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