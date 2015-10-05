<?php
class Forum extends Dao
{
	private $access = array();
	
	// Fonction qui retourne un tableau de deux cases avec le nombre de sujet en première case et le nombre de messages en deuxième.
	public function get_stats()
	{
		$list_forums = array_merge(array(
			$this
		), $this->get_sousforum());
		$list_sujets = array();
		foreach($list_forums as $f)
		{
			$sujets = Sujet::search('IdForum', $f->IdForum);
			$list_sujets = array_merge($list_sujets, $sujets);
		}
		if (count($list_sujets) > 0)
		{
			$tab['NbSujets'] = count($list_sujets);
			$tab['NbVues'] = 0;
			$count = 0;
			foreach($list_sujets as $s)
			{
				$messages = Post::search('IdSujet', $s->IdSujet);
				$count = $count + count($messages);
				$tab['NbVues'] += $s->Vues;
			}
			$tab['NbMessages'] = $count;
		}
		else
		{
			$tab['NbSujets'] = 0;
			$tab['NbMessages'] = 0;
			$tab['NbVues'] = 0;
		}
		return $tab;
	}

	// Fonction qui retourne les sous-forums du forum courrant.
	public function get_sousforum()
	{
		$list_forums = array();
		$forums = Forum::search('IdPere', $this->IdForum);
		if (count($forums) > 0)
		{
			$list_forums = $forums;
			foreach($forums as $f)
			{
				$list_forums = array_merge($list_forums, $f->get_sousforum());
			}
		}
		return $list_forums;
	}

	// Fonction qui retourne le dernier message d'un forum.
	public function last_message()
	{
		$list_forums = array_merge(array(
			$this
		), $this->get_sousforum());
		$list_sujets = array();
		foreach($list_forums as $f)
		{
			$sujets = Sujet::search('IdForum', $f->IdForum);
			$list_sujets = array_merge($list_sujets, $sujets);
		}
		$list_last_messages = array();
		foreach($list_sujets as $s)
		{
			$mes = $s->last_message();
			if ($mes !== FALSE)
			{
				$list_last_messages[] = $mes;
			}
		}
		$nbmessages = (isset($list_last_messages)) ? (count($list_last_messages)) : (0);
		if ($nbmessages > 1)
		{
			$idmax = $list_last_messages[0]->IdPost;
			$case = 0;
			$i = $nbmessages - 1;
			while($i >= 0)
			{
				if ($list_last_messages[$i]->IdPost > $idmax)
				{
					$idmax = $list_last_messages[$i]->IdPost;
					$case = $i;
				}
				$i --;
			}
			$last_message = $list_last_messages[$case];
		}
		elseif ($nbmessages == 1)
		{
			$last_message = $list_last_messages[0];
		}
		else
		{
			$last_message = FALSE;
		}
		return $last_message;
	}

	// Fonction qui retourne les droits d'un membre sur un forum.
	public function get_acces($id_membre = 0)
	{
		if (is_numeric($id_membre) == FALSE)
		{
			return NULL;
		}
		if ($id_membre == 0)
		{
			$id_groupe = 0;
		}
		else
		{
			$membre_groupe = MembreGroupe::search(array(
				'IdMembre' => $id_membre
			));
			if (count($membre_groupe) == 0)
			{
				return FALSE;
			}
			else
			{
				$id_groupe = $membre_groupe[0]->IdGroupe;
			}
		}
		if (isset($this->access[$id_groupe]) == FALSE)
		{
			$droit = DroitForum::load(array(
				$this->IdForum,
				$id_groupe
			));
			if (is_object($droit) == FALSE)
			{
				$this->access[$id_groupe] = FALSE;
			}
			else
			{
				$this->access[$id_groupe]['View'] = $droit->Voir;
				$this->access[$id_groupe]['Read'] = $droit->Lire;
				$this->access[$id_groupe]['Answer'] = $droit->Repondre;
				$this->access[$id_groupe]['Create'] = $droit->Creer;
				$this->access[$id_groupe]['Manage'] = $droit->Moderer;
			}
		}
		return $this->access[$id_groupe];
	}

	// Fonction qui retourne un booléen pour la vue du forum en fonction droit du membre.
	public function is_visible($id_membre = 0)
	{
		$droits = $this->get_acces($id_membre);
		if (is_array($droits) == FALSE)
		{
			return FALSE;
		}
		return ($droits['View'] == 1) ? (TRUE) : (FALSE);
	}

