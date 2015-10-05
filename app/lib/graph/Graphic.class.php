<?php
/**
 * Graphic est la classe mère des graphiques.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @copyright 2011-2015 Yoann Chaumin
 */
abstract class Graphic 
{
	/**
	 * Liste de couleurs.
	 * @var array<string>
	 */
    protected $_colors;
    
    /**
     * Taille en pixel.
     * @var array<int>
     */
    protected $_size;
    
    /**
     * Liste des valeurs.
     * @var array<int>
     */
    protected $_values;
    
    /**
     * Liste des titres.
     * @var array<string>
     */
    protected $_titles;
    
    /**
     * Opacité en pourcentage des représentation des valeurs.
     * @var int
     */
    protected $_opacity;
    
    /**
     * Si TRUE, la gradutation est affichée.
     * @var boolean
     */
    protected $_gradation;
    
    /**
     * Unité des valeurs.
     * @var string
     */
    protected $_unit = NULL;
    
    /**
     * Taille en pixel des bordures.
     * @var int
     */
    protected $_border_size;
    
    /**
     * Couleur des bordures.
     * @var string
     */
    protected $_border_color;
    
    /**
     * Liste de 4 éléments représentant les border-radius du graphique.
     * @var array<int>
     */
    protected $_border_radius;
	
    /**
     * Constructeur.
     */
	public function __construct()
	{
	    $this->_border_size = 0;
	    $this->_border_color = '#000';
	    $this->_border_radius = array(0, 0, 0, 0);
        $this->_opacity = 100;
        $this->_gradation = FALSE;
	}
	
	/**
	 * Définie la taille du graphique.
	 * @param array<int> $args Une liste des valeurs ou un nombre indéfini d'arguments.
	 * @return Graphic
	 */
	public function size($args)
	{
	    $size = (is_array($args)) ? ($args) : (func_get_args());
        $this->_size = array();
	    foreach ($size as $s)
	    {
	        if (is_numeric($s) && $s >= 0)
	        {
	        	$this->_size[] = $s;
	        } 
	    }
		return $this;
	}
	
	
	/**
	 * Définie les valeurs.
	 * @param array<int> $args Liste des valeurs ou un nombre indéfini d'arguments.
	 * @return Graphic
	 */
	public function value($args)
	{
		$values = (is_array($args)) ? ($args) : (func_get_args());
	    $this->_values = array();
	    foreach ($values as $s)
	    {
	        if (is_numeric($s) && $s >= 0)
	        {
	        	$this->_values[] = $s;
	        } 
	    }
		return $this;
	}
	
	/**
	 * Définie la valeur maximale.
	 * @param int $value Valeur maximale du graphe.
	 * @return Graphic
	 */
	public function value_max($value)
	{
		if (is_numeric($value) && $value > 0)
		{
			$this->_values[0] = $value;
		}
		return $this;
	}
	
	/**
	 * Définie les titres du graphique.
	 * @param array<string> $args Liste des valeurs ou un nombre indéfini d'arguments. 
	 * @return Graphic
	 */
	public function title($args)
	{
		$values = (is_array($args)) ? ($args) : (func_get_args());
	    $this->_titles = array();
	    foreach ($values as $s)
	    {
	        if (is_scalar($s) || $s == NULL)
	        {
	        	$this->_titles[] = $s;
	        } 
	    }
		return $this;
	}
	
	/**
	 * Définie les couleurs du graphique.
	 * @param array<string> $args Liste des valeurs ou un nombre indéfini d'arguments. 
	 * @return Graphic
	 */
	public function color($args)
	{
		$values = (is_array($args)) ? ($args) : (func_get_args());
	    $this->_colors = array();
	    foreach ($values as $s)
	    {
	        if (is_scalar($s))
	        {
	        	$this->_colors[] = $s;
	        } 
	    }
		return $this;
	}
	
	/**
	 * Change la couleur de la police.
	 * @param string $color Couleur de la police.
	 * @return Graphic
	 */
	public function color_font($color)
	{
	    return $this;
	}
	
	/**
	 * Définie l'unité du graphique.
	 * @param string $name Nom de l'unité des valeurs du graphique.
	 * @return Graphic
	 */
	public function unit($name)
	{
	    if (is_scalar($name) || is_null($name))
	    {
	        $this->_unit = $name;
	    }
		return $this;
	}
	
	/**
	 * Définir l'opacité des représentations des valeurs.
	 * @param int $value Nombre en pourcentage.
	 * @return Graphic
	 */
	public function opacity($value=100)
	{
		if (is_numeric($value) && $value >= 0 && $value <= 100)
		{
			$this->_opacity = $value;
		}
		return $this;
	}
	
	/**
	 * Définie les bordures du graphe.
	 * @param int $size Taille en pixel des bordures.
	 * @param string $color Couleur des bordures.
	 * @return Graphic
	 */
	public function border($size, $color)
	{
		if (is_numeric($size))
		{
		    $this->_border_size = $size;
		}
		if (is_scalar($color))
		{
		    $this->_border_color = $color;
		}
		return $this;
	}
	
	/**
	 * Affiche ou cache la gradation des valeurs.
	 * @param boolean $bool Si TRUE, la gradation est affichée.
	 * @return Graphic
	 */
	public function gradation($bool=TRUE)
	{
	    if (is_bool($bool))	
	    {
	        $this->_gradation = $bool;
	    }
		return $this;
	}
	
	/**
	 * Définie les bordures radius du graphe.
	 * @param int $br1 Taille du border radius en haut à gauche ou de toutes les borders radius s'il est le seul argument renseigné.
	 * @param int $br2 Taille du border radius en haut à droite.
	 * @param int $br3 Taille du border radius en bas à droite.
	 * @param int $br4 Taille du border radius en bas à gauche.
	 * @return Graphic
	 */
	public function radius($br1, $br2=NULL, $br3=NULL, $br4=NULL)
	{
		if ($br2 === NULL && $br3 === NULL && $br4 === NULL)
		{
			$this->_border_radius[0] = $br1;
			$this->_border_radius[1] = $br1;
			$this->_border_radius[2] = $br1;
			$this->_border_radius[3] = $br1;
		}
		else
		{
			$this->_border_radius[0] = $br1;
			$this->_border_radius[1] = $br2;
			$this->_border_radius[2] = $br3;
			$this->_border_radius[3] = $br4;
		}
		return $this;
	}
	
	/**
	 * Retourne le contenu HTML du graphique.
	 * @return string
	 */
	public function __toString()
	{
		return '<div class="graphic" style="color:red">Impossible de générer le graphique</div>';
	}
}
?>