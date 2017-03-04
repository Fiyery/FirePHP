<?php
/**
 * Helper pour l'utilisation des Tableaux.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 */
class Collection implements ArrayAccess, IteratorAggregate, Countable
{
	/**
	 * Identifiant CSS du tableau si g�n�ration HTML.
	 * @var string
	 */
    private $_id = '';
    
    /**
     * Classes CSS du tableau si g�n�ration HTML.
     * @var string
     */
    private $_class = '';
    
    /**
     * Liste des ent�tes du tableau si g�n�ration HTML.
     * @var array
     */
    private $_header = [];
    
    /**
     * Contenu du tableau.
     * @var array
     */
    private $_array;
    
    /**
     * Tableau parent afin de permettre la modification des sous-�l�ments d'un tableau.
     * @var Collection
     */
    private $_parent = NULL;
    
    /**	
     * Tableaux enfant afin de permettre la modification des sous-�l�ments d'un tableau.
     * @var array
     */
    private $_childs = [];

    /**
     * Sauvegarde du dernier index pour une insertion dichotomique afin de concerver l'ordre croissant. 
     * @var int
     */
    private $_dichotomous_index = [];

    /**
     * Contructeur.
     * @param array $array
     */
    public function __construct(array $array = [])
    {
        $this->_array = $array;
    }

    /**
     * Retourne une valeur selon la cl�.
     * @param string $index NULL pour r�cup�rer tout le tableau.
     * @return mixed
     */
    public function get($index=NULL)
    {
        if ($index === NULL)
        {
            return $this->_array;
        }
        if ($this->has($index))
        {
            $res = $this->_array[$index];
            if (is_array($res))
            {
                $child = new self($res);
                $this->_childs[$index] = $child;
                $child->_parent = $this;
                return $child;
            }
            else
            {
                return $res;
            }
        }
        else
        {
            return NULL;
        }
    }

    /**
     * D�finie une valeur dans le tableau.
     * @param string $index
     * @param string $value
     * @return string
     */
    public function set($index, $value)
    {
        // Reset les indexs sauvegard�s.
        $this->_dichotomous_index = [];
    
        $this->_array[$index] = $value;
        // Mise � jour du parent.
        if ($this->_parent !== NULL)
        {
            $this->_parent->set(array_search($this, $this->_parent->_childs), $this->_array);
        }
    }

    /**
     * V�rifie l'existence d'un index.
     * @param string $index Cl� � rechercher.
     * @param boolean
     */
    public function has($index)
    {
        return isset($this->_array[$index]);
    }

    /**
     * Recherche la cl� d'un �l�ment du tableau.
     * @param string $value Valeur � rechercher.
     * @return string
     */
    public function search($value)
    {
        return array_search($value, $this->_array);
    }

    /**
     * Trie le tableau.
     * @param bool $asc D�fini si l'ordre est croissant ou non.
     * @param string
     * @return array
     */
    public function sort($asc=TRUE, $column=NULL)
    {
        // Reset les indexs sauvegard�s.
        $this->_dichotomous_index = [];

        uasort($this->_array, function($a, $b) use ($column, $asc) {
            return $this->_compare($a, $b, $asc, $column);
        });
        return $this;
    }

    /**
     * Trie le tableau selon l'algorithme du trie � bulle.
     * @param bool $asc D�fini si l'ordre est croissant ou non.
     * @param string
     * @return array
     */
    public function sort_linear($asc=TRUE, $column=NULL)
    {
        // Reset les indexs sauvegard�s.
        $this->_dichotomous_index = [];

        $max = $this->count();
        for($j=1; $j < $max; $j++)
        {
            for($i=1; $i < $max; $i++)
            {
                if ($this->_compare($this->_array[$i-1], $this->_array[$i], $asc, $column) > 0)
                {
                    $o = $this->_array[$i-1];
                    $this->_array[$i-1] = $this->_array[$i];
                    $this->_array[$i] = $o;
                }
            }
        }
        return $this;
    }

