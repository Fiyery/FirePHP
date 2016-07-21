<?php
class Index extends Module
{
	public function action_index()
	{
		//$q = new Query($this->base, 'user', 'user');
		/*
		$q = new Query();
		Debug::show($q->table('user')->join('user_group')->order('name', 'DESC')->sql());
		*/
		
		// Pour l'ajax, retourner FALSE pour ne pas récupérer le template du module 
		// mais seulement le principale qui retournera la réponse.
 		// $this->tpl->assign('ajax', 'test');
		// return FALSE;
	}
}
?>