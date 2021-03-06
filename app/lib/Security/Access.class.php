<?php
namespace FirePHP\Security;

/**
 * Access gère les accès des utilisateurs aux pages du site. 
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 */
class Access 
{
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
	public function __construct()
	{
		$this->_rules = [];	
	}
	
	/**
	 * Ajoute une règle.
	 * @param string $id Identifiant du groupe d'accès.
	 * @param string $module Nom du module ou * pour tous les modules.
	 * @param string $action Nom de l'action ou * pour toutes les actions.
	 */
	public function add_rule($id, $module, $action=NULL)
	{
		$perm = ($action != NULL) ? ($module.'/'.$action) : ($module);
		if (isset($this->_rules[$id]) == FALSE)
		{
			$this->_rules[$id] = [];
		}
		$this->_rules[$id][] = $perm;
	}
	
	/**
	 * Supprime toutes les règles.
	 */
	public function reset_rules()
	{
		$this->_rules = [];
	}
	
	/**
	 * Renvoie la liste des règles d'accès.
	 * @return array Liste des règles.
	 */
	public function get_rules() : array
	{
		return $this->_rules;
	}

	/**
	 * Vérifie si l'utilisateur a les droits d'accès pour le module et l'action courrante.
	 * @param int[] $id Identifiant du type d'accès.
	 * @param string $module Nom du module courant.
	 * @param string $action Nom de l'action courante.
	 * @param boolean $strict 
	 * 	Si TRUE, la permission avec plusieurs ids renvera TRUE si tous les ids renvoient TRUE. 
	 * 	Si FALSE, la fonction renvera TRUE si au moins un id a la permission
	 * @return boolean
	 */
	public function is_authorized($id, $module, $action=NULL, $strict=TRUE) : bool
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
		        if (isset($this->_rules[$i]) === FALSE)
		        {
		        	$access = FALSE;
		        }
		        else 
		        {
		            $rules = $this->_rules[$i];
		            $perms = [];
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
		    $perms = [];
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
	public function is_enabled() : bool
	{
	    return $this->_enable;
	}
}
?>