    /**
     * Compare deux �l�ments
     * @param mixed $a Premier �l�ment � comparer.
     * @param mixed $b Deuxi�me �l�ment � comparer.
     * @param bool $asc Trie croissant ou d�croissant.
     * @param string $column Nom de la colonne des sous-�l�ments si besoin.
     * @return int
     */
    private function _compare($a, $b, $asc=TRUE, $column=NULL)
    {
        if ($column !== NULL)
        {
            if ((is_object($a) && isset($a->$column) === FALSE) || (is_array($a) && isset($a[$column]) === FALSE) || (is_object($a) === FALSE && is_array($a) === FALSE))
            {
                return 0;
            }
            if ((is_object($b) && isset($b->$column) === FALSE) || (is_array($b) && isset($b[$column]) === FALSE) || (is_object($b) === FALSE && is_array($b) === FALSE))
            {
                return 0;
            }
            $a = (is_object($a)) ? ($a->$column) : ($a[$column]);
            $b = (is_object($b)) ? ($b->$column) : ($b[$column]);
        }
        if ($a == $b)
        {
            return 0;
        }
        elseif (is_numeric($a) && is_numeric($b))
        {
            return (($asc && $a > $b) || ($asc === FALSE && $a < $b)) ? (1) : (-1);
        }
        elseif ((is_scalar($a) && is_scalar($b)))
        {
            return ($asc) ? (strcasecmp($a, $b)) : (strcasecmp($b, $a));
        }
        else
        {
            return 0;
        }
    }

    /**
     * Ex�cute une fonction sur tout le table ou une colonne des sous-�l�ments.
     * @param callback $callback Fonction a ex�cuter.
     * @param string $column Colonne sur laquelle ex�cuter la fonction pour les sous-�l�ments.
     * @return Collection
     */
    public function call($callback, $column = NULL)
    {
        // Reset les indexs sauvegard�s.
        $this->_dichotomous_index = [];

        if ($column === NULL)
        {
            array_walk($this->_array, $callback);
        }
        else
        {
            reset($this->_array);
            while (list($i, $o) = each($this->_array))
            {
                $this->_array[$i][$column] = call_user_func($callback, $this->_array[$i][$column]);
            }
        }
        return $this;
    }

    /**
     * Efface et remplace une portion de tableau.
     * @param int $offset Position dans le tableau.
     * @param int $length Taille � supprimer.
     * @param mixed $replacement Element qui remplacement la s�lection supprim�e.
     * @return Collection
     */
    public function splice($offset, $length, $replacement)
    {
        // Reset les indexs sauvegard�s.
        $this->_dichotomous_index = [];

        array_splice($this->_array, $offset, $length, $replacement);
        return $this;
    }

    /**
     * Supprime un �l�ment du tableau.
     * @param string $index.
     * @return Collection
     */
    public function remove($index)
    {
        // Reset les indexs sauvegard�s.
        $this->_dichotomous_index = [];

        unset($this->_array[$index]);
        return $this;
    }

    /**
     * Extrait l'ensemble des cl�s.
     * @return Collection
     */
    public function keys()
    {
        return new self(array_keys($this->_array));
    }

    /**
     * Extrait sous forme de liste l'ensemble des valeurs selon la cl� des sous tableaux.
     * @param string $key Cl� des sous tableaux � extraire.
     * @return Collection
     */
    public function extract($key)
    {
        $res = [];
        foreach ($this->_array as $a)
        {
            if (is_array($a) && isset($a[$key]))
            {
                $res[] = $a[$key];
            }
        }
        return new self($res);
    }

    /**
     * Transforme le tableau ou une partie du tableau en chaine.
     * @param string $glue Caract�re de liaison.
     * @param string $index Facultatif.
     * @return string
     */
    public function join($glue, $index = FALSE)
    {
        // Reset les indexs sauvegard�s.
        $this->_dichotomous_index = [];

        if ($index !== FALSE)
        {
            return implode($glue, $this->extract($index)->_array);
        }
        else
        {
            return implode($glue, $this->_array);
        }
    }

