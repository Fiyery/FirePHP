<?php
class JeuSession extends Dao
{
	// Fonction qui retourne l'ensemble des objets tentative pour une session de jeu.
	public function get_tentatives()
	{
		return JeuTentative::search('IdJeuSession',$this->IdJeuSession,FALSE,NULL,NULL,array('ASC'=>array('Date')));
	}
	
	// Fonction qui retourne la session suivante.
	public function get_next_session()
	{
		$sql = "SELECT * FROM `jeusession` WHERE IdJeuSession > ".$this->IdJeuSession." AND IdJeu = ".$this->IdJeu." LIMIT 1";
		$base = Base::get_instance();
		$res = $base->query($sql,$base->select_base(__CLASS__));
		return (is_array($res)) ? (new self($res[0])) : (NULL);
	}
	
	// Fonction boolèenne qui retourne TRUE si la réponse est valide.
	public function win($answer)
	{
		$answer = String::format($answer, ' ');
		$answer = explode(' ', $answer);
		$words_session = explode(' ', String::format($this->Reponse, ' '));
		$good_need = ceil(count($words_session)*0.90);
		$find = 0;
		foreach ($words_session as $w)
		{
			if (in_array($w, $answer))
			{
				$find++;
				$key = array_search($w,$answer);
				unset($answer[$key]);
			}
		}
		return ($find >= $good_need);
	}
}
?>