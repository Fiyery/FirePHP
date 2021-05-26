<?php
namespace FirePHP\Time;

use DateTime;
use FirePHP\Time\Interval;

/**
 * Date est un outil de simplification de la gestion du temps.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses Interval
 */
class Date
{
    /**
     * Format YYMMDDHHMMSS. Peut être reçu par le constructeur.
     * @var int
     */
    const NORMAL = 'YmdHis';
    
    /**
     * Format timestamps. Peut être reçu par le constructeur.
     * @var int
     */
    const TIMESTAMP = 'U';
    
    /**
     * Format YYYY-MM-DD HH:MM:SS.
     * @var int
     */
    const ENGLISH = 'Y-m-d H:i:s';
    
    /**
     * Format DD/MM/YYYY HH:MM:SS.
     * @var int
     */
    const FRENCH = 'd/m/Y H:i:s';
    
    /**
     * Format DAY_NAME DD MONTH_NAME YYYY HHhMM.
     * @var int
     */
    const FULLWORD = 'l d F Y H\hi';
    
     /**
     * Format DD MONTH_NAME YYYY HHhMM.
     * @var int
     */
    const SIMPLE = 'd F Y H\hi';
    
    /**
     * Format HH:MM:SS.
     * @var int
     */
    const HORAIRE = 'H:i:s';
    
    /**
     * Format HHhMM.
     * @var int
     */
    const HOUR = 'H\hi';
    
    /**
     * Format DD MONTH_NAME YYYY.
     * @var int
     */
    const DAY = 'd F Y';
	
	/**
	 * Timestamp de la date.
	 * @var int
	 */
	private $_timestamp;
	
	/**
	 * Liste des noms des jours de la semaine.
	 * @var string[]
	 */
	private static $_day_names;
	
	/**
	 * Liste des noms des jours de la semaine.
	 * @var string[]
	 */
	private static $_month_names;
	
	/**
	 * Constructeur.
	 * @param string $date Date à traiter.
	 * @param int $format Format de la date à traiter. 
	 */
	public function __construct($date=NULL, $format=NULL)
	{
		if ($format !== NULL)
		{
			$date = DateTime::createFromFormat($format, $this->format());
			$this->_timestamp = (is_object($date)) ? ($date->getTimestamp()) : (time());	
		}
		else
		{
			$this->_timestamp = strtotime($date);
			if ($this->_timestamp === FALSE) 
			{
				$this->_timestamp = (is_numeric($date)) ? ($date) : (time());
			}
		}
		self::$_day_names = [
			'Lundi',
			'Mardi',
			'Mercredi',
			'Jeudi',
			'Vendredi',
			'Samedi',
			'Dimanche'
		];
		self::$_month_names = [
			'Janvier',
			'Février',
			'Mars',
			'Avril',
			'Mai',
			'Juin',
			'Juillet',
			'Août',
			'Septembre',
			'Octobre',
			'Novembre',
			'Décembre'
		];
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
	 * @return int
	 */
	public function timestamp()
	{
		return $this->_timestamp;
	}
	
	/**
	 * Retourne les secondes de la date courante.
	 * @return string 
	 */
	public function second() : string
	{
		return date('s', $this->_timestamp);
	}
	
	/**
	 * Retourne les minutes de la date courante.
	 * @return string
	 */
	public function minute() : string
	{
		return date('i', $this->_timestamp);
	}

	/**
	 * Retourne les heures de la date courante.
	 * @return string
	 */
	public function hour() : string
	{
		return date('H', $this->_timestamp);
	}
	
	/**
	 * Retourne le jour de la date courante.
	 * @return string 
	 */
	public function day() : string
	{
		return date('d', $this->_timestamp);
	}
	
	/**
	 * Retourne le mois de la date courante.
	 * @return string
	 */
	public function month() : string
	{
		return date('m', $this->_timestamp);
	}
	
	/**
	 * Retourne l'année de la date courante.
	 * @return string
	 */
	public function year() : string
	{
		return date('Y', $this->_timestamp);
	}
	
	/**
	 * Retourne le numéro de semaine de la date courrante.
	 * @return string
	 */
	public function week() : string
	{
		return date('W', $this->_timestamp);
	}

	/**
	 * Retourne le numéro de la semaine de la date courrante.
	 * @return string
	 */
	public function day_week() : string
	{
		return date('w', $this->_timestamp);
	}

	/**
	 * Rretourne le numéro du jour de l'année de la date courrante.
	 * @return string
	 */
	public function day_year() : string
	{
		return date('z', $this->_timestamp) + 1;
	}
	
	/**
	 * Retourne la date courante sous un format spécifique.
	 * @param string $format Format de retour parmi les constants de la classe ou convetion de la fonction date() de PHP.
	 * @return string
	 */
	public function format(string $format = self::NORMAL) : string
	{
		$date = date($format, $this->_timestamp);
		$date = str_replace(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'], self::$_day_names, $date);
		foreach (self::$_day_names as $d)
		{
			$sub_day_names[] = substr($d, 0, 3);
		}
		$date = str_replace(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'], $sub_day_names, $date);
		$date = str_replace(['January','February','March','April','May','June','July','August','September','October','November','December'], self::$_month_names, $date);
		foreach (self::$_month_names as $m)
		{
			$sub_month_names[] = substr($m, 0, 3);
		}
		$date = str_replace(['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'], $sub_month_names, $date);
		return $date;
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
		$datetime1 = new DateTime($this->format(self::ENGLISH));
		$datetime2 = new DateTime($date->format(self::ENGLISH));
		return new Interval($datetime1->diff($datetime2));
	}

	/**
	 * Compare deux dates.
	 * @return int
	 */
	public function compare(Date $d)
	{
		return $this->_timestamp <=> $d->timestamp();
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
		$year = substr($date, 0, 4);
		$month = substr($date, 4, 2);
		$day = substr($date, 6, 2);
		$hour = substr($date, 8, 2);
		$minute = substr($date, 10, 2);
		$second = substr($date, 12, 2);
		if (checkdate($month, $day, $year) == FALSE || $hour > 23 || $minute > 59 || $second > 59)
		{
			return FALSE;
		}
		return TRUE;
	}	
	
	/**
	 * Ajoute une période à une date. 
	 * @param string $period Période à ajouter parmi : second, minute, hour, day, week, month, year.
	 * @param string $value Valeur de la période à ajouter.
	 * @return Date
	 */
	public function add(string $period, string $value) : Date
	{
		if (is_numeric($value) == FALSE)
		{
			return NULL;
		}
		$second = $this->second(); 
		$minute = $this->minute(); 
		$hour = $this->hour(); 	
		$day = $this->day(); 
		$month = $this->month(); 
		$year = $this->year(); 
		switch ($period)
		{
			case 'second': $second += $value; break;
			case 'minute': $minute += $value; break;
			case 'hour': $hour += $value; break;
			case 'day': $day += $value; break;
			case 'week': $day += $value*7; break;
			case 'month': $month += $value; break;
			case 'year': $year += $value; break;
			default: break;
		}
		$this->_timestamp = mktime($hour, $minute, $second, $month, $day, $year);
		return $this;
	}
}
?>