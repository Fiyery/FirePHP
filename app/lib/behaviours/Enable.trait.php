<?php
/**
 * Active ou désactive les fonctionnalités de la classe
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @version 1.0
 * @copyright 2011-2015 Yoann Chaumin
 */
trait Enable
{
	/**
	 * Etat courant de l'activité de la classe.
	 * @var boolean 
	 */
	private $_enable = TRUE;
	
	/**
	 * Active les fonctionnalités de la classe.
	 */
	public function enable() 
	{
		$this->_enable = TRUE;
	}
	
	/**
	 * Désactive les fonctionnalités de la classe.
	 */
	public function disable()
	{
		$this->_enable = FALSE;
	}
	
	/**
	 * Vérifie si la classe est active.
	 * @return boolean
	 */
	public function is_enabled()
	{
		return $this->_enable;
	}
}
?>