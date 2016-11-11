<?php
/**
 * Helper pour l'utilisation des Tableaux.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 */
class Table implements ArrayAccess, IteratorAggregate, Countable
{
	/**
	 * Identifiant CSS du tableau si génération HTML.
	 * @var string
	 */
    private $_id = '';
    
    /**
     * Classes CSS du tableau si génération HTML.
     * @var string
     */
    private $_class = '';
    
    /**
     * Liste des entêtes du tableau si génération HTML.
     * @var array
     */
    private $_header = [];
    
    /**
     * Contenu du tableau.
     * @var array
     */
    private $_array;
    
    /**
     * Tableau parent afin de permettre la modification des sous-éléments d'un tableau.
     * @var Table
     */
    private $_parent = NULL;
    
    /**	
     * Tableaux enfant afin de permettre la modification des sous-éléments d'un tableau.
     * @var unknown
     */
    private $_childs = [];

    /**
     * Contructeur.
     * @param array $array
     */
    public function __construct(array $array = array())
    {
        $this->_array = $array;
    }

    /**
     * Retourne une valeur selon la clé.
     * @param string $index NULL pour récupérer tout le tableau.
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
     * Définie une valeur dans le tableau.
     * @param string $index
     * @param string $value
     * @return string
     */
    public function set($index, $value)
    {
        $this->_array[$index] = $value;
        // Mise à jour du parent.
        if ($this->_parent !== NULL)
        {
            $this->_parent->set(array_search($this, $this->_parent->_childs), $this->_array);
        }
    }

    /**
     * Vérifie l'existence d'un index.
     * @param string $index Clé à rechercher.
     * @param boolean
     */
    public function has($index)
    {
        return isset($this->_array[$index]);
    }

    /**
     * Recherche la clé d'un élément du tableau.
     * @param string $value Valeur à rechercher.
     * @return string
     */
    public function search($value)
    {
        return array_search($value, $this->_array);
    }

    /**
     * Trie le tableau.
     * @param bool $asc Défini si l'ordre est croissant ou non.
     * @param string
     * @return array
     */
    public function sort($asc=TRUE, $column=NULL)
    {
        uasort($this->_array, function($a, $b) use ($column, $asc) {
            return $this->_compare($a, $b, $asc, $column);
        });
        return $this;
    }

    /**
     * Trie le tableau selon l'algorithme du trie à bulle.
     * @param bool $asc Défini si l'ordre est croissant ou non.
     * @param string
     * @return array
     */
    public function sort_linear($asc=TRUE, $column=NULL)
    {
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
     * Compare deux éléments
     * @param mixed $a Premier élément à comparer.
     * @param mixed $b Deuxième élément à comparer.
     * @param bool $asc Trie croissant ou décroissant.
     * @param string $column Nom de la colonne des sous-éléments si besoin.
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
     * Exécute une fonction sur tout le table ou une colonne des sous-éléments.
     * @param callback $callback Fonction a exécuter.
     * @param string $column Colonne sur laquelle exécuter la fonction pour les sous-éléments.
     */
    public function call($callback, $column = NULL)
    {
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
     * Supprime un élément du tableau.
     * @param string $index.
     * @return Table
     */
    public function remove($index)
    {
        unset($this->_array[$index]);
        return $this;
    }

    /**
     * Extrait l'ensemble des clés.
     * @return Table
     */
    public function keys()
    {
        return new self(array_keys($this->_array));
    }

    /**
     * Extrait sous forme de liste l'ensemble des valeurs selon la clé des sous tableaux.
     * @param string $key Clé des sous tableaux à extraire.
     * @return Table
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
     * @param string $glue Caractère de liaison.
     * @param string $index Facultatif.
     * @return string
     */
    public function join($glue, $index = FALSE)
    {

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
     * Définie la ou les classes CSS.
     * @param string $class
     * @return Table
     */
    public function classe($class)
    {
        $this->_class = $class;
        return $this;
    }

    /**
     * Définie l'identifiant du tableau.
     * @param string $id
     * @return Table
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