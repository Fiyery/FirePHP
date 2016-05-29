<?php
/**
 * Date est un outil de simplification de la gestion du temps.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses Interval
 * @uses FireException
 */
class Date
{
    /**
     * Format YYMMDDHHMMSS. Peut être reçu par le constructeur.
     * @var int
     */
    const NORMAL = 1;
    
    /**
     * Format timestamps. Peut être reçu par le constructeur.
     * @var int
     */
    const TIMESTAMP = 2;
    
    /**
     * Format YYYY-MM-DD HH:MM:SS.
     * @var int
     */
    const ENGLISH = 3;
    
    /**
     * Format DD/MM/YYYY HH:MM:SS.
     * @var int
     */
    const FRENCH = 4;
    
    /**
     * Format DAY_NAME DD MONTH_NAME YYYY à HH h MM.
     * @var int
     */
    const FULLWORD = 6;
    
     /**
     * Format DD MONTH_NAME YYYY à HH h MM.
     * @var int
     */
    const SIMPLE = 7;
    
    /**
     * Format HH:MM:SS.
     * @var int
     */
    const HORAIRE = 8;
    
    /**
     * Format HH h MM.
     * @var int
     */
    const HOUR = 9;
    
    /**
     * Format DD MONTH_NAME YYYY.
     * @var int
     */
    const DAY = 10;
    
    /**
     * Nombre de secondes de la date.
     * @var number
     */
	private $_second;
	
	/**
	 * Nombre de minutes de la date.
	 * @var number
	 */
	private $_minute;
	
	/**
	 * Nombre d'heures de la date.
	 * @var number
	 */
	private $_hour;
	
	/**
	 * Nombre de jours de la date.
	 * @var number
	 */
	private $_day;
	
	/**
	 * Nombre de mois de la date.
	 * @var number
	 */
	private $_month;
	
	/**
	 * Nombre d'année de la date.
	 * @var number
	 */
	private $_year;
	
	/**
	 * Nombre de semaine de la date.
	 * @var number
	 */
	private $_week;
	
	/**
	 * Numero du jour de la semaine de la date.
	 * @var int
	 */
	private $_day_week;
	
	/**
	 * Numero du jour de l'année de la date.
	 * @var number
	 */
	private $_day_year;
	
	/**
	 * Timestamp de la date.
	 * @var number
	 */
	private $_timestamp;
	
	/**
	 * Liste des noms des jours de la semaine.
	 * @var array<string>
	 */
	private $_day_names;
	
	/**
	 * Liste des noms des jours de la semaine.
	 * @var array<string>
	 */
	private $_month_names;
	
	/**
	 * Constructeur.
	 * @param string $date Date à traiter.
	 * @param int $format Format de la date à traiter. N'accepte que NORMAL et TIMESTAMP
	 * @throws FireException
	 */
	public function __construct($date=NULL, $format=self::NORMAL)
	{
		if ($format != self::NORMAL && $format != self::TIMESTAMP)
		{
			$d = debug_backtrace();
			throw new FireException('Le format de la date est invalide', $d[0]['file'], $d[0]['line']);
		}
		if ($format == self::TIMESTAMP)
		{
			$value = date('YmdHis',$date);
			if ($value == FALSE)
			{
				$format = self::NORMAL;
			}
			else
			{
			    $this->_timestamp = $date;
			}
		}
		if ($format == self::NORMAL)
		{
			$value = ($this->check($date)) ? ($date) : (self::get_now());
		}
		$this->_year = substr($value,0,4);
		$this->_month = substr($value,4,2);
		$this->_day = substr($value,6,2);
		$this->_hour = substr($value,8,2);
		$this->_minute = substr($value,10,2);
		$this->_second = substr($value,12,2);
		$this->_day_names = array('1'=>'Lundi','2'=>'Mardi','3'=>'Mercredi','4'=>'Jeudi','5'=>'Vendredi','6'=>'Samedi','7'=>'Dimanche');
		$this->_month_names = array('1'=>'Janvier','2'=>'Février','3'=>'Mars','4'=>'Avril','5'=>'Mai','6'=>'Juin','7'=>'Juillet','8'=>'Août','9'=>'Septembre','10'=>'Octobre','11'=>'Novembre','12'=>'Décembre');
		$this->_week = NULL;
		$this->_day_week = NULL;
		$this->_day_year = NULL;
	}
	
