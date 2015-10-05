<?php
class Membre extends Dao
{
	private $new_mp = FALSE;
	private $new_post = FALSE;
	private $groups = NULL; 
	
	public $pseudo_html = NULL;
	
	// Fonction qui retourne le lien de l'avatar.
	public function get_link_avatar()
	{
	    $config = Config::get_instance();
		$dir = $config->path->avatar;
		$site = Site::get_instance();
		$root_url = $site->get_root();
		$root_dir = $site->get_root(Site::DIR);
		$files = scandir($root_dir.$dir);
		sort($files,SORT_NUMERIC);
		$find = array();	
		$i = 0;
		$max = count($files);
		$id = 0;
		while ($id <= $this->IdMembre && $i < $max)
		{
			$f = $files[$i];
			if (is_dir($root_dir.$dir.$f) == FALSE)
			{
				$id = substr($f,0,strpos($f,'.'));
				if ($id == $this->IdMembre)
				{
					$find[] = $root_url.$dir.$f;
				}
				elseif (is_numeric($id) == FALSE)
				{
					$id = 0;
				}
			}
			$i++;
		}
		$nb = count($find);
		if ($nb == 0)
		{
			return $root_url.$dir.$config->AvatarParDefaut;
		}
		elseif ($nb == 1)
		{
			return $find[0];
		}
		else 
		{
			return $find;
		}
	}

	// Fonction qui retourne le code html de l'avatar du membre courrant.
	public function get_avatar($x=NULL,$y=NULL)
	{
	    $config = Config::get_instance();
		$avatar = $this->get_link_avatar();
		$avatar .= '?d='.time(); // Empêche l'image d'être en cache.
		$pseudo = $this->Pseudo;
		$max_height =  (is_int($y)) ? ($y) : ($config->HauteurAvatar);
		$max_width = (is_int($x)) ? ($x) : ($config->LargeurAvatar);
		return '<img src="'.$avatar.'" style="max-height:'.$max_height.'px;max-width:'.$max_width.'px;" class="avatar" title="Avatar de '.$pseudo.'"/>';
	}
	
	// Fonction qui retourne le pseudo formaté et mise en forme.
	public function get_pseudo()
	{
		if (is_null($this->pseudo_html))
		{
			$this->get_groups();
			if (is_array($this->groups))
			{
				reset($this->groups);
				$group = current($this->groups);
				foreach ($this->groups as $g)
				{
					if ($group->Hierarchie > $g->Hierarchie)
					{
						$group = $g;
					}
				}
			}
			$color = (isset($group)) ? ($group->Couleur) : ('#000');
			$this->pseudo_html = self::parse_pseudo($this->Pseudo, $color);
		}
		return $this->pseudo_html;
	}

	// Fonction qui test si le membre a lu tous les messages du forum et retourne un booléen.
	public function is_forum_read($forum,$last_message=NULL)
	{
		if (!is_object($forum) || ($last_message !== NULL && $last_message !== FALSE && !is_object($last_message)))
		{
			return FALSE;
		}
		// Si il n'y pas les droits de voir le forum, on considère qu'il l'a lu.
		if ($forum->is_visible($this->IdMembre) == FALSE)
		{
			return TRUE;
		}
		// On test si le dernier message est lu (90% de chance que ce ne soit pas le cas).
		if ($last_message === NULL)
		{
			$last_message = $forum->last_message();
		}
		if (is_object($last_message))
		{
			$subject_view = SujetVue::load(array($last_message->IdSujet,$this->IdMembre));
			if (is_object($subject_view) == FALSE || $subject_view->IdPost != $last_message->IdPost)
			{
				return FALSE;
			}
		}
		// On test tous les sujets du forum.
		$subjects = Sujet::search(array('IdForum'=>$forum->IdForum));
		if (count($subjects) > 0)
		{
			$view = TRUE;
			while (($s = current($subjects)) && $view)
			{
				$view = $this->is_subject_read($s);
				next($subjects);
			}
			if ($view == FALSE)
			{
				return FALSE;
			}
		}
		// On test tous les sous-forums.
		$sousforums = Forum::search(array('IdPere'=>$forum->IdForum));
		$view = TRUE;
		if (count($sousforums) > 0)
		{
			while (($f = current($sousforums)) && $view)
			{
				$view = $this->is_forum_read($f);
				next($sousforums);
			}
		}
		return $view;
	}
	