	// Fonction qui retourne un booléen pour la lecture des sujets en fonction droit du membre.
	public function is_readable($id_membre = 0)
	{
		$droits = $this->get_acces($id_membre);
		if (is_array($droits) == FALSE)
		{
			return FALSE;
		}
		return ($droits['Read'] == 1) ? (TRUE) : (FALSE);
	}

	// Fonction qui retourne un booléen pour la réponse des sujets en fonction droit du membre.
	public function is_answerable($id_membre = 0)
	{
		$droits = $this->get_acces($id_membre);
		if (is_array($droits) == FALSE)
		{
			return FALSE;
		}
		return ($droits['Answer'] == 1) ? (TRUE) : (FALSE);
	}

	// Fonction qui retourne un booléen pour la création des sujets en fonction droit du membre.
	public function is_creable($id_membre = 0)
	{
		$droits = $this->get_acces($id_membre);
		if (is_array($droits) == FALSE)
		{
			return FALSE;
		}
		return ($droits['Create'] == 1) ? (TRUE) : (FALSE);
	}

	// Fonction qui retourne un booléen pour la modération des sujets en fonction droit du membre.
	public function is_manageable($id_membre = 0)
	{
		$droits = $this->get_acces($id_membre);
		if (is_array($droits) == FALSE)
		{
			return FALSE;
		}
		return ($droits['Manage'] == 1) ? (TRUE) : (FALSE);
	}

	// Fonction qui retourne le chemin du forum.
	public function get_way()
	{
	    $config = Config::get_instance();
		$separateur = ' '.$config->SeparateurCheminForum.' ';
		$forum = $this;
		$way = $separateur . '<a href="?module=PageForum&id=' . $forum->IdForum . '">' . $forum->Nom . '</a>';
		while($forum->IdPere != 0)
		{
			$forum = Forum::load($forum->IdPere);
			$way = $separateur . '<a href="?module=PageForum&id=' . $forum->IdForum . '">' . $forum->Nom . '</a>' . $way;
		}
		$categorie = Categorie::load($forum->IdCategorie);
		$way = $categorie->get_way() . $way;
		return $way;
	}

