<?php
/**
 * Page permet de générer le code html d'un pagination en incorporant l'url, le nombre de page et la page courante.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @copyright 2011-2013 Yoann Chaumin
 */
class Page 
{
	/**
	 * Nombre maximal de pages.
	 * @var int
	 */
	private $max; 
	
	/**
	 * Numéro de la page courante.
	 * @var int
	 */
	private $current;
	
	/**
	 * Addresse URL sur laquelle on appliquera la pagination. Rajout de la pagionation à cette adresse.
	 * @var string
	 */
	private $url;
	
	/**
	 * Nombre de pages visibles à côté de la page courante.
	 * @var int
	 */
	private $precision = 2; 
	
	/**
	 * Chaîne à placer à la fin de l'url.
	 * @var string
	 */
	private $end_url = ''; 
	
	/**
	 * Constructeur.
	 * @param $url string URL à paginer.
	 * @param $max int Nombre total de page.
	 * @param $current int Numéro de la page courante.
	 */
	public function __construct($url,$max,$current)
	{
		$this->url = $url;
		$this->max = $max;
		$this->current = $current;
		$this->parse_url();
	}
	
	/**
	 * Transforme l'URL en ajoutant le paramètre de la page.
	 */
	private function parse_url()
	{
		if (substr($this->url,-1) == '!')
		{
			$this->url_page = $this->url.'page';
		}
		elseif (stripos($this->url,'?') !== FALSE)
		{
			$this->url_page = $this->url.'&page=';
		}
		else
		{
			$this->url_page = $this->url.'?page=';
		}		
	}
	
	/**
	 * Génération du code HTML.
	 * @return string Le code HTML de la pagination.
	 */
	public function __toString()
	{
		$html = '<div class="bloc_page">';
		$i = 1;
		if ($i == $this->current)
		{
			$html .= '<div class="link_page selected"><a href="'.($this->url).'">'.$i.'</a></div>';
		}
		else 
		{
			$html .= '<div class="link_page"><a href="'.($this->url).'">'.$i.'</a></div>';
		}
		if ($i + 1 < ($this->current-$this->precision))
		{
			$i = ($this->current-$this->precision);
			$html .= '<div class="separator">...</div>';
		}
		else 
		{
			$i++;
		}	
		while ($i <= ($this->current+$this->precision) && $i < $this->max)
		{
			if ($i == $this->current)
			{
				$html .= '<div class="link_page selected"><a href="'.($this->url_page.$i).$this->end_url.'">'.$i.'</a></div>';
			}
			else 
			{
				$html .= '<div class="link_page"><a href="'.($this->url_page.$i).$this->end_url.'">'.$i.'</a></div>';
			}
			$i++;
		}
		if ($i <= $this->max)
		{
			if ($i < $this->max)
			{
				$html .= '<div class="separator">...</div>';
			}
			$i = $this->max;
			if ($i == $this->current)
			{
				$html .= '<div class="link_page selected"><a href="'.($this->url_page.$i).$this->end_url.'">'.$i.'</a></div>';
			}
			else 
			{
				$html .= '<div class="link_page"><a href="'.($this->url_page.$i).$this->end_url.'">'.$i.'</a></div>';
			}	
		}	
		$html .= '</div>';
		return $html;
	}
}
?>