	// Fonction qui test si le membre a lu tous les messages du sujet et retourne un booléen.
	public function is_subject_read($subject,$last_message=NULL)
	{
		if (!is_object($subject) || ($last_message !== NULL && $last_message !== FALSE && !is_object($last_message)))
		{
			return FALSE;
		}
		$subject_view = SujetVue::load(array($subject->IdSujet,$this->IdMembre));
		
		if ($last_message === NULL)
		{
			$last_message = $subject->last_message();
		}
		if (is_object($last_message))
		{
			$subject_view = SujetVue::load(array($last_message->IdSujet,$this->IdMembre));
			if (is_object($subject_view) == FALSE || $subject_view->IdPost != $last_message->IdPost)
			{
				return FALSE;
			}
		}
		return TRUE;
	}

	// Fonction qui retourne les fiches d'un membre.
	public function get_fiches()
	{
		$sql = 'SELECT fiche.* FROM fichemembre, fiche WHERE fichemembre.IdFiche = fiche.IdFiche AND fichemembre.IdMembre = '.$this->IdMembre;
		$base = Base::get_instance();
		$res = $base->query($sql,$base->select_base('fiche'));
		$list = FALSE;
		if (is_array($res))
		{
			foreach ($res as $f)
			{
				$list[] = new Fiche($f);
			}
		}
		return $list;
	}
	
	// Fonction qui test si un pseudo existe ou non dans la base de données. Elle renvoie un booléen true si le pseudo existe et false dans le cas contraire.
	public static function pseudo_exists($pseudo)
	{
		return (count(self::search('Pseudo',$pseudo)) > 0);
	}
	
	// Fonction qui test si un main existe ou non dans la base de données. Elle renvoie un booléen true si le mail existe et false dans le cas contraire.
	public static function mail_exists($mail)
	{
		return (count(self::search('Mail',$mail)) > 0);
	}
	
	// Fonction qui retourne le code html de l'avatar d'un visiteur.
	public static function get_avatar_visitor($x=NULL,$y=NULL)
	{
	    $config = Config::get_instance();
		$dir = $config->path->avatar;
		$pseudo = 'Visiteur';
		$avatar = $dir.$config->AvatarParDefaut;
		$max_height =  (is_int($y)) ? ($y) : ($config->HauteurAvatar);
		$max_width = (is_int($x)) ? ($x) : ($config->LargeurAvatar);
		$avatar = Site::get_instance()->get_root().$avatar;
		return '<img src="'.$avatar.'" style="max-height:'.$max_height.';max-width:'.$max_width.';" class="avatar" title="Avatar de '.$pseudo.'"/>';
	}

	// Fonction qui retourne un booléan à true si le membre à un nouveau message.
	public function has_new_mp()
	{
		$messages = Message::search(array('IdDestinataire'=>$this->IdMembre,'Etat'=>0),NULL,TRUE);
		$this->new_mp = (count($messages) > 0);
		return $this->new_mp;
	}
	
	// Fonction qui retourne un booléan à true si le membre n'a pas lu tous les messages du forum.
	public function has_new_post()
	{
		$base = Base::get_instance();
		$sql = "
			SELECT MAX(IdPost), sujet.IdSujet, post.IdMembre
			FROM sujet, post 
			WHERE sujet.IdSujet = post.IdSujet 
			GROUP BY IdSujet 
			HAVING MAX(IdPost) NOT IN 
			(
				SELECT IdPost 
				FROM sujetvue
				WHERE IdMembre = ".$this->IdMembre." 
			); 
		";
		$post = $base->query($sql,$base->select_base('sujet'));
		$this->new_post = (is_array($post));
		return $this->new_post;
	}
	
	// Fonction qui retourne le top des players.
	public static function get_top_players()
	{
		return Membre::search(NULL,NULL,0,10,array('DESC'=>'PointCompetition'));
	}

	// Fonction qui retourne le top des posters sur le forum sous forme d'objet Membre avec l'attribut NbPost.
	public static function get_top_posters()
	{
		$sql = "
			SELECT COUNT(*) as NbPost, IdMembre
			FROM post
			GROUP BY IdMembre
			ORDER BY NbPost DESC;
		";
		$base = Base::get_instance();
		$res = $base->query($sql,$base->select_base('post'));
		$list = array();
		foreach ($res as $r)
		{
			$m = Membre::load($r['IdMembre']);
			if ($m !== FALSE)
			{
				$m->NbPost = $r['NbPost'];
				$list[] = $m;	
			}
 		}
 		return $list;
	}
	
	// Fonction qui retourne le top des créateurs de fiches.
	public static function get_top_fichers()
	{
		$sql = "
			SELECT COUNT(*) as NbFiche, IdMembre
			FROM fiche
			GROUP BY IdMembre
			ORDER BY NbFiche DESC;
		";
		$base = Base::get_instance();
		$res = $base->query($sql,$base->select_base('fiche'));
		$list = array();
		foreach ($res as $r)
		{
			$m = Membre::load($r['IdMembre']);
			if ($m !== FALSE)
			{
				$m->NbFiche = $r['NbFiche'];
				$list[] = $m;	
			}
 		}
 		return $list;
	}

