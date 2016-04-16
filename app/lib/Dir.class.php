<?php
/**
 * Dir est une classe qui rassemble des fonctions utiles sur les dossiers.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @copyright 2011-2015 Yoann Chaumin
 * @uses FireException
 */
class Dir
{
    /**
     * Chemin du dossier.
     * @var string
     */
    private $_dirname = NULL;
    
    /**
     * Constructeur.
     * @param string $dirname Chemin du dossier.
     * @throws FireException
     */
    public function __construct($dirname='.')
    {
        if (is_dir($dirname) == FALSE)
        {
            $d = debug_backtrace();
            throw new FireException('Dossier invalide', $d[0]['file'], $d[0]['line']);
        }
        $this->_dirname = (substr($dirname, -1) != '/') ? ($dirname.'/') : ($dirname);
    }
    
    /**
     * Supprime le dossier courant.
     * @return boolean
     */
    public function delete()
    {
        if (empty($this->_dirname))
        {
            return FALSE;
        }
        return $this->delete_rec($this->_dirname);
    }
    
    /**
     * Supprime récursivement tous les fichiers et dossier du dossier passé en paramètre.
     * @param string $dir Chemin du dossier à supprimer.
     * @return boolean
     */
    private function delete_rec($dir)
    {
        $list = array_diff(scandir($dir), array('..', '.'));
        foreach ($list as $f)
        {
            if (is_dir($dir.$f))
            {
                $this->delete_rec($dir.$f.'/');
            }
            else
            {
                unlink($dir.$f);
            }
        }
        return unlink($dir);
    } 
    
    /**
     * Retourne la taille d'un dossier.
     * @return int Taille do dossier en octet.
     */
    public function size()
    {
        $size = 0;
        $list_dirs = array($this->_dirname);
        $i = 0;
        while(isset($list_dirs[$i]))
        {
        	$d = $list_dirs[$i];
        	$list = array_diff(scandir($d), array('..', '.'));
        	foreach ($list as $f)
        	{
        		if (is_dir($d.$f))
        		{
        			$list_dirs[] = $d.$f.'/';
        		}
        		else
        		{
        			$size += filesize($d.$f);
        		}
        	}
        	$i++;
        }
        return $size;
    }
    
    /**
     * Retourne la liste des éléments contenus par le dossier.
     * @return array Liste des éléments du dossier sans le dossier courant et parent.
     */
    public function scan()
    {
        return array_diff(scandir($this->_dirname), array('..', '.'));
    }
    
    /**
     * Retourne la liste de tout les éléments contenus par le dossier et ses sous dossiers.
     * @return array Liste des éléments du dossier et sous dossier sans le dossier courant et parent.
     */
    public function scan_rec()
    {
    	$list_dir = array($this->get());
    	$list = array();
    	$i = 0;
    	while (isset($list_dir[$i]))
    	{
    	    $d = $list_dir[$i];
    	    $content = array_diff(scandir($d), array('..', '.'));
    	    foreach ($content as $c)
    	    {
    	        if (is_dir($d.$c))
    	        {
    	            $c .= '/';
    	            $list_dir[] = $d.$c;
    	        }
    	        $list[] = $d.$c;
    	    }
    	    unset($list_dir[$i]);
    	    $i++;
    	}
    	return $list;
    }
    
    /**
     * Retourne le chemin du dossier.
     * @return string
     */
    public function get()
    {
        return $this->_dirname;
    }
}
?>