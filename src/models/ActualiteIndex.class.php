<?php
/**
 * Classe DAO pour actualiteindex.
 */
class ActualiteIndex extends Dao
{
	// Fonction qui retourne les $n dernières actualités.
	public static function get_from($a,$b)
	{
		$sql = 'SELECT * FROM actualiteindex ORDER BY IdActu DESC LIMIT '.$a.','.($b-$a);
		$base = Base::get_instance();
		$result = $base->query($sql,$base->select_base(__CLASS__));
		if (is_array($result) == FALSE)
		{
			return FALSE;
		}
		$objects = array();
		foreach ($result as $r)
		{
			$objects[] = new self($r);
		}
		return $objects;
	}
	
	// Fonction qui retourne la dernière actualité.
	public static function get_last()
	{
		$sql = 'SELECT * FROM actualiteindex ORDER BY IdActu DESC LIMIT 1';
		$base = Base::get_instance();
		$result = $base->query($sql,$base->select_base(__CLASS__));
		return (is_array($result)) ? (new self($result[0])) : (FALSE);
	}
	
	// Fonction qui retourne l'image de l'actualité ou FALSE.
	public function get_image()
	{
		$site = Site::get_instance();
		$config = Config::get_instance();
	    $root_dir = $site->get_root(Site::DIR).$config->path->news;
		$root = $site->get_root().$config->path->news;
		if (file_exists($root_dir.$this->IdActu.'.png'))
		{
			return $root.$this->IdActu.'.png';
		} 
		elseif (file_exists($root_dir.$this->IdActu.'.jpg'))
		{
			return $root.$this->IdActu.'.jpg';
		}
		elseif (file_exists($root_dir.$this->IdActu.'.jpeg'))
		{
			return $root.$this->IdActu.'.jpeg';
		}
		return FALSE;
	}
	
	// Fonction qui retourne l'image de l'actualité ou FALSE.
	public function get_image_min()
	{
		$site = Site::get_instance();
		$config = Config::get_instance();
	    $root_dir = $site->get_root(Site::DIR).$config->path->news;
		$root = $site->get_root().$config->path->news;
		if (file_exists($root_dir.$this->IdActu.'.min.png'))
		{
			return $root.$this->IdActu.'.min.png';
		} 
		elseif (file_exists($root_dir.$this->IdActu.'.min.jpg'))
		{
			return $root.$this->IdActu.'.min.jpg';
		}
		elseif (file_exists($root_dir.$this->IdActu.'.min.jpeg'))
		{
			return $root.$this->IdActu.'.min.jpeg';
		}
		return FALSE;
	}
	
	/**
	 * Retourne le nombre de commentaires d'une actualité.
	 * @return int Nombre de commentaires d'une actualité.
	 */
	public function get_count_coms()
	{
		$sql = 'SELECT count(*) Nb FROM commentaire WHERE IdActu = '.$this->IdActu;
		$base = Base::get_instance();
		$result = $base->query($sql, $base->select_base(__CLASS__));
		return (is_array($result) !== FALSE) ? ($result[0]['Nb']) : (0);
	}
	
	/**
	 * Retourne les commentaires d'une actualité d'index.
	 * @return boolean|array<Commentaire> Retourne un tableau de Commentaires ou FALSE.
	 */
	public function get_coms()
	{
		$sql = 'SELECT * FROM commentaire WHERE IdActu = '.$this->IdActu.' ORDER BY Date DESC';
		$base = Base::get_instance();
		$result = $base->query($sql, $base->select_base(__CLASS__));
		if (is_array($result) === FALSE)
		{
			return FALSE;
		}
		$objects = array();
		foreach ($result as $r)
		{
			$objects[] = new Commentaire($r);
		}
		return $objects;
	}
}
?>