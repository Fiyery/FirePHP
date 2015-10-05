<?php
class Categorie extends Dao
{
	// Fonction qui retourne le chemin du catégorie.
	public function get_way()
	{
	    $config = Config::get_instance();
		$separateur = ' '.$config->SeparateurCheminForum.' ';
		$way = $separateur.'<a href="?module=PageCategorie&id='.$this->IdCategorie.'">'.$this->Nom.'</a>';
		$way = '<a href="?module=PageIndex">Index du Forum</a>'.$way;
		return $way;
	}
	
	// Fonction qui retourne le nombre de messages et de sujets de la catégorie.
	public function get_stats()
	{
		$list_forums = Forum::search('IdCategorie',$this->IdCategorie);
		$nb_messages = 0;
		$nb_sujets = 0;
		$nb_vues = 0;
		foreach ($list_forums as $f)
		{
			$tmp = $f->get_stats();
			$nb_messages += $tmp['NbMessages'];
			$nb_sujets += $tmp['NbSujets'];
			$nb_vues += $tmp['NbVues'];
		}
		return array('NbMessages'=>$nb_messages,'NbSujets'=>$nb_sujets,'NbVues'=>$nb_vues);
	}
}
?>