<?php
class Jeu extends Dao
{
	// Fonction qui retourne l'objet session d'un jeu. Si pas de date renseignée, c'est la partie courant d'un jeu qui sera retournée.
	public function get_session($date=NULL)
	{
		$current_date = ($date != NULL) ?($date) : (Date::get_now());
		$sql = 'SELECT * FROM jeusession WHERE IdJeu = '.$this->IdJeu.' AND '.$current_date.' >= DateDebut AND DateFin >= '.$current_date.';';
		$base = Base::get_instance();
		$res = $base->query($sql,$base->select_base('jeusession'));
		return (is_array($res)) ? (new JeuSession($res[0])) : (NULL);
	}
	
	// Fonction qui retourne le dernier objet session d'un jeu.
	public function get_last_session()
	{
		$sql = 'SELECT * FROM jeusession WHERE IdJeu = '.$this->IdJeu.' ORDER BY DateDebut DESC LIMIT 1;';
		$base = Base::get_instance();
		$res = $base->query($sql,$base->select_base('jeusession'));
		return (is_array($res)) ? (new JeuSession($res[0])) : (NULL);
	}
	
	// Fonction qui format les reponses données par les utilisateurs au même format que les réponses du jeu.
	public static function parse_answer($word)
	{
		$word = strtr($word,"ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ","aaaaaaaaaaaooooooooooooeeeeeeeecciiiiiiiiuuuuuuuuynn");
		$word = preg_replace('/([^\w\s]*)/i','',$word);
		$word = str_replace('  ',' ',$word);
		$word = strtolower($word);
		return $word;
	}
}
?>