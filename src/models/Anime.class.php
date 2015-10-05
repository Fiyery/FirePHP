<?php
class Anime extends Fiche
{	
	// Fonction booléenne qui renvoit TRUE si l'anime existe.
	public static function is_exists($name)
	{
		$base = self::$_base;
		$name = str_replace("'","\'",$name);
		$sql = 'SELECT IdAnime FROM anime WHERE Nom = \''.$name.'\';';
		$res = $base->query($sql,$base->select_base(__CLASS__));
		return (is_array($res));
	}

	// Fonction qui retourne les images d'un anime.
	public function get_image()
	{
		if (isset($this->Image) == FALSE)
		{
		    $site = Site::get_instance();
		    $config = Config::get_instance();
			$dir = $site->get_root(Site::DIR).$config->path->image.'/fiches/';
			$file = 'anime-'.$this->IdAnime;
			$way = $site->get_root().$config->path->image.'/design/wiki/wikimanga-default.png';
			if (file_exists($dir.$file.'.jpg'))
			{
				$way = $site->get_root().$config->path->image.'/fiches/'.$file.'.jpg';
			}
			elseif (file_exists($dir.$file.'.png'))
			{
				$way = $site->get_root().$config->path->image.'/fiches/'.$file.'.png';
			}
			elseif (file_exists($dir.$file.'.gif'))
			{
				$way = $site->get_root().$config->path->image.'/fiches/'.$file.'.gif';
			}
			$this->Image = $way;
		}
		return $this->Image;
	}

	// Fonction qui retourne le nom des genres d'un anime.
	public function get_genres()
	{
		if (isset($this->Genres) == FALSE)
		{
			$base = Base::get_instance();
			$sql = 'SELECT genre.IdGenre, Nom FROM genre,animegenre WHERE IdAnime = '.$this->IdAnime.' AND genre.IdGenre = animegenre.IdGenre;';
			$res = $base->query($sql,$base->select_base(get_called_class()));
			$list = array();
			if (is_array($res))
			{
				foreach ($res as $r)
				{
					$list[$r['IdGenre']] = $r['Nom'];
				}
			}
			$this->Genres = $list;
		}
		return $this->Genres;
	}

	// Fonction qui retourne le nom des auteurs d'un anime.
	public function get_auteurs()
	{
		if (isset($this->Auteurs) == FALSE)
		{
			$base = Base::get_instance();
			$sql = 'SELECT Nom FROM animeauteur WHERE IdAnime = '.$this->IdAnime.';';
			$res = $base->query($sql,$base->select_base(get_called_class()));
			$list = array();
			if (is_array($res))
			{
				foreach ($res as $r)
				{
					$list[] = $r['Nom'];
				}
			}
			$this->Auteurs = $list;
		}
		return $this->Auteurs;
	}

	// Fonction qui retourne le nom des studios d'un anime.
	public function get_studios()
	{
		if (isset($this->Studios) == FALSE)
		{
			$base = Base::get_instance();
			$sql = 'SELECT Nom FROM animestudio WHERE IdAnime = '.$this->IdAnime.';';
			$res = $base->query($sql,$base->select_base(get_called_class()));
			$list = array();
			if (is_array($res))
			{
				foreach ($res as $r)
				{
					$list[] = $r['Nom'];
				}
			}
			$this->Studios = $list; 
		}
		return $this->Studios;
	}

	// Fonction qui retourne la moyenne des notes de l'anime et le nombre de votants.
	public function get_note()
	{
		if (isset($this->Note) == FALSE)
		{
			$base = Base::get_instance();
			$sql = 'SELECT Note FROM animenote WHERE IdAnime = '.$this->IdAnime.';';
			$res = $base->query($sql,$base->select_base(get_called_class()));
			$list = FALSE;
			if (is_array($res))
			{
				$sum = 0;
				$nb = count($res);
				foreach ($res as $r)
				{
					$sum += $r['Note'];
				}
				$average = number_format($sum/$nb,1);
				$this->NombreVote = $nb;
				$this->Note = $average;
			}
			else 
			{
				$this->NombreVote = 0;
				$this->Note = NULL;
			}
		}
		return ($this->Note !== NULL) ? (array('nb'=>$this->NombreVote,'average'=>$this->Note)) : (FALSE);	
	}

