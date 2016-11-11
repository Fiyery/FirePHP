<?php
/**
 * Key génère des clés aléatoires et unique.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 */
class Key 
{
	/**
	 * Constructeur.
	 */
	public function __construct()
	{
		
	}
	
	/**
	 * Récupère une clé unique basé sur le temps.
	 * @param string $prefix Prefixe ajouté à la clé.
	 * @return string
	 */
	public function get_unique($prefix=NULL)
	{
		return uniqid($prefix);
	}
	
	/**
	 * Récupère une clé aléatoire.
	 * @param int $number Nombre de caractères souhaités.
	 * @return string
	 */
	public function get_ramdom($number)
	{
		$max = $number/40;
		$key = '';
		$seed = md5($number);
		for($i=0; $i <= $max; $i++)
		{
			$key .= sha1(microtime(TRUE).mt_rand());
		}	
		return substr($key, 0, $number);
	}
}
?>