<?php
class Index extends Module
{
	public function action_index()
	{
		// Gestion des dates
		// $d1 = new Date();
		// $d2 = new Date('2016-11-01');
		// Debug::show($d1->diff($d2)->is_later());
		// Debug::show($d1->format(Date::FRENCH));
		// $d1->add('month', '-2');
		// Debug::show($d1->format(Date::FRENCH));
		
		// Exemple de requête SQL.
		// $q = new Query($this->base, 'user', 'user');
		// Debug::show($q->table('user')->join('user_group')->order('name', 'DESC')->sql());
		
		// Pour l'ajax, retourner FALSE pour ne pas récupérer le template du module 
		// mais seulement le principale qui retournera la réponse.
 		// $this->tpl->assign('ajax', 'test');
		// return FALSE;

		// Gestionnaire de Log 
		// $l = new FileLogger();
		// $l->path('./app/var/log')->granularity(FileLogger::TIME_MONTH);
		// $e = new Event("User", "Début de la récupération des utilisateurs", "error", ['args'=>  func_get_args(), 'test'=>['1',2,3]]);
		// $e->fire();
		// $l->notify($e);
		// $l->write();
		
		// Exemple Template dynamique
		// $t = new Template('.');
		// $t->assign('onglet_buttons', ['Onglet 1', 'Onglet 2']);
		// $t->set_syntaxe(Template::SMARTY_STRICT);
		// $this->tpl->assign('var', $t->fetch('res/views/components/ul.tpl'));
	}
}
?>