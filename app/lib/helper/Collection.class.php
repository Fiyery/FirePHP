<?php
namespace FirePHP\Helper;

use Countable;
use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;

/**
 * Helper pour l'utilisation des Tableaux.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 */
class Collection implements ArrayAccess, IteratorAggregate, Countable
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
     * @var Collection
     */
    private $_parent = NULL;
    
    /**	
     * Tableaux enfant afin de permettre la modification des sous-éléments d'un tableau.
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
     * Retourne une valeur selon la clé.
     * @param string $index NULL pour récupérer tout le tableau.
     * @return mixed
     */
    public function get(string $index = NULL)
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
     * @param mixed $value
     */
    public function set(string $index, $value)
    {
        // Reset les indexs sauvegardés.
        $this->_dichotomous_index = [];
    
        $this->_array[$index] = $value;
        // Mise à jour du parent.
        if ($this->_parent !== NULL)
        {
            $this->_parent->set(array_search($this, $this->_parent->_childs), $this->_array);
        }
    }

    /**
     * Définie une valeur dans le tableau.
     * @param mixed $value
     * @return Collection
     */
    public function push($value) : Collection
    {
        $this->_array[] = $value;
        return $this;
    }

    /**
     * Vérifie l'existence d'un index.
     * @param string $index Clé à rechercher.
     * @param bool
     */
    public function has(string $index) : bool
    {
        return isset($this->_array[$index]);
    }

    /**
     * Recherche la clé d'un élément du tableau.
     * @param string $value Valeur à rechercher.
     * @return mixed
     */
    public function search($value)
    {
        return array_search($value, $this->_array);
    }

    /**
     * Trie le tableau.
     * @param bool $asc Défini si l'ordre est croissant ou non.
     * @param string Nom de la colonne
     * @return Collection
     */
    public function sort(bool $asc=TRUE, string $column = NULL) : Collection
    {
        // Reset les indexs sauvegardés.
        $this->_dichotomous_index = [];

        uasort($this->_array, function($a, $b) use ($column, $asc) {
            return $this->_compare($a, $b, $asc, $column);
        });
        return $this;
    }

    /**
     * Trie le tableau selon l'algorithme du trie à bulle.
     * @param bool $asc Défini si l'ordre est croissant ou non.
     * @param string
     * @return Collection
     */
    public function sort_linear(bool $asc = TRUE, string $column = NULL) : Collection
    {
        // Reset les indexs sauvegardés.
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
     * Compare deux éléments
     * @param mixed $a Premier élément à comparer.
     * @param mixed $b Deuxième élément à comparer.
     * @param bool $asc Trie croissant ou décroissant.
     * @param string $column Nom de la colonne des sous-éléments si besoin.
     * @return int
     */
    private function _compare($a, $b, bool $asc = TRUE, string $column = NULL) : int
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
     * @param Callable $callback Fonction à exécuter.
     * @param string $column Colonne sur laquelle exécuter la fonction pour les sous-éléments.
     * @return Collection
     */
    public function call(Callable $callback, string $column = NULL) : Collection
    {
        // Reset les indexs sauvegardés.
        $this->_dichotomous_index = [];

        if ($column === NULL)
        {
            array_walk($this->_array, $callback);
        }
        else
        {
            reset($this->_array);
			foreach ($this->_array as $k => $o)
            {
                $this->_array[$k][$column] = call_user_func($callback, $o[$column]);
            }
        }
        return $this;
    }

    /**
     * Efface et remplace une portion de tableau.
     * @param int $offset Position dans le tableau.
     * @param int $length Taille à supprimer.
     * @param mixed $replacement Element qui remplacement la sélection supprimée.
     * @return Collection
     */
    public function splice(int $offset, int $length, $replacement) : Collection
    {
        // Reset les indexs sauvegardés.
        $this->_dichotomous_index = [];

        array_splice($this->_array, $offset, $length, $replacement);
        return $this;
    }

    /**
     * Supprime un élément du tableau.
     * @param string $index.
     * @return Collection
     */
    public function remove($index) : Collection
    {
        // Reset les indexs sauvegardés.
        $this->_dichotomous_index = [];

        unset($this->_array[$index]);
        return $this;
    }

    /**
     * Extrait l'ensemble des clés.
     * @return Collection
     */
    public function keys() : Collection
    {
        return new self(array_keys($this->_array));
    }

    /**
     * Extrait sous forme de liste l'ensemble des valeurs selon la clé des sous tableaux.
     * @param string $key Clé des sous tableaux à extraire.
     * @return Collection
     */
    public function extract($key) : Collection
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
    public function join(string $glue, string $index = FALSE) : string
    {
        // Reset les indexs sauvegardés.
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
     * Extrait les valeurs d'une colonne des sous tableaux.
     * @param mixed $name Nom de la colonne.
     * @return Collection
     */
    public function column($name) : Collection
    {
        return new self(array_column($this->_array, $name));
    }

    /**
     * Combine le tableau courant en clé et celui en paramètre en valeur.
     * @param Collection Tableau de valeur
     * @return Collection
     */
    public function combine(Collection $array) : Collection
    {
        return new self(array_combine($this->_array, $array->_array));
    }

    /**
     * Différence entre le tableau courant et celui en paramètre.
     * @param Collection Tableau à comparer
     * @return Collection
     */
    public function diff(Collection $array) : Collection
    {
        return new self(array_diff($this->_array, $array->_array));
    }

    /**
     * Vérifie si le tableau est trié.
     * @return bool
     */
    public function is_sorted() : bool
    {
        $asc = TRUE;
        $desc = TRUE;
        reset($this->_array);
        $e0 = current($this->_array);
        while (($asc || $desc) && ($e1 = next($this->_array)))
        {
            $asc = ($asc && ($e0 <= $e1));
            $desc = ($desc && ($e0 >= $e1));
            $e0 = $e1;
        }
        return ($asc || $desc);
    }

    /**
     * Calcul le maximum.
     * @param mixed $index Facultatif.
     * @return int
     */
    public function max($index = FALSE) : int
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
     * @param mixed $index Facultatif.
     * @return int
     */
    public function min($index = FALSE) : int
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
     * @return Collection
     */
    public function classe(string $class) : Collection
    {
        $this->_class = $class;
        return $this;
    }

    /**
     * Définie l'identifiant du tableau.
     * @param string $id
     * @return Collection
     */
    public function id(string $id) : Collection
    {
        $this->_id = $id;
        return $this;
    }

    /**
     * Affiche le tableau
     */
    public function print()
    {
        print_r($this->_array);
    }

    /**
     * Traduit le tableau de maximum deux dimensions en HTML.
     * @return string Tableau HTML.
     */
    public function html() : string
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
     * Recherche dichotomique d'une valeur dans un tableau numérique trié par ordre croissant.
     * @param int $val 
     * @return int Index de la valeur trouvée ou -1.
     */
    public function search_dichotomous(int $val) : int
    {
        $min = 0;
        $max = $this->count() - 1;
        $index = -1;

        if ($this[$min] === $val)
        {
            $index = $min;
        } 
        elseif ($this[$max] === $val)
        {
            $index = $max;
        }
        elseif ($this[$min] > $val)
        {
            $mid = 0;
            $max = $min; // On empêche la boucle.
        }
        elseif ($this[$max] < $val)
        {
            $mid = $max + 1;
            $max = $min; // On empêche la boucle.
        }
        else
        {
            $mid = 1;
        }

        if ($max - $min <= 1) 
        {
            $this->_dichotomous_index[$val] = ($index > -1) ? ($index) : ($mid);
            return $index;
        }

        while ($index === -1 && ($max - $min) > 1)
        {
            $mid = floor(($max + $min) / 2);
            if ($this[$mid] === $val)
            {
                $index = $mid;
            }
            elseif ($this[$mid] < $val)
            {
                $min = $mid;
            }
            else
            {
                $max = $mid;
            }
        }

        $this->_dichotomous_index[$val] = $index;
        if ($index === -1)
        {
            $this->_dichotomous_index[$val] = $mid;
            $this->_dichotomous_index[$val] = ($this[$mid] < $val) ? ($mid + 1) : ($mid);
        }

        return $index;
    }

    /**
     * Insert un nombre de façon ordonnée grâce à la recherche dichotomique.
     * @param int $val
     * @return Collection
     */
    public function insert_dichotomous(int $val) : Collection
    {
        if ($val >= $this[$this->count() -1])
        {
            return $this->splice($this->count(), 0, $val);
        }
        if ($val <= $this[0])
        {
            return $this->splice(0, 0, $val);
        }
        if (isset($this->_dichotomous_index[$val]) === FALSE)
        {
            $this->search_dichotomous($val);
        }
        $index = $this->_dichotomous_index[$val];
        $this->_dichotomous_index = [];
        return $this->splice($index, 0, $val);
    }

    /**
     * Surcharge Interface Countable
     * @return int
     */
    public function count() : int
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
    public function getIterator() : ArrayIterator
    {
        return new ArrayIterator($this->_array);
    }

    /**
     * Affiche le tableau de debug.
     * @return array
     */
    public function __debugInfo() : array
    {
        return $this->_array;
    }

    /**
     * Converti le tableau en HTML.
     * @return string
     */
    public function __toString() : string
    {
        return $this->html();
    }
}
?>