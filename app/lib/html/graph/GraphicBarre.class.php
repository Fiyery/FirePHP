<?php
namespace FirePHP\Html\Graph;

/**
 * GraphicBarre est une spécialisation de Graphic sous forme de barres.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 */
class GraphicBarre extends Graphic
{
    /**
     * Liste des labels des barres.
     * @var array<string>
     */ 
    private $_labels;
    
    /**
     * Taille en pixel des marges intérieures.
     * @var int
     */
    private $_padding;
    
    /**
     * Couleur du titre.
     * @var string
     */
    private $_title_color;
    
    /**
     * Constructeur
     */
    public function __construct()
    {
        parent::__construct();
        $this->_size[0] = 500;
        $this->_size[1] = 20;
        $this->_size[2] = 100;
        $this->_values[0] = 0;
        $this->_values[1] = 50;
        $this->_values[2] = 20;
        $this->_values[3] = 30;
        $this->_labels[0] = '1/2';
        $this->_labels[1] = '3/10';
        $this->_labels[2] = '1/5';
        $this->_titles[0] = 'Barre Graphic Title';
        $this->_unit = '%';
        $this->_border_size = 1;
        $this->_padding = 10;
        $this->default_colors();
    }
    
    /**
     * Définie la valeur de la barre.
	 * @param array<int> $args Liste des valeurs de la barre avec comme clé le label. 
	 * @return GraphicBarre
     * @see Graphic::value()
     */
    public function value($args)
    {
        $values = (is_array($args)) ? ($args) : (func_get_args());
        if (isset($this->_values[0]) == FALSE)
        {
            $this->_values[0] = 0;
        }
        $this->_values = array_merge(array($this->_values[0]), array_values($args));
        $this->_labels = array_keys($args);
        return $this;
    }
    
    /**
     * Définie la taille du graphique.
     * @param array<int> $args Une liste des valeurs ou des arguments :
     * Le premier argument est la largeur du graphique.
     * Le deuxième argument des la hauteur des barres.
     * Le troisième argument est la taille des paddings.
     * @return GraphicBarre
     * @see Graphic::size()
     */
    public function size($args)
    {
    	$size = (is_array($args)) ? ($args) : (func_get_args());
    	$this->_size = $size;
    	$this->_padding = (is_array($size) && isset($size[2])) ? ($size[2]) : ($this->_padding);
    	return $this;
    }
    
    /**
     * Définie les marges intérieures du graphe.
     * @param int $pixel Nombre de pixel pour les paddings.
     * @return GraphicBarre
     */
    public function padding($pixel)
    {
        $this->_padding = $pixel;
        return $this;
    }
    
    /**
     * Définie le titre.
     * @param string $args Titre du graphique.
     * @return GraphicBarre
     * @see Graphic::value()
     */
    public function title($args)
    {
    	$values = (is_array($args) && is_string($args) == FALSE) ? ($args) : (func_get_args());
    	if (isset($values[0]) && (is_scalar($values[0]) || $values[0] == NULL))
    	{
    		$this->_titles[0] = $values[0];
    	}
    	return $this;
    }
    
    /**
     * Définie les couleurs du graphique.
     * @param array $args Liste des valeurs ou des arguments :
     * Le premier est la couleur de fond du graphique.
     * Le deuxième est la couleur de la police.
     * Le reste sont les couleurs pour les barres.
     * @return GraphicBarre
     * @see Graphic::color()
     */
    public function color($args)
    {
    	$values = (is_array($args)) ? ($args) : (func_get_args());
    	$this->_colors = $values;
    	return $this;
    }
    
    /**
     * Change la couleur de la police.
     * @param string $color Couleur de la police.
     * @return GraphicBarre
     * @see Graphic::color_font()
     */
    public function color_font($color)
    {
    	if (is_scalar($color))
    	{
    		$this->_colors[1] = $color;
    	}
    	return $this;
    }
    
