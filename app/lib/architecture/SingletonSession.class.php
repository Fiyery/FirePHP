<?php
namespace FirePHP\Architecture;
/**
 * SingletonSession est une spécialisation de Singleton. Le Singleton est aussi sauvegardé en session.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses Singleton
 */
abstract class SingletonSession extends Singleton
{	
    /**
     * Si TRUE, les instances seront bien sauvegardées en session.
     * @var boolean
     */
	private static $_save = TRUE;
	
	/**
	 * Destructeur.
	 */
	public function __destruct()
	{
	    if (self::$_save)
	    {
	        $class = get_called_class();
	        $_SESSION['__instances'][$class] = serialize($class::$_instance);
	    }
	}
	
	/**
	 * Retourne l'instance unique.
	 * @return SingletonSession
	 */
	public static function get_instance() 
	{
		$class = get_called_class();
		if (property_exists($class,'_instance') == FALSE || isset($_SESSION) == FALSE)
		{
		    return NULL;
		}
		if ($class::$_instance === NULL)
		{
			if (self::$_save)
			{
			    if (isset($_SESSION['__instances']) == FALSE)
			    {
			    	$_SESSION['__instances'] = array();
			    }
			    if (isset($_SESSION['__instances'][$class]))
			    {
			    	$class::$_instance = unserialize($_SESSION['__instances'][$class]);
			    }
			    else
			    {
			    	$class::$_instance = $class::__create(func_get_args());
			    }
			}
			else
			{
			    $class::$_instance = $class::__create(func_get_args());
			}
		}
		return $class::$_instance;
	} 
	
	/**
	 * Réinitialise le singleton.
	 */
	public static function reset_instance()
	{
		$class = get_called_class();
		if (isset($_SESSION) && isset($_SESSION['__instances']) && isset($_SESSION['__instances'][$class]))
		{
		    unset($_SESSION['__instances'][$class]);
		}
		parent::reset_instance();
	}
	
	/**
	 * Active la sauvegarde des instances en session.
	 */
	final public static function enable_save()
	{
	    self::$_save = TRUE;
	}
	
	/**
	 * Désactive la sauvegarde des instances en session.
	 */
	final public static function disable_save()
	{
		self::$_save = FALSE;
	}
}
?>