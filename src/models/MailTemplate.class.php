<?php
class MailTemplate extends Dao
{
	// Remplace une variable par sa valeur.
	public function bind($name, $value)
	{
		$this->Contenu = str_replace('$'.$name, $value, $this->Contenu);
	}
}
?>