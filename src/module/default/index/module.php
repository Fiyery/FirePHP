<?php
use FirePHP\Controller\Module; 

class IndexModule extends Module
{
	public function action_index()
	{
		
	






		// $result = self::query()
		// 	->table(self::table_prefix().substr($prop, 3))
		// 	// ->select([
		// 	// 	self::table_prefix().substr($prop, 3).".id", 
		// 	// 	self::table_prefix().substr($prop, 3).".name", 
		// 	// ])
		// 	->join($table, "id_sheet_entity")
		// 	->order(["name" => "ASC"])
		// 	->run_array();








		// RESPONSE AJAX
		// $response = new ResponseAjax();
		// $response->data()->toto = 1;
		// $response->data()->add(["toto" => 1]);
		// $response->alert()->add_error("Paramètre invalide");
		// $response->send();

		// REPONSE
		// $this->response->alert()->add_error("Tests");
		// $this->response->status_code(403);
		// exit();

		// UPLOAD FILE
		// $_FILES["file"]["name"] = "ob_002ad3_tumblr-lwoqr74juo1qeiog9.png";
		// $_FILES["file"]["type"] = "image/png";
		// $_FILES["file"]["tmp_name"] = "../club-manga-v2/tmp.png";
		// $_FILES["file"]["error"] = 0;
		// $_FILES["file"]["size"] = 54531;
		// // Exigence.
		// $this->upload
		// 	->expect(Upload::FILE_TYPE_IMAGE)
		// 	->size(100000)
		// 	->exts(["jpg", "jpeg", "bmp", "png"]);
		// try 
		// {
		// 	$this->upload->load("file"); // Vérification.
		// 	$this->upload->move("avatar.png"); // Déplacement.
		// }
		// catch (Throwable $t)
		// {
		// 	Debug::show($t);
		// }

		// user::search();

		// COMPONENT
		// $c = new UlComponent();
		// $c->assign('elements', ['Element 1', 'Element 2', 'Element 3']);
		// $this->tpl->assign('component', $c->fetch());

		// EXEMPLE DE THREAD
		// $t = new Thread('.');
		// $t->add($this->config->path->root_url.'index/thread1/');
		// $t->add($this->config->path->root_url.'index/thread2/');
		// $t->add($this->config->path->root_url.'index/thread3/');
		// $this->session->open(User::load(1));
		// $t->run();
		// Debug::show($t->response(0));
		// Debug::show($t->response(1));
		// Debug::show($t->response(2));

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
		// mais seulement le principale qui retournera la réponse.
 		// $this->tpl->assign('ajax', 'test');
		// return FALSE;

		// GESTIONNAIRE DE LOG
		// $l = new FileLogger();
		// $l->path('./app/var/log')->granularity(FileLogger::TIME_MONTH);
		// $e = new Event("User", "Début de la récupération des utilisateurs", "error", ['args'=>  func_get_args(), 'test'=>['1',2,3]]);
		// $e->fire();
		// $l->notify($e);
		// $l->log();
		
		// EXEMPLE DE TEMPLATE DYNAMIQUE
		// $t = new Template('.');
		// $t->assign('onglet_buttons', ['Onglet 1', 'Onglet 2']);
		// $t->set_syntaxe(Template::SMARTY_STRICT);
		// $this->tpl->assign('var', $t->fetch('res/views/components/ul.tpl'));
	}

	public function action_thread1()
	{
		echo json_encode(["SESSION_ID"=>$this->session->user]);
		exit();
	}

	public function action_thread2()
	{
		sleep(1);
		echo json_encode(["SESSION_ID"=>$this->session->user]);
		exit();
	}

	public function action_thread3()
	{
		sleep(1);
		echo json_encode(["SESSION_ID"=>$this->session->user]);
		exit();
	}
}
?>