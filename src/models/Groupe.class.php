<?php
class Groupe extends Dao
{
	/**
	 * Récupère l'ensemble des groupes et leurs liaisons avec les membres.
	 * @return boolean|array<Model>
	 */
	public static function get_all_link()
	{
		$sql = "SELECT * FROM groupe g, membregroupe mg WHERE mg.IdGroupe = g.IdGroupe";
		$q = new Query($sql);
		$result = Base::get_instance()->query($q);
		return Model::handle_result($result, __CLASS__);	
	}
}
?>