    /**
     * Change la couleur du titre.
     * @param string $color Couleur du titre.
     * @return GraphicBarre
     * @see Graphic::color_font()
     */
    public function color_title($color)
    {
    	if (is_scalar($color))
    	{
    		$this->_title_color = $color;
    	}
    	return $this;
    }
    
    /**
     * Définie un jeu de couleur par défaut.
     * @return GraphicBarre
     */
    public function default_colors()
    {
        $this->_colors = array(
            '#FFF',
            '#000',
            '#77ABD6',
            '#8AE8DB',
            '#A7A37E',
            '#FF358B',
            '#C44C51',
            '#E8CC06',
            '#333333',
            '#96CA2D',
            '#4BB5C1',
            '#FF5B2B',
            '#B9121B',
            '#F6E497',
            '#BD8D46'
        ); 
        return $this;
    }
    
    /**
     * Retourne le contenu HTML du graphique en barre.
	 * @return string
     * @see Graphic::__toString()
     */
    public function __toString()
    {
        $html = '';
        $width_content = $this->_size[0] - 2 * $this->_padding - 2 * $this->_border_size;
        $width_label = ($this->_size[2] <= $width_content) ? ($this->_size[2]) : ($width_content);
        if ($width_content >= $width_label * 1.5)
        {
            $width_content -= $width_label;
            $clear_progess = FALSE;
        }
        else
        {
            $clear_progess = TRUE;
        }
        $style1 = 'border:'.$this->_border_size.'px solid '.$this->_border_color.';';
        $style1 .= 'width:'.$this->_size[0].'px;';
        $style1 .= 'background-color:'.$this->_colors[0].';';
        $style1 .= 'padding:'.$this->_padding.'px;';
        $style1 .= '-webkit-box-sizing: border-box;
                    -moz-box-sizing: border-box;
                    box-sizing : border-box;'; 
    	$style1 .= 'color:'.$this->_colors[1].';';
    	$style1 .= 'overflow:hidden;';
        $style2 = 'margin-bottom:'.$this->_padding.'px;';
        $style2 .= 'text-align:center;';
        $style2 .= 'color:'.$this->_title_color.';';
        $style3 = 'clear:left;';
        $style3 .= 'float:left;';
        $style3 .= 'width:'.$width_label.'px;';
        $style3 .= 'margin-bottom:2px;';
        $style4 = 'float:left;';
        $style4 .= 'margin-bottom:2px;';
        $style4 .= 'height:'.$this->_size[1].'px;';
        $style4 .= 'text-align:center;';
        if ($clear_progess)
        {
            $style4 .= 'clear:left;';
        }
        $nb_values = count($this->_labels);
        if (isset($this->_values[0]) == FALSE || $this->_values[0] == 0)
        {
        	$this->_values[0] = 0;
        	for($i=1; $i <= $nb_values; $i++)
        	{
        	    $this->_values[0] = $this->_values[0] + $this->_values[$i];
        	}
        }
        $html = "<div class='graphic graphic-barre' style='".$style1."'>";
        $html .= "<div class='graphic-barre-title' style='".$style2."'>".$this->_titles[0]."</div>";
        $count = count($this->_colors) - 2;
        for($i=0; $i < $nb_values; $i++)
        {
        	$j = ($i % $count) + 2;
        	$width_barre = round($this->_values[$i+1]/$this->_values[0]*100);
        	$width_barre = ($width_barre / 100) * $width_content;
        	$html .= "<label class='graphic-barre-label' style='color:".$this->_colors[$j].";".$style3."'>".$this->_labels[$i]."</label>";
        	$html .= "<div class='graphic-barre-progress' style='background-color:".$this->_colors[$j].";width:".$width_barre."px;".$style4."'>";
        	if ($this->_gradation)
        	{
        	    $html .= $this->_values[$i+1].$this->_unit;
        	}
        	$html .= "</div>";
        }
        $html .= "</div>";
        return $html;
    }
}
?>