<?php
/**
 * Singleton permet de transformer les classes héritantes en singleton. La classe qui hérite de Singleton doit posséder l'attribut "protected static $_instance = NULL".
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 */
abstract class Singleton 
{	
	/**
	 * Constructeur.
	 */ 
	protected function __construct($args=NULL)
	{
	    
	}
	
	/**
	 * Retourne une instance de la classe avec les arguments correctement ordonnés selon le constructeur de la classe.
	 * @param array $args Tableau d'arguments du constructeur.
	 * @return Singleton
	 */
	protected static function __create($args)
	{
		$class = get_called_class();
		return new $class();
	}
	
	/**
	 * Fournie une instance unique de la classe courante.
	 * @return Singleton
	 */
	public static function get_instance()
	{
		$class = get_called_class();
		if (property_exists($class,'_instance') == FALSE)
		{
			return NULL;
		}
		if ($class::$_instance == NULL)
		{
			$class::$_instance = $class::__create(func_get_args());
		}
		return $class::$_instance;
	}
	
	/**
	 * Réinitialise le singleton.
	 */
	public static function reset_instance()
	{
		$class = get_called_class();
		if (property_exists($class,'_instance') == FALSE)
		{
			return NULL;
		}
		$class::$_instance = NULL;
	}
}
?>