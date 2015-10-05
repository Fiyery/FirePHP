<?php
class Connexion extends Dao
{
	private static $connected_time = 300; // Temps en seconde durant lequel une personne est considérée en ligne.
	private static $number_visitors = NULL;
	private static $connected_members = NULL;

	// Fonction qui retourne le nombre de visiteurs uniques.
	public static function get_unique()
	{
		$sql = "SELECT count(DISTINCT Ip) count FROM ".strtolower(__CLASS__).";";
		$base = self::$_base;
		$retour = $base->query($sql,$base->select_base(__CLASS__));
		return ($retour !== FALSE) ? ($retour[0]['count']) : (0);
	}

	// Fonction qui retourne le nombre de visiteurs total.
	public static function get_all()
	{
		$sql = "SELECT count(*) count FROM ".strtolower(__CLASS__).";";
		$base = self::$_base;
		$retour = $base->query($sql,$base->select_base(__CLASS__));
		return ($retour !== FALSE) ? ($retour[0]['count']) : (0);
	}

	// Fonction qui retourne le nombre de visiteurs sur une certaine période, peut être annuelle si $period=Y, ou mensuelle, si period=M ou hedomadaire si $period=W ou $period=D pour jour.
	public static function get_visites_period($period=NULL,$last=0)
	{
		$base = self::$_base;
		if ($period == 'Y')
		{
			$date = date('Y') - $last;
			$sql = "SELECT count(*) FROM ".strtolower(__CLASS__)." WHERE Date LIKE '".$date."%';";
		}
		elseif ($period == 'M')
		{
			$date = date('Ym',mktime(0,0,0,date('m')-$last,1,date('Y')));
			$sql = "SELECT count(*) FROM ".strtolower(__CLASS__)." WHERE Date LIKE '".$date."%';";
		}
		elseif ($period == 'D')
		{
			$date = date('Ymd',mktime(0,0,0,date('m'),date('d')-$last,date('Y')));
			$sql = "SELECT count(*) FROM ".strtolower(__CLASS__)." WHERE Date LIKE '".$date."%';";
		}
		elseif ($period == 'H')
		{
			$time = mktime(0,0,0,date('m'),date('d')-date('N')+1,date('Y'));
			$res = strtotime('-'.$last.' week',$time);
			$date1 = date('YmdHis',$res);
			$res = strtotime('+7 days',$res);
			$date2 = date('YmdHis',$res);
			$sql = "SELECT count(*) FROM ".strtolower(__CLASS__)." WHERE Date BETWEEN ".$date1." AND ".$date2.";";
		}
		$retour = $base->query($sql,$base->select_base(__CLASS__));
		return ($retour !== FALSE) ? ($retour[0]['count(*)']) : (0);
	}

	// Fonction qui retourne le pseudo des membres connecté.
	public static function get_connected()
	{
		if (self::$number_visitors == NULL && self::$connected_members == NULL)
		{	
			$date = new Date();
			$date->add('second',-self::$connected_time);
			$query = new Query("
				SELECT c.IdConnexion, Ip, Date, IdMembre
				FROM 
				(
				    SELECT MAX(IdConnexion) IdConnexion
				    FROM connexion 
				    WHERE Date >= ".$date->format()."
				    GROUP BY Ip 
				) l
				INNER JOIN connexion AS c ON c.IdConnexion = l.IdConnexion; 
			");
			$result = self::$_base->query($query);
			self::$number_visitors = 0;
			self::$connected_members = array();
			if (is_array($result))
			{
				foreach ($result as $r)
				{
					if ($r['IdMembre'] != 0)
					{
						self::$connected_members[] = Membre::load($r['IdMembre']);
					}
					else
					{
						self::$number_visitors++;
					}
				}			
			}
		}
		return self::$connected_members;
	}

	// Fonction qui retourne le nombre de visiteurs sur le site.
	public static function get_visited()
	{
		if (self::$number_visitors == NULL && self::$connected_members == NULL)
		{
			self::get_connected();
		}
		return self::$number_visitors;
	}

	// Fonction qui ajoute une connexion dans la table.
	public static function add_connected()
	{
		$session = Session::get_instance();
		$id_member = ($session->is_open()) ? ($session->user->IdMembre) : (0);
		$ip = $_SERVER['REMOTE_ADDR'];
		$date = Date::get_now();
		self::add(array('',$ip,$date,$id_member));
	}

	// Fonction qui retourne tous les membres connectés depuis une durée en seconde passée en arguments.
	public static function get_member_period($seconds=3600)
	{
		$date = new Date(time() - $seconds,Date::TIMESTAMP);
		$date = $date->format();
		$sql = 'SELECT IdMembre FROM '.strtolower(__CLASS__).' WHERE IdMembre > 0 AND Date > '.$date;
		$base = self::$_base;
		$result = $base->query($sql,$base->select_base(__CLASS__));
		if (is_array($result) == FALSE)
		{
			return FALSE;
		}
		$list_members = array();
		foreach ($result as $l)
		{
			if (in_array($l['IdMembre'],$list_members) == FALSE)
			{
				$list_members[] = $l['IdMembre'];
			}
		}
		$list_member_objects = array();
		foreach ($list_members as $m)
		{
			$member = Membre::load($m);
			if ($member !== FALSE)
			{
				$list_member_objects[] = $member;
			}
		}
		return $list_member_objects;
	}
}
?>