	// Fonction qui retourne l'ensemble des derniers messages du forum avec leurs caractéristiques.
	public static function get_all_last_messages()
	{
		$query = new Query("
		SELECT IdForum, IdPost DernierIdPost, IdSujet DernierIdSujet, IdMembre DernierIdMembre, Titre DernierTitre, Date DernierDate
		FROM
		(
		    SELECT p.IdPost, p.Contenu, p.IdSujet, p.IdMembre, s.Titre, s.Vues, p.Date, s.IdForum
		    FROM 
		    (
		        SELECT * FROM post ORDER BY Date DESC
		    ) p
		    , sujet s
		    WHERE p.IdSujet = s.IdSujet
		    GROUP BY p.IdSujet
		    ORDER BY Date DESC
		) s
		GROUP BY IdForum
		");
		$result = Base::get_instance()->query($query);
		if (is_array($result) == FALSE)
		{
			return FALSE;
		}
		return $result;
	}

	// Fonction qui retourne l'ensemble des forums et les catégories avec leurs caractéristiques.
	public static function get_all_index_info()
	{
		$categories = array();
		$forums = array();
		$query = new Query("
		SELECT f.IdForum,f.Nom ForumNom, f.Ordre ForumOrdre, f.IdPere, f.IdCategorie, f.Description, c.Nom CategorieNom, c.Ordre CategorieOrdre, COUNT(s.IdSujet) NbSujets, SUM(s.Vues) NbVues, SUM(s.NbPosts) NbMessages
		FROM forum AS f
		LEFT JOIN categorie AS c ON f.IdCategorie = c.IdCategorie
		LEFT JOIN 
		(
		    SELECT s.IdSujet, s.Vues, COUNT(*) NbPosts, s.IdForum
		    FROM sujet s, post p
		    WHERE s.IdSujet = p.IdSujet
		    GROUP BY s.IdSujet
		)
		AS s ON s.IdForum = f.IdForum 
		GROUP BY f.IdForum
		ORDER BY c.Ordre, f.Ordre;
		");
		$result = Base::get_instance()->query($query);
		if (is_array($result) == FALSE)
		{
			return FALSE;
		}
		$sub_forums = array();
		foreach($result as $r)
		{
			if ($r['IdCategorie'] != 0 && array_key_exists($r['IdCategorie'], $categories) == FALSE)
			{
				$categories[$r['IdCategorie']] = new Categorie(array(
					'IdCategorie' => $r['IdCategorie'],
					'Nom' => $r['CategorieNom'],
					'Ordre' => $r['CategorieOrdre'],
					'NbSujets' => 0,
					'NbVues' => 0,
					'NbMessages' => 0,
					'Forums' => array()
				));
			}
			if ($r['IdPere'] == 0)
			{
				$f = new Forum(array(
					'IdForum' => $r['IdForum'],
					'Nom' => $r['ForumNom'],
					'Ordre' => $r['ForumOrdre'],
					'IdCategorie' => $r['IdCategorie'],
					'Description' => $r['Description'],
					'NbSujets' => ($r['NbSujets'] != NULL) ? ($r['NbSujets']) : (0),
					'NbVues' => ($r['NbVues'] != NULL) ? ($r['NbVues']) : (0),
					'NbMessages' => ($r['NbMessages'] != NULL) ? ($r['NbMessages']) : (0),
					'IdPere' => $r['IdPere'],
					'SousForums' => array()
				));
				$forums[$f->IdForum] = $f;
			}
			else
			{
				$f = new Forum(array(
					'IdForum' => $r['IdForum'],
					'Nom' => $r['ForumNom'],
					'Ordre' => $r['ForumOrdre'],
					'NbSujets' => ($r['NbSujets'] != NULL) ? ($r['NbSujets']) : (0),
					'NbVues' => ($r['NbVues'] != NULL) ? ($r['NbVues']) : (0),
					'NbMessages' => ($r['NbMessages'] != NULL) ? ($r['NbMessages']) : (0),
					'IdPere' => $r['IdPere']
				));
				$sub_forums[$f->IdForum] = $f;
			}
		}
		$categories = array_values($categories);
		while(count($sub_forums) > 0)
		{
			while((list ($id, $s) = each($sub_forums)))
			{
				$find = FALSE;
				reset($forums);
				$id_pere = $s->IdPere;
				while($find == FALSE && ($f = current($forums)))
				{
					if ($id_pere == $f->IdForum || array_key_exists($id_pere, $f->SousForums))
					{
						$f->SousForums[$id] = $s;
						$f->NbSujets += $s->NbSujets;
						$f->NbMessages += $s->NbMessages;
						$f->NbVues += $s->NbVues;
						$find = TRUE;
					}
					next($forums);
				}
				if ($find)
				{
					unset($sub_forums[$id]);
				}
			}
		}
		return Forum::get_all_categorie_complete($categories, $forums);
	}

	// Fonction qui permet de faire le calcul des catégories après la fonction get_all_forums
	private static function get_all_categorie_complete($categories, $forums)
	{
		if (count($forums) == 0)
		{
			return $categories;
		}
		while(($c = current($categories)))
		{
			foreach($forums as $f)
			{
				if ($f->IdCategorie == $c->IdCategorie)
				{
					$c->Forums[] = $f;
					$c->NbVues += $f->NbVues;
					$c->NbSujets += $f->NbSujets;
					$c->NbMessages += $f->NbMessages;
				}
			}
			next($categories);
		}
		return $categories;
	}

	// Fonction qui détermine quel forum contient des sujets non-lus par le membre.
	public static function get_forum_not_read_by($id)
	{
		$query = new Query("
		SELECT p.IdSujet, IdPost, IdForum
		FROM
		(
		    SELECT * FROM post ORDER BY Date DESC, IdPost DESC
		) p, sujet s
		WHERE s.IdSujet = p.IdSujet
		GROUP BY p.IdSujet
		HAVING CONCAT(IdSujet,'|',IdPost) NOT IN 
		(
			SELECT CONCAT(IdSujet,'|',IdPost) 
			FROM sujetvue     
			WHERE IdMembre = " . $id . "
		)
		");
		$result = Base::get_instance()->query($query);
		if (is_array($result) == FALSE)
		{
			return array();
		}
		$id_forums = array();
		foreach($result as $r)
		{
			if (in_array($r['IdForum'], $id_forums) == FALSE)
			{
				$id_forums[] = $r['IdForum'];
			}
		}
		return $id_forums;
	}
}
?>