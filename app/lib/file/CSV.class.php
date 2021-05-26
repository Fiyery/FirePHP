<?php
namespace FirePHP\File;

use FirePHP\Exception\Exception;

/**
 * Helper pour la lecture des fichiers CSV.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 */
class CSV
{
    /**
     * Chemin du fichier.
     * @var string
     */
    private $_file = NULL;

    /**
     * Descripteur du fichier.
     * @var Ressource
     */
    private $_handle = NULL;

    /**
     * Délimiteur entre chaque information.
     * @var string
     */
    private $_delimiter = ';';

    /**
     * Enête du CSV.
     * @var string[]
     */
    private $_header = [];

    /**
     * Contructeur.
     * @param string $file Chemin du fichier.
     */
    public function __construct($file)
    {
        if (!is_string($file) && !file_exists($file) && !is_readable($file))
        {
            throw new Exception("Invalid file");
        }
        $this->_file = str_replace('\\', '/', $file);
        $this->_handle = fopen($this->_file, "r");
    }

    /**
     * Définie et récupère le délimiteur CSV.
     * @param string $delimiter Délimiteur entre chaque information.
     */
    public function delimiter($delimiter=NULL)
    {
        if (is_string($delimiter) && $delimiter != '')
        {
            $this->_delimiter = $delimiter;
        }
        return $this->_delimiter;
    }

    /**
     * Retourne les informations de la lignes sous formes de tableau.
     * @return array 
     */
    public function current()
    {
        $begin = $this->tell();
        $line = fgetcsv($this->_handle, 0, $this->_delimiter);
        $this->move($begin-$this->tell());
        return $this->_parse($line);
    }

    /**
     * Retourne la position du curseur.
     * @return int 
     */
    public function tell()
    {
        return ftell($this->_handle);
    }

    /**
     * Deplace la position du curseur.
     * @param int Déplacement en caractère.
     * @return int 
     */
    public function move($pos)
    {
        if ($this->tell() + $pos >= 0)
        {
            return fseek($this->_handle, $pos, SEEK_CUR);
        }
        return FALSE;
    }

    /**
     * Définie la position du curseur.
     * @param int Position à rejoindre.
     * @return int 
     */
    public function set($pos)
    {
        return fseek($this->_handle, $pos, SEEK_SET);
    }

    /**
     * Retourne l'élément courant et déplace la position du curseur sur la ligne suivante.
     * @return array 
     */
    public function next()
    {
        return $this->_parse(fgetcsv($this->_handle, 0, $this->_delimiter));
    }

    /**
     * Déplace l'élément à la fin du fichier.
     * @return int 
     */
    public function end()
    {
        return fseek($this->_handle, 0, SEEK_END);
    }

    /**
     * Déplace l'élément au début du fichier.
     * @return int 
     */
    public function begin()
    {
        return fseek($this->_handle, 0, SEEK_SET);
    }

    /**
     * Défini si le CSV a un entête.
     */
    public function header()
    {
        if (count($this->_header) === 0)
        {
            $this->_header = $this->next();
        }
        return $this->_header;
    }

    /**
     * Formate le retour de la ligne avec l'entête s'il est défini.
     * @return array
     */
    private function _parse($array)
    {
        $count = count($this->_header);
        return ($count > 0 && $count === count($array)) ? (array_combine($this->_header, $array)) : ($array);
    }
}
?>