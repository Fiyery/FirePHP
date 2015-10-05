<?php
class Smiley extends Dao
{
	// Fonction qui retourne tous les smileys.
	public static function get_all()
	{
        $smileys = Smiley::search();
        $list = array();
		if (count($smileys) > 0)
		{
			foreach ($smileys as $s)
			{
				$list[$s->Code] = $s->Image;
			}
		}
		return $list;
	}
}
?>