	/**
	 * Affichage la date courante.
	 * @return string Date courante au format NORMAL.
	 */
	public function __toString()
	{
		return $this->format();
	}	
	
	/**
	 * Retourne le timestamps de la date courrante.
	 * @return number 
	 */
	public function get_timestamp()
	{
		if ($this->_timestamp == NULL)
		{
			$date = DateTime::createFromFormat('YmdHis',$this->format());
			$this->_timestamp = (is_object($date)) ? ($date->getTimestamp()) : (FALSE);
		}
		return $this->_timestamp;
	}
	
	/**
	 * Retourne les secondes de la date courante.
	 * @return number
	 */
	public function get_second()
	{
		return $this->_second;
	}
	
	/**
	 * Retourne les minutes de la date courante.
	 * @return number
	 */
	public function get_minute()
	{
		return $this->_minute;	}
	
	/**
	 * Retourne le jour de la date courante.
	 * @return number
	 */
	public function get_day()
	{
		return $this->_day;
	}
	
	/**
	 * Retourne le mois de la date courante.
	 * @return number
	 */
	public function get_month()
	{
		return $this->_month;
	}
	
	/**
	 * Retourne l'année de la date courante.
	 * @return number
	 */
	public function get_year()
	{
		return $this->_year;
	}
	
	/**
	 * Retourne le numéro de semaine de la date courrante.
	 * @return number
	 */
	public function get_week()
	{
		if ($this->_week == NULL)
		{
			$this->_week = date('W',$this->get_timestamp());
		}
		return $this->_week;
	}

	/**
	 * Retourne le numéro de la semaine de la date courrante.
	 * @return int
	 */
	public function get_day_week()
	{
		if ($this->_day_week == NULL)
		{
			$this->_day_week = date('w',$this->get_timestamp());
		}
		return $this->_day_week;
	}

	/**
	 * Rretourne le numéro du jour de l'année de la date courrante.
	 * @return number
	 */
	public function get_day_year()
	{
		if ($this->_day_year == NULL)
		{
			$this->_day_year = date('z',$this->get_timestamp()) + 1;
		}
		return $this->_day_year;
	}
	