	// Fonction qui retourne le nombre de post sur le forum.
	public function get_nb_post()
	{
		$sql = new Query('SELECT count(*) as Nb FROM post WHERE IdMembre = '.$this->IdMembre.';');
		$res = Base::get_instance()->query($sql);
		return ($res !== FALSE) ? ($res[0]['Nb']) : (0);
	}
	
	// Fonction qui retourne le nombre de post sur le forum.
	public function get_nb_fiche()
	{
		$sql = new Query('SELECT count(*) as Nb FROM fiche WHERE IdMembre = '.$this->IdMembre.';');
		$res = Base::get_instance()->query($sql);
		return ($res !== FALSE) ? ($res[0]['Nb']) : (0);
	}
	
	// Fonction qui retourne la card du membre.
	public function get_card()
	{
		$root = Site::get_instance()->get_root();
		$dir_image = Config::get_instance()->path->image;
		$crypt = new Crypt();
		$url = $root.'?module=PageProfil&amp;id='.$crypt->encrypt($this->IdMembre);
		$url_image = $this->get_link_avatar();
		$nb_post = $this->get_nb_post();
		$nb_post = ($nb_post > 1) ? ($nb_post.' posts') : ($nb_post.' post');
		$nb_fiche = $this->get_nb_fiche();
		$nb_fiche = ($nb_fiche > 1) ? ($nb_fiche.' fiches') : ($nb_fiche.' fiche');
		$nb_pc = ($this->PointCompetition > 1) ? ($this->PointCompetition.' PCs') : ($this->PointCompetition.' PC');
		$card = new Card($this->Pseudo,$url,$url_image);
		if ($this->DateNaissance == '0000-00-00')
		{
			$age = '';
		}
		else 
		{
			$age = new DateTime($this->DateNaissance);
			$age = $age->diff(new DateTime())->format('%Y').' ans';	
		}
		if ($this->Sexe == 1)
		{
			$age .= " <img src='".$root.$dir_image."design/icones/male.png' width='14px'/>"; 
		}
		elseif ($this->Sexe == 2)
		{
			$age .= " <img src='".$root.$dir_image."design/icones/female.png' width='14px'/>"; 
		}
		
		$card->add_field('Infos',$age);
		$card->add_field('Forum',$nb_post);
		$card->add_field('Wiki',$nb_fiche);
		$card->add_field('Dojo',$nb_pc);
		return $card->get_html();
	}

	// Fonction qui récupère les groupes du membre.
	public function get_groups()
	{
		if ($this->groups === NULL)
		{
			$sql = "SELECT g.* FROM groupe g INNER JOIN membregroupe AS mg ON mg.IdGroupe = g.IdGroupe WHERE IdMembre = ".$this->IdMembre;
			$result = self::$_base->cache(86400)->query($sql);
			if (is_array($result) == FALSE)
			{
				$this->groups = FALSE;
			}
			else 
			{
				$groups = array();
				foreach ($result as $r)
				{
					$groups[$r['Reference']] = new Groupe($r);
				}
				$this->groups = $groups;
			}
		}
		return $this->groups;
	}
	
	// Fonction booléenne qui retoure TRUE si le membre appartient au groupe.
	public function belong($name)
	{
		$groups = $this->get_groups();
		return (is_array($groups) && is_string($name) && array_key_exists($name,$groups)) ? (TRUE) : (FALSE);		
	}
	
	// Fonction qui retourne tous les pseudos des membres.
	public static function get_all_names()
	{
		$base = Base::get_instance();
		$result = array();
		$sql = 'SELECT Pseudo FROM '.strtolower(__CLASS__).';';
		$res = $base->query($sql,$base->select_base(__CLASS__));	
		if (is_array($res))
		{
			foreach ($res as $r)
			{
				$result[] = $r['Pseudo'];
			}
		}
		sort($result);
		return (count($result) > 0) ? ($result) : (FALSE);		
	} 
	
	// Fonction qui retourne le tableau d'objets Membre en fonction du pseudo.
	public static function get_by_name($names)
	{
		if (is_string($names) == FALSE && is_array($names) == FALSE)
		{
			return FALSE;
		}
		if (is_string($names))
		{
			$names = array($names);
		}
		$search = '("'.implode('","',$names).'")';
		$base = Base::get_instance();
		$sql = 'SELECT * FROM '.strtolower(__CLASS__).' WHERE Pseudo IN '.$search.';';
		$res = $base->query($sql,$base->select_base(__CLASS__));
		$objects = array();
		foreach ($res as $m)
		{
			$objects[] = new self($m);
		}
		return $objects;
	}
	
