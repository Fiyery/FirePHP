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

		// $l = new FileLogger();
		// $l->path('./app/var/log')->granularity(FileLogger::TIME_MONTH);
		// $e = new Event("User", "Début de la récupération des utilisateurs", "error", ['args'=> func_get_args(), 'test'=>['1',2,3]]);
		// $e->fire();
		// $l->notify($e);
		// $l->write();

		
	}
}
?>