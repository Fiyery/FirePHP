<?php
class Table implements ArrayAccess, IteratorAggregate, Countable
{
    private $_id = '';
    private $_class = '';
    private $_header = [];
    private $_array;
    private $_parent = NULL;
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
     * @param string $index
     * @return mixed
     */
    public function get($index)
    {
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