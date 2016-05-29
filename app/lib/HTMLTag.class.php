<?php
/**
 * HTMLTag est la classe représentation HTML.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 */
class HTMLTag 
{
    /**
     * Liste des attributs.
     * @var array<string>
     */
	protected $_attrs;
	
	/**
	 * Booléen qui définie si la balise est deux parties ou non.
	 * @var boolean
	 */
	protected $_short;
	
	/**
	 * Contenu de la balise.
	 * @var array<string>
	 */
	protected $_content;
	
	/**
	 * Nom de la balise.
	 * @var string
	 */
	protected $_name;
	
	/**
	 * Constructeur.
	 * @param string $name Nom de la balise
	 * @param boolean $short Si TRUE, la balise sera en un seul bloc au lieu de deux (balise ouvrante et fermante).
	 */
	public function __construct($name,$short=FALSE)
	{
		$this->_name = strtolower($name);
		$this->_short = (is_bool($short)) ? ($short) : (FALSE);
		$this->_content = array();
		$this->_attrs = array();
	}
		
	/**
	 * Retourne tous les attributs.
	 * @return array<string>
	 */
	public function get_attrs()
	{
		return $this->_attrs;
	}
	
	/**
	 * Définie ou retourne un attribut.
	 * @param string $name Nom de l'attribut.
	 * @param string $value Valeur de l'attribut.
	 * @return HTMLTag
	 */
	public function attr($name, $value)
	{
		if (is_scalar($value) || is_null($value))
		{
			$this->_attrs[$name] = $value;
			return $this;
		}		
	}
	
	/**
	 * Supprime un attribut.
	 * @param string $name Nom de l'attribut à supprimer.
	 * @return boolean
	 */
	public function remove_attr($name)
	{
	    if (isset($this->_attrs[$name]))
	    {
	        unset($this->_attrs[$name]);
	        return TRUE;
	    }
	    else
	    {
	        return FALSE;
	    }
	}
	
	/**
	 * Définie le contenu de la balise.
	 * @param array<string>|string $content Contenu de la balise.
	 * @return HTMLTag|string Retourne l'objet en cas d'affection ou le contenu de l'objet si aucun paramètre n'est passé.
	 */
	public function content($content=NULL)
	{
		if ($content != NULL)
		{
			$this->_content = (is_array($content)) ? ($content) : (array($content));
			return $this;
		}
		else
		{
			return $this->_content;	
		}
	}
	
	/**
	 * Ajoute du contenu de la balise.
	 * @param array<string>|string $content Contenu de la balise.
	 * @return HTMLTag La balise.
	 */
	public function add_content($content)
	{
		$this->_content = (is_array($content)) ? (array_merge($this->_content, $content)) : (array_merge($this->_content, array($content)));
		return $this;
	}
	
	/**
	 * Définie ou retourne la classe de la balise.
	 * @param string $class Nom de la ou les classe.
	 * @return HTMLTag|string Retourne l'objet en cas d'affection ou les classes de l'objet si aucun paramètre n'est passé.
	 */
	public function classe($class=NULL)
	{
		if (is_string($class))
		{
			if (isset($this->_attrs['class']) == FALSE)
			{
			    $this->_attrs['class'] = '';
			}
		    $this->_attrs['class'] = $this->_attrs['class'].' '.$class; 
			return $this;
		}
		else
		{
			return $this->_attrs['class'];	
		}
	}
	
	/**
	 * Ajoute une classe.
	 * @param string $class Nom de la classe.
	 * @return HTMLTag La balise.
	 */
	public function add_classe($class)
	{
		$this->_attrs['class'] = (is_string($class)) ? ($this->_attrs['class'].' '.$class) : ($this->_attrs['class']); 
		return $this;
	}
	
	/**
	 * Définie ou retourne l'identifiant de la balise.
	 * @param string $id Identifiant de la balise.
	 * @return HTMLTag|string Retourne l'objet en cas d'affection ou l'identifiant de l'objet si aucun paramètre n'est passé.
	 */
	public function id($id=NULL)
	{
		if (is_string($id))
		{
			$this->_attrs['id'] = $id; 
			return $this;
		}
		else
		{
			return $this->_attrs['id'];	
		}
	}
	
	/**
	 * Retourne la valeur d'un attribut.
	 * @param string $name Nom de l'attribut.
	 * @return string Valeur de l'attribut.
	 */
	public function __get($name)
	{
        if (isset($this->_attrs[$name]))
	    {
	        return $this->_attrs[$name];
	    }
	    else
	    {
	        return NULL;
	    }
	}
	
	/**
	 * Définie un attribut de la balise.
	 * @param string $name Nom de l'attribut.
	 * @param string $value Valeur de l'attribut.
	 */
	public function __set($name, $value)
	{
	    $this->attr($name,$value);
	}
	
	/**
	 * Vérifie l'existence d'un attribut.
	 * @param string $name Nom de l'attribut.
	 * @return boolean
	 */
	public function __isset($name)
	{
	    return (isset($this->_attrs[$name]));
	}
	
	/**
	 * Supprime un attribut.
	 * @param string $name Nom de l'attribut.
	 */
	public function __unset($name)
	{
		if (isset($this->_attrs[$name]))
		{
		    unset($this->_attrs[$name]);
		}
	}
	
	/**
	 * Définie la valeur d'un attribut et renvoie l'objet.
	 * @param string $name Nom de l'attribut
	 * @param mixed $value Valeur de l'attribut.
	 * @return HTMLTag
	 */
	public function __call($name, $value)
	{
	    $value = (isset($value[0]) && is_scalar($value[0])) ? ($value[0]) : (NULL); 
	    $this->_attrs[$name] = $value;
	    return $this;
	}
	
	/**
	 * Retourne le code HTML de la balise.
	 * @return string
	 */
	public function __toString()
	{
		$s = '<'.$this->_name;
		foreach($this->_attrs as $n => $v)
		{
			$s .= ($v !== NULL && $v !== FALSE) ? (' '.$n.'="'.$v.'"') : (' '.$n);
		}
		if ($this->_short)
		{
			$s .= '/>';
		}
		else
		{
			$s .= '>';
			foreach($this->_content as $b)
			{
				$s .= $b;
			}
			$s .= '</'.$this->_name.'>';
		}
		return $s;
	}
	
	
}
?>