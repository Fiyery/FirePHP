<?php
class Drama extends Fiche
{
    // Récupère tous les animes.
    public static function get_all()
    {
    	$base = self::$_base;
    	$sql = 'SELECT * FROM drama d INNER JOIN fiche AS f ON f.IdFiche = a.IdFiche';
    	$result = $base->query($sql);
    	$objects = array();
    	if (is_array($result))
    	{
    	    foreach ($result as $a)
    	    {
    	    	$objects[] = new self($a);
    	    }
    	}
    	return $objects;
    }
    
    // Fonction qui retourne la card de la fiche.
    public function get_card($root)
    {
    	return '';
    }
}
?>