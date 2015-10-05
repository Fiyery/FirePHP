<?php
class Post extends Dao
{
	
// Fonction qui retourne le nombre de messages pour un membre.
	public static function NbMessages($idmembre=NULL)
	{
		if (!is_numeric($idmembre))
		{
			return FALSE;
		}
		$messages = Post::search('IdMembre',$idmembre);
		return (count($messages));		
	}
		
}
?>