<?php
class FicheCritique extends Dao
{
    public static function get_last_critiques()
    {
        $req = "
            SELECT * FROM (
            SELECT * FROM fichecritique ORDER BY Date DESC LIMIT 0, 10
            ) critique INNER JOIN (
            SELECT Nom, IdFiche FROM Anime 
            UNION
            SELECT Nom, IdFiche FROM Manga 
            ) AS name ON name.IdFiche = critique.IdFiche; 
           ";
        $result = self::$_base->query($req);
        if (is_array($result) == FALSE) 
        {
            return array(); 
        }
        $list = array();
        foreach ($result as $r)
        {
            $list[] = new FicheCritique($r);
        }
        return $list;
    }
}
?>