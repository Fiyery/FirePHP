<?php
class Fiche extends Dao
{
	private static $_types = array('Anime', 'Manga', 'Drama');
	
	// Retourne tous les types possibles d'une fiche.
	public static function get_all_types()
	{
		return self::$_types;
	} 
	
	// Fonction qui retourne les fiches d'un certain type passé en paramètre.
	public static function get_type($type=NULL)
	{
		$type = ucfirst(strtolower($type));
		$res = FALSE;
		if (in_array($type,self::get_all_types()))
		{
			$key = 'Id'.$type;
			$name = strtolower(__CLASS__);
			$sql = 'SELECT * FROM '.$name.' WHERE '.$key.' > 0;';
			$base = Base::get_instance();
			$res = $base->query($sql,$base->select_base($name));
			if (is_array($res))
			{
				$tmp  = array();
				foreach($res as $d)
				{
					$tmp[] = new $name($d);
				}
				$res = $tmp;
			}
		}
		return $res;
	}

	// Fonction qui retourne l'ensemble des noms des fiche.
	public static function get_all_names()
	{
		$base = Base::get_instance();
		$result = array();
		$types = self::get_all_types();
		foreach ($types as $t)
		{
			$id = 'Id'.$t;
			$t = strtolower($t);
			$sql = 'SELECT Nom FROM '.$t.';';
			$res = $base->query($sql,$base->select_base(__CLASS__));	
			if (is_array($res))
			{
				foreach ($res as $r)
				{
					$result[] = $r['Nom'];
				}
			}
		}
		sort($result);
		return (count($result) > 0) ? ($result) : (FALSE);		
	}
	
	// Fonction qui retourne l'objet ou le tableau d'objets Fiche en fonction du nom.
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
		$types = self::get_all_types();
		$results = array();
		$base = Base::get_instance();
		foreach ($types as $t)
		{
			$table = strtolower($t);
			$sql = 'SELECT * FROM '.$table.' WHERE Nom IN '.$search.';';
			$res = $base->query($sql,$base->select_base(__CLASS__));
			if (is_array($res))
			{
				$objects = array();
				foreach ($res as $r)
				{
					$objects[] = new $t($r);
				}
				$results = array_merge($results, $objects);
			}
		}
		return $objects;
	}
	
	// Fonction qui complète les informations de la fiche.
	public function complete()
	{
	    $base = self::$_base;
	    $i = 0;
	    $max = count(self::$_types);
	    $completed = FALSE;
	    while ($i < $max && $completed == FALSE)
	    {
	        $class = self::$_types[$i];
	        $table = strtolower($class);
	        $sql = 'SELECT * FROM '.$table.' WHERE IdFiche = '.$this->IdFiche;
	        $res = $base->query($sql,$base->select_base(__CLASS__));
	        if (is_array($res) && count($res) > 0)
	        {
	        	$object = new $class($res[0]);
	        	$object->Fiche = $this;
	        	$object->complete();
	        	$complete = TRUE;
	        }
	        $i++;
	    }
		return $object;
	}

	// Fonction qui retourne la card de la fiche.
	public function get_card($root)
	{
		$this->complete();
		$id_fiche = $this->IdFiche;
		$title = '';
		$url_image = '';
		$info1_name = '';
		$info2_name = '';
		$info3_name = '';
		$info1_value = '';
		$info2_value = '';
		$info3_value = '';
		if ($this->Anime !== NULL)
		{
			$title = $this->Anime->Nom;
			$url_image = $this->Anime->Image;
			$info1_name = 'Année';
			$info2_name = 'Nb d\'épisodes';
			$info3_name = 'Genres';
			$info1_value = $this->Anime->Annee;
			$info2_value = $this->Anime->NbEpisodes;
			$info3_value = implode(', ',$this->Anime->Genres);
		}
		elseif ($this->Manga !== NULL)
		{
			$title = $this->Manga->Nom;
			$url_image = $this->Manga->Image;
			$info1_name = 'Année';
			$info2_name = 'Nb de tomes';
			$info3_name = 'Catégorie';
			$info1_value = $this->Manga->AnneeVO;
			$info2_value = $this->Manga->NbTomesVO;
			$info3_value = $this->Manga->get_categorie();
		}
		elseif ($this->Drama !== NULL)
		{
				
		}
		$pos = strrpos($url_image,'.');
		$url_image = substr($url_image,0,$pos).'.min'.substr($url_image,$pos);
		$url = $root."wiki/?module=PageFiche&amp;action=voir&amp;id=".$id_fiche;
		$card = new Card($title,$url,$url_image);
		$card->add_field($info1_name,$info1_value);
		$card->add_field($info2_name,$info2_value);
		$card->add_field($info3_name,$info3_value);
		return $card->get_html();
	}

	// Fonction qui retourne l'objet caractéristique d'une fiche.
	public function get_item()
	{
		$this->complete();
		if ($this->Anime !== NULL)
		{
			return $this->Anime;
		}
		elseif ($this->Manga !== NULL)
		{
			return $this->Manga;
		}
		elseif ($this->Drama !== NULL)
		{
			return $this->Drama;
		}
		return NULL;
	}
	
	// Fonction qui retourne les dernières fiches et les instancient.
	public static function get_last($nb)
	{
	    if (is_numeric($nb) == FALSE || $nb <= 0)
	    {
	        return array();
	    }
	    $sql = '
                SELECT * 
                FROM `fiche` f
                ORDER BY f.IdFiche DESC
                LIMIT 0,'.$nb;
	    $base = self::$_base;
	    $res = $base->query($sql);
	    if (is_array($res) == FALSE)
	    {
	        return array();
	    }
	    $ids = array();
	    foreach ($res as $r)
	    {
	        $ids[] = $r['IdFiche'];
	    }
	    $objects = array();
	    foreach (self::$_types as $t)
	    {
	        $sql = '
                SELECT *
                FROM `'.strtolower($t).'`
                WHERE IdFiche IN ('.implode(',', $ids).')';
	        $res2 = $base->query($sql);
	        if (is_array($res2))
	        {
	            $tmp = array();
	            foreach ($res2 as $r)
	            {
                    $ob = new $t($r);
                    $max = count($res);
                    for($i=0; $i < $max; $i++)
                    {
                        if ($res[$i]['IdFiche'] == $ob->IdFiche)
                        {
                            foreach ($res[$i] as $attribut => $value)
                            {
                                $ob->$attribut = $value;
                            }
                            unset($res[$i]);
                            $res = array_values($res);
                            $i = $max;
                        }
                    }
	                $tmp[] = $ob;
	            }
	            $objects = array_merge($objects, $tmp);
	        }
	    }
	    usort($objects, function($a,$b) { return ($a->Date <= $b->Date);});
	    return $objects;
	}
	
}
?>