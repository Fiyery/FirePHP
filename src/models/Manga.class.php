<?php
class Manga extends Fiche
{
	// Fonction booléenne qui renvoit TRUE si le manga existe.
	public static function is_exists($name)
	{
		$base = self::$_base;
		$name = str_replace("'","\'",$name);
		$sql = 'SELECT IdManga FROM manga WHERE Nom = \''.$name.'\';';
		$res = $base->query($sql,$base->select_base(__CLASS__));
		return (is_array($res));
	}

	// Fonction qui retourne les images d'un manga.
	public function get_image()
	{
		if (isset($this->Image) == FALSE)
		{
		    $site = Site::get_instance();
		    $config = Config::get_instance();
			$dir = $site->get_root(Site::DIR).$config->path->image.'/fiches/';
			$file = 'manga-'.$this->IdManga;
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

	// Fonction qui retourne le nom des genres d'un manga.
	public function get_genres()
	{
		if (isset($this->Genres) == FALSE)
		{
			$base = Base::get_instance();
			$sql = 'SELECT Nom FROM genre,mangagenre WHERE IdManga = '.$this->IdManga.' AND genre.IdGenre = mangagenre.IdGenre;';
			$res = $base->query($sql,$base->select_base(get_called_class()));
			$list = array();
			if (is_array($res))
			{
				foreach ($res as $r)
				{
					$list[] = $r['Nom'];
				}
			}
			$this->Genres = $list;
		}
		return $this->Genres;
	}

	// Fonction qui retourne la moyenne des notes de le manga et le nombre de votants.
	public function get_note()
	{
		if (isset($this->Note) == FALSE)
		{
			$base = Base::get_instance();
			$sql = 'SELECT Note FROM manganote WHERE IdManga = '.$this->IdManga.';';
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

	// Fonction qui retourne la catégorie de genre du manga.
	public function get_categorie()
	{
		if (isset($this->IdCategorieGenre) && isset($this->IdCategorieGenre) && is_string($this->IdCategorieGenre))
		{
			$res = CategorieGenre::search('IdCategorieGenre',$this->IdCategorieGenre);
			$this->CategorieGenre = (count($res) > 0) ? (new CategorieGenre($res[0])) : (NULL);	
			unset($this->IdCategorieGenre);
		}
		return $this->CategorieGenre->Nom;
	}
	
	// Fonction qui complète les informations du manga. Si $show à TRUE, alors on ne charge les données que pour les card.
	public function complete($show=FALSE)
	{
		if ($show == FALSE)
		{
			$this->get_note();
		}
		$this->get_image();
		$this->get_genres();
		$this->get_categorie();
	}
	
	// Fonction qui retourne la card de la fiche.
	public function get_card($root)
	{
		$url_image = $this->get_image();
		$pos = strrpos($url_image,'.');
		$url_image = substr($url_image, 0, $pos).'.min'.substr($url_image,$pos);
		$url = $root."wiki/?module=PageFiche&amp;action=voir&amp;id=".$this->IdFiche;
		$card = new Card($this->Nom, $url, $url_image);
		$year = ($this->AnneeVF == 0) ? ('-') : ($this->AnneeVF);
		$card->set_id($this->IdFiche);
		$card->add_field('Année', $year);
		$card->add_field('Nb de tomes', $this->NbTomesVF);
		$card->add_field('Catégorie', $this->get_categorie());
		if (isset($this->Termine) && $this->Termine == 1)
		{
			$card->set_ribbon('Terminé');
		}
		return $card->get_html();
	}
	
	// Récupère tous les manga.
	public static function get_all($id_member=NULL)
	{
		$base = self::$_base;
		if ($id_member == NULL)
		{
			$sql = '
		    SELECT m.*, f.*, c.Nom CategorieGenre FROM manga m
		    INNER JOIN fiche AS f ON f.IdFiche = m.IdFiche
            INNER JOIN categoriegenre AS c ON m.IdCategorieGenre = c.IdCategorieGenre;';
		}
		else
		{
			$sql = '
		    SELECT m.*, f.*, c.Nom CategorieGenre FROM manga m
		    INNER JOIN fiche AS f ON f.IdFiche = m.IdFiche
            INNER JOIN categoriegenre AS c ON m.IdCategorieGenre = c.IdCategorieGenre
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
	
	// Récupère tous les mangas du membre.
	public static function get_all_owned($id_member)
	{
		$base = self::$_base;
		$sql = '
			SELECT a.Nom, a.Note, a.Date, a.IdMembre, a.NbEpisodes, AVG(an.Note) Moyenne, a.IdFiche, a.AnneeVF
			FROM
			(
				SELECT a.IdManga, a.Nom, a.NbEpisodes, an.Note, f.Date, f.IdMembre, f.IdFiche, a.AnneeVF
				FROM manganote an
				INNER JOIN manga AS a ON an.IdManga = a.IdManga
				INNER JOIN fiche AS f ON f.IdFiche = a.IdFiche
				WHERE an.IdMembre = '.$id_member.' 
			) a
			INNER JOIN 
			(
				SELECT IdFiche from fichemembre WHERE IdMembre = '.$id_member.'   
			) 
			AS f ON f.IdFiche = a.IdFiche
			INNER JOIN manganote AS an ON an.IdManga = a.IdManga
			GROUP BY a.IdManga ORDER BY a.Nom';
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