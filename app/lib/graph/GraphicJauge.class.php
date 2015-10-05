<?php
/**
 * GraphicJauge est une spécialisation de Graphic sous forme d'une jauge remplissable.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @copyright 2011-2015 Yoann Chaumin
 */
class GraphicJauge extends Graphic
{
    /**
     * Constructeur
     */
    public function __construct()
    {
    	parent::__construct();
    	$this->_colors[0] = '#FFF';
    	$this->_colors[1] = '#D3D3D3';
    	$this->_colors[2] = '#000';
    	$this->_size[0] = 200;
    	$this->_size[1] = 20;
    	$this->_values[0] = 100;
    	$this->_values[1] = 50;
    	$this->_titles[0] = 'Jauge Graphic Title';
    	$this->_unit = '%';
    	$this->_border_size = 1;
    }
    
    /**
     * Définie la valeur de la barre.
     * @param int $args Valeur de la barre.
     * @return GraphicJauge
     * @see Graphic::value()
     */
    public function value($args)
    {
    	$values = (is_array($args)) ? ($args) : (func_get_args());
    	if (isset($values[0]) && is_numeric($values[0]) && $values[0] >= 0)
    	{
    		$this->_values[1] = $values[0];
    	}
    	return $this;
    }
    
    /**
     * Définie la taille du graphique.
     * @param array<int> $args Une liste des valeurs ou des arguments :
     * Le premier argument est la largeur du graphique.
     * Le deuxième argument des la hauteur du graphique.
     * @return GraphicJauge
     * @see Graphic::size()
     */
    public function size($args)
    {
    	$size = (is_array($args)) ? ($args) : (func_get_args());
    	$this->_size = $size;
    	return $this;
    }
    
    /**
     * Définie les couleurs du graphique.
     * @param array<string> $args Liste des valeurs ou des arguments :
     * Le premier est la couleur de fond de la jauge.
     * Le deuxième est la couleur de la jauge.
     * Le troisième est la couleur de la police.
     * @return GraphicJauge
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
     * @return GraphicJauge
     * @see Graphic::color_font()
     */
    public function color_font($color)
    {
        if (is_scalar($color))
        {
            $this->_colors[2] = $color;
        }
    	return $this;
    }
    
    /**
     * Définie le titre.
     * @param string $args Titre du graphique.
     * @return GraphicJauge
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
     * Retourne le contenu HTML du graphique en barre.
     * @return string
     * @see Graphic::__toString()
     */
    public function __toString()
    {
    	$html = '';
    	$style1 = 'width:'.$this->_size[0].'px;';
    	if ($this->_gradation)
    	{
    		$style1 .= 'height:'.($this->_size[1]+20+2*$this->_border_size).'px;';
    	}
    	else
    	{
    		$style1 .= 'height:'.($this->_size[1]+2*$this->_border_size).'px;';
    	}
    	$width_content = $this->_size[0] - 2 * $this->_border_size;
    	$style1 .= 'overflow:hidden;';
    	$style1 .= 'position:relative;';
    	$style1 .= 'color:'.$this->_colors[2].';';
    	$style1 .= '-webkit-box-sizing: border-box;
                    -moz-box-sizing: border-box;
                    box-sizing : border-box;';
    	$style2 = 'background-color:'.$this->_colors[1].';';
    	if ($this->_values[0] == 0)
    	{
    	    $width_progess = 0;
    	}
    	else 
    	{
    	    $width_progess = ($this->_values[1]/$this->_values[0]) * 100;
    	    $width_progess = ($width_progess / 100) * $width_content;
    	}
    	$style2 .= 'width:'.$width_progess.'px;';
    	$style2 .= 'height:'.$this->_size[1].'px;';
    	$style2 .= 'position:relative;';
    	$style2 .= 'opacity:'.($this->_opacity/100).';';
    	$style2 .= 'margin-top:'.$this->_border_size.'px;';
    	$style2 .= 'margin-left:'.$this->_border_size.'px;';
    	$style3 = 'width:100%;';
    	$style3 .= 'position:absolute;';
    	$style3 .= 'height:'.$this->_size[1].'px;';
    	$style3 .= 'text-align:center;';
    	$style3 .= 'overflow:hidden;';
    	$style3 .= 'height:'.$this->_size[1].'px;';
    	$style3 .= 'line-height:'.$this->_size[1].'px;';
    	$style3 .= 'z-index:3;';
    	$style4 = 'background-color:'.$this->_colors[0].';';
    	$style4 .= 'width:'.$width_content.'px;';
    	$style4 .= 'height:'.$this->_size[1].'px;';
    	$style4 .= 'border: '.$this->_border_size.'px '.$this->_border_color.' solid;';
    	$style4 .= 'border-radius: '.$this->_border_radius[0].'px '.$this->_border_radius[1].'px '.$this->_border_radius[2].'px '.$this->_border_radius[3].'px;';
    	$style4 .= 'overflow:hidden;';
    	$style4 .= 'position:absolute;';
    	$html = '
			<div class="graphic graphic-jauge" style="'.$style1.'">
				<div class="graphic-jauge-frame" style="'.$style4.'"></div>
				<div class="graphic-jauge-title" style="'.$style3.'">'.$this->_titles[0].'</div>
				<div class="graphic-jauge-progress" style="'.$style2.'"></div>';
    	if ($this->_gradation)
    	{
    		$style5 = 'float:left;';
    		$style5 .= 'padding-left:3px;';
    		$style6 = 'float:right;';
    		$style6 .= 'padding-right:3px;';
    		$html .= "
				<div>
					<div style='".$style5."'>0 ".$this->_unit."</div>
					<div style='".$style6."'>".$this->_values[0]." ".$this->_unit."</div>
				</div>
			";
    	}
    	$html .= '</div>';
    	return $html;
    }  
}
?>