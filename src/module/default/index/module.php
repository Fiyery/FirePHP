<?php
class IndexModule extends Module
{
	public function action_index()
	{
		// GESTION DES HOOKS
		// $hook = new Hook();
		// require(__DIR__.'/../debugbar/module.php');
		// require(__DIR__.'/../erreur/module.php');
		// $hook->add(new DebugBar($this->services()));
		// $hook->add(new Erreur($this->services()));
		// $hook->add(new Index($this->services()));
		// $hook->notify(new Event('debug'));
		
		// GESTION DES DATES
		// $d1 = new Date();
		// $d2 = new Date('2016-11-01');
		// Debug::show($d1->diff($d2)->is_later());
		// Debug::show($d1->format(Date::FRENCH));
		// $d1->add('month', '-2');
		// Debug::show($d1->format(Date::FRENCH));
		
		// REQUETE SQL
		// $q = new Query($this->base, 'user', 'user');
		// Debug::show($q->table('user')->join('user_group')->order('name', 'DESC')->sql());
		
		// POUR L'AJAX, RETOURNER FALSE POUR NE PAS APPELER LE TEMPLATE DU MODULE 
		// mais seulement le principale qui retournera la r�ponse.
 		// $this->tpl->assign('ajax', 'test');
		// return FALSE;

		// GESTIONNAIRE DE LOG
		// $l = new FileLogger();
		// $l->path('./app/var/log')->granularity(FileLogger::TIME_MONTH);
		// $e = new Event("User", "D�but de la r�cup�ration des utilisateurs", "error", ['args'=>  func_get_args(), 'test'=>['1',2,3]]);
		// $e->fire();
		// $l->notify($e);
		// $l->log();
		
		// EXEMPLE DE TEMPLATE DYNAMIQUE
		// $t = new Template('.');
		// $t->assign('onglet_buttons', ['Onglet 1', 'Onglet 2']);
		// $t->set_syntaxe(Template::SMARTY_STRICT);
		// $this->tpl->assign('var', $t->fetch('res/views/components/ul.tpl'));
	}
}
?>