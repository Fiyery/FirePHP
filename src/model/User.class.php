<?php

namespace FirePHP\Model;

use FirePHP\Database\Dao; 
class User extends Dao
{
	protected $nom;
	
	protected $pass;
	
	/**
	 * @param string $_nom
	 * @return user
	 */
	public function set_nom($nom)
	{
		$this->nom = $nom;
		return true;
	}
	
	/**
	 * @return string
	 */
	public function get_nom()
	{
		return $this->nom;
	}
}
?>