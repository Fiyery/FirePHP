<?php
namespace FirePHP\Html\Form;

/**
 * FormInputImage génère input type image pour des formulaires.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses FormInput
 */
class FormInputImage extends FormInput
{
	/**
	 * Constructeur.
	 */ 
	public function __construct()
	{
		parent::__construct();
		$this->_attrs['type'] = 'image';
	}
	
	/**
	 * Définie l'attribut alt.
	 * @param string $alt Valeur de l'attribut alt.
	 * @return FormInputImage
	 */
	public function alt($alt)
	{
		if (is_string($alt))
		{
			$this->alt = $alt;
		}
		return $this;
	}
	
	/**
	 * Définie l'adresse de l'image.
	 * @param string $src Adresse de l'image.
	 * @return FormInputImage
	 */
	public function src($src)
	{
		if (is_string($src))
		{
			$this->src = $src;
		}
		return $this;
	}
}
?>