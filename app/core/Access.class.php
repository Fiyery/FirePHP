<?php
/**
 * Access gère les accès des utilisateurs aux pages du site. 
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @copyright 2011-2015 Yoann Chaumin
 * @uses SingletonSession
 */
class Access extends Singleton
{
	/**
	 * Instance de Singleton.
	 * @var Access
	 */
	protected static $_instance = NULL;
	
	/**
	 * Liste des droits d'accès en fonction d'un identifiant.
	 * @var array
	 */
	private $_rules;
	
	/**
	 * Etat courant de l'activité de la classe.
	 * @var boolean
	 */
	private $_enable = TRUE;

	/**
	 * Constructeur.
	 */ 
	protected function __construct()
	{
		$this->_rules = array();	
	}
	
	/**
	 * Ajoute une règle.
	 * @param string $id Identifiant de l'accès.
	 * @param string $module Nom du module ou * pour tous les modules.
	 * @param string $action Nom de l'action ou * pour toutes les actions.
	 */
	public function add_rule($id, $module, $action=NULL)
	{
		$perm = ($action != NULL) ? ($module.'/'.$action) : ($module);
		if (isset($this->_rules[$id]) == FALSE)
		{
			$this->_rules[$id] = array();
		}
		$this->_rules[$id][] = $perm;
	}
	
	/**
	 * Définie les règles.
	 * @param array $list Liste des règles sous format [id] => array("domaine1/module1/action1, ...), ...
	 */
	public function set_rule(array $list)
	{
		$this->_rules = $list;
	}
	
	/**
	 * Supprime toutes les règles.
	 */
	public function reset_rules()
	{
		$this->_rules = array();
	}
	
	/**
	 * Renvoie la liste des règles d'accès.
	 * @return array Liste des règles.
	 */
	public function get_rules()
	{
		return $this->_rules;
	}

	/**
	 * Vérifie si l'utilisateur a les droits d'accès pour le module et l'action courrante.
	 * @param array<int> $id Identifiant du type d'accès.
	 * @param string $module Nom du module courant.
	 * @param string $action Nom de l'action courante.
	 * $param boolean $strict 
	 * 	Si TRUE, la permission avec plusieurs ids renvera TRUE si tous les ids renvoient TRUE. 
	 * 	Si FALSE, la fonction renvera TRUE si au moins un id a la permission
	 * @return boolean
	 */
	public function is_authorized($id, $module, $action=NULL, $strict=TRUE)
	{
		if ($this->is_enabled() == FALSE)
		{
			return TRUE;
		}
		if (is_array($id))
		{
		    $max = count($id);
		    if ($max == 0)
		    {
		        return TRUE;
		    }
		    $return = $strict;
		    foreach ($id as $i)
		    {
		        if (isset($this->_rules[$i]) == FALSE)
		        {
		        	$acces = FALSE;
		        }
		        else 
		        {
		            $rules = $this->_rules[$i];
		            $perms = array();
		            if ($action != NULL)
		            {
		            	$perms[] = $module.'/'.$action;
        		    	$perms[] = $module.'/*';
        		    	$perms[] = '*/*';
		            }
		            else
		            {
		            	$perms[] = $module;
                        $perms[] = '*';
		            }
		            $access = (!!array_intersect($perms, $rules));
		        }
		        $return = ($strict) ? ($return && $access) : ($return || $access);
		    }
		    return $return;
		}
		else 
		{
		    if (isset($this->_rules[$id]) == FALSE)
		    {
		    	return FALSE;
		    }
		    $rules = $this->_rules[$id];
		    $perms = array();
		    if ($action != NULL)
		    {
		    	$perms[] = $module.'/'.$action;
		    	$perms[] = $module.'/*';
		    	$perms[] = '*/*';
		    }
		    else
		    {
		    	$perms[] = $module;
		    	$perms[] = '*';
		    }
            return (!!array_intersect($perms, $rules));
		}
	}
	
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