	/**
	 * Retourne la date courante sous un format spécifique.
	 * @param string $format Format de retour parmi les constants de la classe ou convetion de la fonction date() de PHP.
	 * @return string
	 */
	public function format($format=self::NORMAL)
	{
		$date = NULL;
		if ($format == self::NORMAL)
		{
			$date = $this->_year.$this->_month.$this->_day.$this->_hour.$this->_minute.$this->_second;
		}
		elseif ($format == self::ENGLISH)
		{
			$date = $this->_year.'-'.$this->_month.'-'.$this->_day.' '.$this->_hour.':'.$this->_minute.':'.$this->_second;
		}
		elseif ($format == self::FRENCH)
		{
			$date = $this->_day.'/'.$this->_month.'/'.$this->_year.' '.$this->_hour.':'.$this->_minute.':'.$this->_second;
		}
		elseif ($format == self::FULLWORD)
		{
			$date = $this->_day_names[$this->get_day_week()].' '.$this->_day.' '.$this->_month_names[(int)$this->_month].' '.$this->_year.' à '.$this->_hour.' h '.$this->_minute;
		}
		elseif ($format == self::SIMPLE)
		{
			$date = $this->_day.' '.$this->_month_names[(int)$this->_month].' '.$this->_year.' à '.$this->_hour.' h '.$this->_minute;
		}
		elseif ($format == self::HORAIRE)
		{
			$date = $this->_hour.':'.$this->_minute.':'.$this->_second;
		}
		elseif ($format == self::HOUR)
		{
			$date = $this->_hour.' h '.$this->_minute;
		}
		elseif ($format == self::DAY)
		{
			$date = $date = $this->_day.' '.$this->_month_names[(int)$this->_month].' '.$this->_year;
		}
		elseif ($format == self::FRENCH)
		{
			$date = $this->_day.'/'.$this->_month.'/'.$this->_year.' à '.$this->_hour.'h'.$this->_minute;
		}
		else
		{
			$date = date($format,$this->get_timestamp());
			$date = str_replace(array('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), $this->_day_names, $date);
			foreach ($this->_day_names as $d)
			{
				$sub_day_names[] = substr($d,0,3);
			}
			$date = str_replace(array('Mon','Tue','Wed','Thu','Fri','Sat','Sun'),$sub_day_names,$date);
			$date = str_replace(array('January','February','March','April','May','June','July','August','September','October','November','December'),$this->_month_names,$date);
			foreach ($this->_month_names as $m)
			{
				$sub_month_names[] = substr($m,0,3);
			}
			$date = str_replace(array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'),$sub_month_names,$date);
		}
		return $date;
	}
	
	/**
	 * Extrait les différentes parties de la date courante.
	 * @return array<number> Liste des valeur avec leur clé.
	 */
	public function extract()
	{
		$tab['date'] = $this->format();
		$tab['year'] = $this->_year;
		$tab['month'] = $this->_month;
		$tab['day'] = $this->_day;
		$tab['hour'] = $this->_hour;
		$tab['minute'] = $this->_minute;
		$tab['second'] = $this->_second;
		return $tab;
	}

	/**
	 * Différence entre deux dates.
	 * @param Date $date Date à comparer par rapport à la date courante.
	 * @return Interval
	 */
	public function diff(Date $date)
	{
		if (!is_object($date) || get_class($date) != __CLASS__)
		{
			return NULL;
		}
		$datetime1 = new DateTime($this->_year.'-'.$this->_month.'-'.$this->_day.' '.$this->_hour.':'.$this->_minute.':'.$this->_second);
		$datetime2 = new DateTime($date->_year.'-'.$date->_month.'-'.$date->_day.' '.$date->_hour.':'.$date->_minute.':'.$date->_second);
		return new Interval($datetime1->diff($datetime2));
	}
	
	/**
	 * Retourne la date actuelle au format NORMAL.
	 * @return string
	 */
	public static function get_now()
	{
		return date('YmdHis');
	}

	/**
	 * Vérifie si la date est au bon format NORMAL.
	 * @param string $date Chaîne de caractères à tester.
	 * @return boolean
	 */
	public static function check($date)
	{
		if (!is_numeric($date) || strlen($date) != 14)
		{
			return FALSE;
		}
		$year = substr($date,0,4);
		$month = substr($date,4,2);
		$day = substr($date,6,2);
		$hour = substr($date,8,2);
		$minute = substr($date,10,2);
		$second = substr($date,12,2);
		if (checkdate($month,$day,$year) == FALSE || $hour > 23 || $minute > 59 || $second > 59)
		{
			return FALSE;
		}
		return TRUE;
	}	
	
	/**
	 * Ajoute une période à une date. 
	 * @param string $period Période à ajouter parmi : second, minute, hour, day, week, month, year.
	 * @param number $value Valeur de la période à ajouter.
	 * @return Date
	 */
	public function add($period, $value)
	{
		if (is_numeric($value) == FALSE)
		{
			return NULL;
		}
		$d = $this->extract();
		switch ($period)
		{
			case 'second': $d['second'] += $value; break;
			case 'minute': $d['minute'] += $value; break;
			case 'hour': $d['hour'] += $value; break;
			case 'day': $d['day'] += $value; break;
			case 'week': $d['day'] += $value*7; break;
			case 'month': $d['month'] += $value; break;
			case 'year': $d['year'] += $value; break;
			default: break;
		}
		$timestamp = mktime($d['hour'],$d['minute'],$d['second'],$d['month'],$d['day'],$d['year']);
		$value = date('YmdHis',$timestamp);
		if ($value !== FALSE)
		{
			$this->_year = substr($value,0,4);
			$this->_month = substr($value,4,2);
			$this->_day = substr($value,6,2);
			$this->_hour = substr($value,8,2);
			$this->_minute = substr($value,10,2);
			$this->_second = substr($value,12,2);
			$this->_week = NULL;
			$this->_day_week = NULL;
			$this->_day_year = NULL;
		}
		return $this;
	}
}
?>