	// Fonction qui retourne tous les pseudos avec la couleur de groupe.
	public static function get_all_pseudos()
	{
		$query = new Query("
			SELECT IdMembre, Couleur
			FROM membregroupe mg, groupe g
			WHERE mg.IdGroupe = g.IdGroupe;
		");
		$base = Base::get_instance();
		$colors = $base->query($query);
		if (is_array($colors) == FALSE)
		{
			return FALSE;
		}
		$query = new Query("
			SELECT IdMembre, Pseudo
			FROM membre;
		");
		$base = Base::get_instance();
		$pseudos = $base->query($query);
		if (is_array($pseudos) == FALSE)
		{
			return FALSE;
		}
		$parses = array();
		foreach ($pseudos as $p)
		{
			$id = $p['IdMembre'];
			$find = FALSE;
			reset($colors);
			while ($find === FALSE && ($c = current($colors)))
			{
				if ($c['IdMembre'] == $id)
				{
					$find = $c;
				}
				next($colors);
			}
			if ($find === FALSE)
			{
				$color = '#000';
			}
			$parses[$id] = self::parse_pseudo($p['Pseudo'], $find['Couleur']);
		}
		return $parses;
	}
	
	// Fonction qui format un pseudo en fonction de la couleur du groupe.
	public static function parse_pseudo($pseudo,$color)
	{
		return '<span class="pseudo" style="color:'.$color.';">'.$pseudo.'</span>';
	}
	
	// Retourne les identifiants de groupe.
	public function get_group_ids()
	{
		$this->get_groups();
		$ids = array();
		if (is_array($this->groups))
		{
			foreach ($this->groups as $g)
			{
				$ids[] = $g->IdGroupe;
			}
		}
		return $ids;
	}
	
	// Retourne le nom du dossier de l'hébergement.
	public static function search_files_dir($pseudo)
	{
	    $base = self::$_base;
	    $list= $base->query('SELECT Pseudo, IdMembre FROM membre;');
	    $id = -1;
	    while ($id == -1 && ($m = current($list)))
	    {
	        if (String::format_url($m['Pseudo']) == $pseudo)
	        {
	            $id = $m['IdMembre'];
	        }
	        next($list);
	    }
	    return $id;	    
	}
	
	// Retourne la racine de l'hébergeur d'image.
	public static function get_root_heberg($format=Site::URL)
	{
	    $root = Site::get_instance()->get_root($format);
	    $dir = Config::get_instance()->path->ressource;
	    return $root.$dir.'files/';
	}
	
	// Retourne la bannière du membre.
	public function get_banniere()
	{
		$site = Site::get_instance();
		$config = Config::get_instance();
		$root_dir = $site->get_root(Site::DIR);
		$root_url = $site->get_root();
		$list = glob($root_dir.$config->path->image.'design/bannieres/*.jpg');
		// Ordre chronologique.
		$list[] = $list[0];
		unset($list[0]);
		$bannieres = array();
		$count = count($list);
		$id = ($this->IdBanniere > 0) ? ($this->IdBanniere) : ($count);
		$img = NULL;
		for($i=1; $i <= $count && $img == NULL; $i++)
		{
			if ($id == $i)
			{
				$img = str_replace($root_dir, $root_url, $list[$i]);
			}
		}
		return $img;
	}
	
	// Retourne la date de dernière connexion.
	public function get_last_connexion()
	{
		$result = self::$_base->query("SELECT Date FROM connexion WHERE IdMembre = ".$this->IdMembre." ORDER BY Date DESC LIMIT 1");
		if (is_array($result))
		{
			$date = new Date($result[0]['Date']);
			return $date->format(Date::FRENCH);
		}
		else
		{
			return NULL;
		}
	}
	
	// Retourne toutes les fiches de la liste du membre.
	public function get_listed_fiches()
	{
		$result = self::$_base->query("SELECT IdFiche FROM fichemembre WHERE IdMembre = ".$this->IdMembre);
		if (is_array($result))
		{
			$ids = [];
			foreach ($result as $r)
			{
				$ids[] = $r['IdFiche'];
			}
		}
		else
		{
			$ids = [];
		}
		return $ids;
	}
	
	// Met à jour les fiches de la liste du membre.
	public function update_list_fiche($owned, $not_owned)
	{
		if (count($owned) > 0)
		{
			$sql = "INSERT IGNORE INTO fichemembre VALUES ";
			$data = array();
			foreach ($owned as $o)
			{
				$data[] = '('.$o.','.$this->IdMembre.')';
			}
			$sql .= implode(',', $data).';';
			$result = self::$_base->query($sql);
		}
		if (count($not_owned) > 0)
		{
			$sql = "DELETE FROM fichemembre WHERE IdMembre = '".$this->IdMembre."' AND IdFiche IN (".implode(',', $not_owned).');';
			$result = self::$_base->query($sql);
		}
	}
	

}
?>