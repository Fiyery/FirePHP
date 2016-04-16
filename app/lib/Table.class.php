<?php
/**
 * Table contient les fonctions utiles sur les tableaux PHP et HTML.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 */
class Table
{
	/**
	 * Constant pour le trie par ordre croissant.
	 */
	const SORT_ASC = 1;
	
	/**
	 * Constant pour le trie par ordre décroissant.
	 */
	const SORT_DESC = 2;

	/**
	 * Type de trie pour la fonction sort.
	 * @var string
	 */
	private static $_sort = NULL;
	
	/**
	 * Colonne de trie pour la fonction sort.
	 * @var string
	 */
	private static $_key = NULL;
	
	/**
	 * Valeur du tableau.
	 * @var array
	 */
	private $_cases = array();
	
	/**
	 * Nombre de cases horizontales.
	 * @var int
	 */
	private $_width = 0;
	
	/**
	 * Nombre de cases verticales.
	 * @var int
	 */
	private $_height = 0;
	
	/**
	 * Entête du tableau.
	 * @var array<string>
	 */
	private $_header = array();
	
	/**
	 * Classes du tableau.
	 * @var string
	 */
	private $_class = NULL;
	
	/**
	 * Identifiant du tableau.
	 * @var string
	 */
	private $_id = NULL;

	/**
	 * Constructeur.
	 * @param int|array $width Taille en largeur ou un tableau.
	 * @param int $height Taille en haut.
	 */
	public function __construct($width = 0, $height = 0)
	{
		if (is_int($width) && is_int($height))
		{
			if ($width > 0 && $height == 0)
			{
				$height = 1;
			}
			$tab = array();
			for($i = 0; $i < $width; $i ++)
			{
				for($j = 0; $j < $height; $j ++)
				{
					
					$this->_cases[$j][$i] = NULL;
				}
			}
			$this->_width = $width;
			$this->_height = $height;
		}
		elseif (is_array($width))
		{
			$this->_height = count($width);
			$this->_width = 1;
			if (is_array(current($width)) == FALSE)
			{
				$this->_cases = array_values($width);
			}
			else
			{
				$this->_cases = array_values(array_map('array_values',$width));
				foreach ($width as $c)
				{
					$count = count($c);
					if ($count > $this->_width)
					{
						$this->_width = $count;
					}
				}
			}
		}
	}	
	
	/**
	 * Assigne une valeur à une case du tableau.
	 * @param int $width Cordonnée x de la case.
	 * @param int $height Cordonnée y de la case.
	 * @param string $value Valeur de la case.
	 * @return boolean
	 */
	public function set($width,$height,$value)
	{
		if (is_int($width) && is_int($height))
		{
			if ($width >= 0 || $width <= $this->_width || $height >= 0 || $height <= $this->_height)
			{
				if (is_string($value) && isset($this->_cases[$height][$width]))
				{
					$this->_cases[$height][$width] = $value;
					return TRUE;
				}
			}
		}
		return FALSE;
	}
	
	/**
	 * Définie l'entête du tableau.
	 * @param array $header Tableau des noms de colonne.
	 * @return boolean
	 */
	public function set_header($header)
	{
		if (is_array($header) == FALSE)
		{
			return FALSE;
		}
		$this->_header = $header;
		$count = count($header);
		if ($this->_width < $count)
		{
			$this->_width = $count;
		}
		return TRUE;
	}
	
	/**
	 * Définie la ou les classes CSS.
	 * @param string $class
	 */
	public function set_class($class)
	{
		$this->_class = $class;
	}
	
	/**
	 * Définie l'identifiant du tableau.
	 * @param string $id
	 */
	public function set_id($id)
	{
		$this->_id = $id;
	}

	/**
	 * Traduit le tableau en HTML.
	 * @return boolean|string Tableau HTML ou FALSE.
	 */
	public function __toString()
	{
		if (count($this->_cases) == 0)
		{
			return FALSE;
		}
		$s = '<table';
		if ($this->_id != NULL)
		{
			$s .= ' id="'.$this->_id.'"';
		}			
		if ($this->_class != NULL)
		{
			$s .= ' class="'.$this->_class.'"';
		}			
		$s .= '>';
		if (count($this->_header) > 0)
		{
			$s .= '<thead><tr>';
			foreach ($this->_header as $e)
			{
				$s .= '<th>'.$e.'</th>';
			}
			$s .= '</tr></thead><tbody>';
		}
		else
		{
			$s .= '<tbody>';
		}
		foreach ($this->_cases as $case)
		{
			$s .= '<tr>';
			if (is_array($case))
			{
				foreach($case as $c)
				{
					$s .= '<td>'.$c.'</td>';
				}
			}
			else
			{
				$s .= '<td>'.$case.'</td>';
			}
			$s .= '</tr>';
		}
		$s .= '</tbody></table>';
		return $s;
	}
	
	/**
	 * Trie de tableau bi-dimensionnel.  
	 * @param array &$array Tableau à trier.
	 * @param string $column Nom de la colonne.
	 * @param int $sort Constante qui définie le trie ascendant pour descendant. 
	 * @return boolean
	 */
	public static function sort(&$array, $column, $sort=self::SORT_ASC)
	{
		if (!is_array($array) || ($sort != self::SORT_ASC && $sort != self::SORT_DESC))
		{
			return FALSE;
		}
		if ($column == NULL)
		{
			if ($sort == self::SORT_ASC && asort($array) == FALSE)
			{
				return FALSE;
			}
			elseif ($sort == self::SORT_DESC && arsort($array) == FALSE)
			{
				return FALSE;
			}
		}
		elseif (is_string($column))
		{
			self::$_sort = $sort;
			self::$_key = $column;
			if ($sort == self::SORT_ASC && usort($array,array(__CLASS__,'compare')) == FALSE)
			{
				return FALSE;
			}
			elseif ($sort == self::SORT_DESC && usort($array,array(__CLASS__,'compare')) == FALSE)
			{
				return FALSE;
			}
		}
		return TRUE;
	}
	
	/**
	 * Compare deux éléments.
	 * @param string|numeric $a Premier élément.
	 * @param string|numeric $b Deuxième élément.
	 * @return int 
	 */
	private static function compare($a,$b)
	{
		$k = self::$_key;
		if (self::$_sort == NULL && $k == NULL && gettype($a[$k]) != gettype($b[$k]))
		{
			return 0;
		}
		elseif (is_numeric($a[$k]))
		{
			return (self::$_sort == self::SORT_ASC) ? ($a[$k] - $b[$k]) : ($b[$k] - $a[$k]);
		}
		elseif (is_string($a[$k]))
		{
			return (self::$_sort == self::SORT_ASC) ? (strcmp($a[$k],$b[$k])) : (strcmp($b[$k],$a[$k]));
		}
		return 0;
	}

	/**
	 * Transforme une liste d'objets vers un tableau.
	 * @param array $list Liste d'objets.
	 * @return boolean|array Retourne le tableau ou FALSE si erreur.
	 */
	public static function list_object_to_array($list)
	{
		if (is_array($list) == FALSE || count($list) == 0)
		{
			return FALSE;
		}
		$array = array_map('get_object_vars', $list);
		return $array;
	}

}	
?>