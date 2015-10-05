<?php
class Episode extends Dao
{
	// Retourne la liste des noms d'épisode non enregistrés dans la base de données.
	public static function find_not_existed($names)
	{
		$base = self::$_base;
		$sql = 'SELECT Nom FROM episode WHERE Nom IN (\''.implode("','", $names).'\')';
		$res = $base->query($sql, $base->select_base(__CLASS__));
		$existed = array();
		foreach ($res as $r)
		{
			$existed[] = $r['Nom'];
		}
		return array_diff($names, $existed);
	}
	
	// Retourne la liste des épisodes sorti une semaine avant la date du dernier épisode sorti.
	public static function get_last_week()
	{
		$base = self::$_base;
		$sql = '
			SELECT e.*, a.IdFiche FROM 
			(
				SELECT DateCreation FROM `episode` ORDER BY DateCreation DESC LIMIT 1
			) d, `episode` e 
			INNER JOIN `anime` AS a ON e.IdAnime = a.IdAnime
			WHERE e.DateCreation >= d.DateCreation - 604800
			ORDER BY e.DateCreation DESC;
		';
		$res = $base->query($sql, $base->select_base(__CLASS__));
		$objects = array();
		foreach ($res as $r)
		{
			$objects[] = new Episode($r);
		}
		return $objects;
	}
}
?>