	// Fonction qui complète les informations de l'anime. Si $show à TRUE, alors on ne charge les données que pour les card.
	public function complete($show=FALSE)
	{
		if ($show == FALSE)
		{
			$this->get_note();
			$this->get_auteurs();
			$this->get_studios();
		  $this->get_genres();
		}
		$this->get_image();
		$this->get_categorie();
	}
	
	// Fonction qui retourne la catégorie de genre du manga.
	public function get_categorie()
	{
		if (isset($this->IdCategorieGenre) && isset($this->IdCategorieGenre) && is_string($this->IdCategorieGenre))
		{
		    if ($this->IdCategorieGenre == 0)
		    {
		        $this->CategorieGenre = NULL;
		    }
		    else 
		    {
		        $res = CategorieGenre::search('IdCategorieGenre',$this->IdCategorieGenre);
		        $this->CategorieGenre = (count($res) > 0) ? (new CategorieGenre($res[0])) : (NULL);
		    }
		    unset($this->IdCategorieGenre);
		}
		return (is_object($this->CategorieGenre)) ? ($this->CategorieGenre->Nom) : (NULL);
	}
	
	// Fonction qui retourne la card de la fiche.
	public function get_card($root)
	{
	    $url_image = $this->get_image();
	    $pos = strrpos($url_image,'.');
	    $url_image = substr($url_image, 0, $pos).'.min'.substr($url_image,$pos);
	    $url = $root."wiki/?module=PageFiche&amp;action=voir&amp;id=".$this->IdFiche;
	    $card = new Card($this->Nom, $url, $url_image);
	    $year = ($this->Annee == 0) ? ('-') : ($this->Annee);
	    $card->set_id($this->IdFiche);
	    $card->add_field('Année', $year);
	    $card->add_field('Nb d\'épisodes', $this->NbEpisodes);
	    $card->add_field('Catégorie', $this->get_categorie());
	    if (isset($this->Termine) && $this->Termine == 1)
	    {
	        $card->set_ribbon('Terminé');
	    }
	    return $card->get_html();
	}
	
	// Récupère tous les animes de tout le monde ou d'un membre.
	public static function get_all($id_member=NULL)
	{
	    $base = self::$_base;
	    if ($id_member == NULL)
	    {
	    	$sql = 'SELECT * FROM anime a 
	    			INNER JOIN fiche AS f ON f.IdFiche = a.IdFiche';
	    }
	    else
	    {
	    	$sql = 'SELECT * FROM anime a 
	    			INNER JOIN fiche AS f ON f.IdFiche = a.IdFiche 
	    			WHERE IdMembre = '.$id_member;
	    }
	    $result = $base->query($sql);
	    $objects = array();
	    foreach ($result as $a)
	    {
	        $objects[] = new self($a);
	    }
	    return $objects;
	}
	
	// Récupère tous les noms et id d'animes.
	public static function get_all_names()
	{
	    $base = self::$_base;
	   	$sql = 'SELECT Nom FROM anime';
	    $result = $base->query($sql);
	    $objects = array();
	    foreach ($result as $a)
	    {
	        $objects[] = $a['Nom'];
	  	}
	    return $objects;
	}
	
	// Récupère tous les animes du membre.
	public static function get_all_owned($id_member)
	{
		$base = self::$_base;
		$sql = '
			SELECT a.Nom, a.Note, a.Date, a.IdMembre, a.NbEpisodes, AVG(an.Note) Moyenne, a.IdFiche, a.Annee
			FROM
			(
				SELECT IdFiche from fichemembre WHERE IdMembre = '.$id_member.'   
			) f
			INNER JOIN			
			(
				SELECT a.IdAnime, a.Nom, a.NbEpisodes, an.Note, f.Date, f.IdMembre, f.IdFiche, a.Annee
				FROM animenote an
				INNER JOIN anime AS a ON an.IdAnime = a.IdAnime
				INNER JOIN fiche AS f ON f.IdFiche = a.IdFiche
				WHERE an.IdMembre = '.$id_member.' 
			) 
			AS a ON f.IdFiche = a.IdFiche
			INNER JOIN animenote AS an ON an.IdAnime = a.IdAnime
			GROUP BY a.IdAnime ORDER BY a.Nom';
		$result = $base->query($sql);
		$objects = array();
		foreach ($result as $a)
		{
			$objects[] = new self($a);
		}
		return $objects;
	}
	
	
}
?>