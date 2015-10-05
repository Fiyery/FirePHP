<?php
class Sujet extends Dao
{
	// Fonction qui retourne le dernier message d'un sujet.
	public function last_message()
	{
		$messages = Post::search('IdSujet',$this->IdSujet);
		if (count($messages) > 0)
		{
			$nbmessages = count($messages);
			if ($nbmessages > 1)
			{
				$idmax = $messages[0]->IdPost;
				$case = 0;
				$i = $nbmessages - 1;
				while ($i >= 0)
				{
					if ($messages[$i]->IdPost > $idmax)
					{
						$idmax = $messages[$i]->IdPost;
						$case = $i;
					}
					$i--;
				}
				$derniermessage = $messages[$case];
			}
			else
			{
				$derniermessage = $messages[0];
			}
		}
		else
		{
			$derniermessage = FALSE;
		}
		return $derniermessage;
	}
	
	// Fonction qui retourne le chemin du sujet.
	public function get_way()
	{
	    $config = Config::get_instance();
		$separateur = ' '.$config->SeparateurCheminForum.' ';
		$way = $separateur.'<a href="?module=PageSujet&id='.$this->IdSujet.'">'.$this->Titre.'</a>';
		$forum = Forum::load($this->IdForum);
		$way = $forum->get_way().$way;
		return $way;
	}
}
?>