    /**
     * Calcul le maximum.
     * @param string $index Facultatif.
     * @return number
     */
    public function max($index = FALSE)
    {
        if ($index !== FALSE)
        {
            return max($this->extract($index)->_array);
        }
        else
        {
            return max($this->_array);
        }
    }

    /**
     * Calcul le minimum.
     * @param string $index Facultatif.
     * @return number
     */
    public function min($index = FALSE)
    {
        if ($index !== FALSE)
        {
            return min($this->extract($index)->_array);
        }
        else
        {
            return min($this->_array);
        }
    }

    /**
     * D�finie la ou les classes CSS.
     * @param string $class
     * @return Collection
     */
    public function classe($class)
    {
        $this->_class = $class;
        return $this;
    }

    /**
     * D�finie l'identifiant du tableau.
     * @param string $id
     * @return Collection
     */
    public function id($id)
    {
        $this->_id = $id;
        return $this;
    }

    /**
     * Affiche le tableau
     * @return string
     */
    public function show()
    {
        return print_f($this->_array, TRUE);
    }

    /**
     * Traduit le tableau de maximum deux dimensions en HTML.
     * @return string Tableau HTML.
     */
    public function html()
    {
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
        foreach ($this->_array as $cell)
        {
            $s .= '<tr>';
            if (is_array($cell))
            {
                foreach($cell as $c)
                {
                    if (is_scalar($c))
                    {
                        $s .= '<td>'.$c.'</td>';
                    }
                }
            }
            else
            {
                $s .= '<td>'.$cell.'</td>';
            }
            $s .= '</tr>';
        }
        $s .= '</tbody></table>';
        return $s;
    }

    /**
     * Recherche dichotomique d'une valeur dans un tableau num�rique tri� par ordre croissant.
     * @param int $val 
     * @return int Index de la valeur trouv�e ou -1.
     */
    public function search_dichotomous($val)
    {
        $min = 0;
        $max = $this->count() - 1;
        if ($val === $this[$min])
        {
            return $min;
        }
        if ($val === $this[$max])
        {
            return $max;
        }
        $find = FALSE;  
        while ($find === FALSE && ($max - $min) > 1)
        {
            $mid = floor(($max + $min) / 2);
            $find = ($val === $this[$mid]);
            if ($val < $this[$mid])
            {
                $max = $mid;
            }
            else 
            {
                $min = $mid;
            }
        }
        $this->_last_dichotomous_index[$val] = $mid;
        return ($find) ? ($mid) : (-1);
    }

    public function insert_dichotomous($val)
    {
        if ($val >= $this[$this->count() -1])
        {
            return $this->splice($this->count(), 0, $val);
        }
        if ($val <= $this[0])
        {
            return $this->splice(0, 0, $val);
        }
        if (isset($this->_last_dichotomous_index[$val]) === FALSE)
        {
            $this->search_dichotomous($val);
        }
        return $this->splice($this->_last_dichotomous_index[$val], 0, $val);
    }

    /**
     * Surcharge Interface Countable
     * @return int
     */
    public function count()
    {
        return count($this->_array);
    }

    /**
     * Methodes ArrayAccess
     */
    public function offsetExists($index)
    {
        return $this->has($index);
    }

    public function offsetGet($index)
    {
        return $this->get($index);
    }

    public function offsetSet($index, $value)
    {
        return $this->set($index, $value);
    }

    public function offsetUnset($index)
    {
        $this->remove($index);
    }

    /**
     * Methodes IteratorAggregate
     */
    public function getIterator()
    {
        return new ArrayIterator($this->_array);
    }

    /**
     * Affiche le tableau de debug.
     * @return array
     */
    public function __debugInfo()
    {
        return $this->_array;
    }

    /**
     * Converti le tableau en HTML.
     * @return string
     */
    public function __toString()
    {
        return $this->html();
    }
}
?>