<?php
/**
 * Cron permet de simuler la commande cronjob en PHP.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 */
class Cron
{
    /**
     * Nom du fichier qui arrête le cron dans le répertoire des logs.
     * @var string
     */
    const NAME_CRON_END = 'cron.end';
    
    /**
     * Dossier de sauvegarde des logs.
     * @var string
     */
    private static $_dir = './';
    
    /**
     * Nom du cron.
     * @var string
     */
	private $_name;
	
	/**
	 * Liste des tâches.
	 * @var array
	 */
	private $_tasks;
	
	/**
	 * Année servant au calcul de la prochaine date d'exécution.
	 * @var int
	 */
	private $_year;
	
	/**
	 * Mois servant au calcul de la prochaine date d'exécution.
	 * @var int
	 */
	private $_month;
	
	/**
	 * Jour servant au calcul de la prochaine date d'exécution.
	 * @var int
	 */
	private $_day;
	
	/**
	 * Heure servant au calcul de la prochaine date d'exécution.
	 * @var int
	 */
	private $_hour;
	
	/**
	 * Minute servant au calcul de la prochaine date d'exécution.
	 * @var int
	 */
	private $_minute;
	

	/**
	 * Constructeur.
	 * @param string $name Nom du Cron.
	 * @param int $time Temps supplémentaire pour l'exécution du script.
	 */
	public function __construct($name,$time=30)
	{
		$this->_name = $name;
		set_time_limit($time);
		ignore_user_abort(TRUE);
		$fp = fopen(self::$_dir.'cron.log', 'a+');
		fwrite($fp, "Begin\t=> ".$this->_name.' at '.date('H:i:s d/m/Y')."\n");
		fclose($fp);
	}
	
	/**
	 * Destructeur.
	 */
	public function __destruct()
	{
		$fp = fopen(self::$_dir.'cron.log', 'a+');
		fwrite($fp, "End\t\t=> ".$this->_name.' at '.date('H:i:s d/m/Y')."\n");
		fclose($fp);
	}
	
	/**
	 * Ajoute une tâche planifiée au Cron. 
	 * Pour l'horloge, les valeurs possibles sont un entier, une suite d'entiers séparés par des virgules, un interval séparer par "-", ou pour toutes les occurences "*".
	 * @param callable $callable Nom d'un fichier à appeler ou le nom d'une fonction ou classe et méthode.
	 * @param int $minutes Minute d'appelle de la tâche.
	 * @param int $hours Heure d'appelle de la tâche.
	 * @param int $days Jour d'appel de la tâche.
	 * @param int $weekdays Jour de la semaine d'appel de la tâche.
	 * @param int $months Mois d'appel de la tâche.
	 */
	public function add_task($callable, $minutes, $hours, $days, $weekdays, $months)
	{
		$this->_tasks[] = array(
		    'call'    => $callable,
		    'minute' => trim($minutes),
		    'hour'   => trim($hours),
		    'day'   => trim($days),
		    'weekday'  => trim($weekdays),
		    'month'   => trim($months)
		);
	}
	
	/**
	 * Lance l'exécution du Cron.
	 */
	public function run()
	{
	    if (count($this->_tasks) == 0)
	    { 
	        return FALSE;
	    }
	    $this->_build_times();
	    while (TRUE)
	    {
	        $index = $this->_get_next_time();
	        $seconds = $this->_tasks[$index]['next_time'] - time();
	        if ($seconds > 0)
	        {
	            sleep($seconds);
	        }
	        $this->_execute($index);
	        $this->taches[$index]['prochain'] = $this->_build_next_time_task($index);
	    }
	}
	
	/**
	 * Calcul le temps en seconde à attendre pour les prochaines tâches.
	 */
	private function _build_times()
	{
	    foreach ($this->_tasks as $i => $t)
	    {
	        Debug::show('Task '.$i);
	        $this->_tasks[$i]['next_time'] = $this->_build_next_time_task($i);
	        Debug::show(date('d/m/Y à H:i',$this->_tasks[$i]['next_time']));
	    }
	}
	
	/**
	 * Sélectionne et retourne l'index du prochain script à exécuter.
	 * @return int
	 */
	private function _get_next_time()
	{
	    $index = -1;
		foreach($this->_tasks as $i => $t)
		{
			if(isset($time) == FALSE || $t['next_time'] < $time)
			{
			    $time = $t['next_time'];
			    $index = $i;
			}
		}
		return $index;
	}
	
	/**
	 * Retourne le timestamp de la prochaine exécution du script.
	 * @param int $index Identifiant de la tâche.
	 * @return int 
	 */
	private function _build_next_time_task($index)
	{
    	$year_now = date("Y");
    	$month_now = date("m");
    	$day_now = date("d");
    	$hour_now = date("H");
    	$minute_now = date("i")+1;
    
    	$this->_year = $year_now;
    	// $this->_next_month() incrémente au moins de 1 à chaque appel, il faut donc -1 pour débuter au mois courant.
    	$this->_month = $month_now - 1;
    	while (TRUE)
    	{
    	    $this->_next_month($index);
    	    if ($this->_month != $month_now || $this->_year != $year_now)
    	    {
    	        $this->_day = 0;
    	        if ($this->_next_day($index) == FALSE)
    	        {
    	            continue;
    	        }
    	        else
    	        {
    	            $this->_hour = -1;
    	            $this->_minute = -1;
    	            $this->_next_hour($index);
    	            $this->_next_minute($index);
    	            return mktime($this->_hour, $this->_minute, 0, $this->_month, $this->_day, $this->_year);
    	        }
    	    }  
            else
            {
                $this->_day = $day_now - 1;
                while ($this->_next_day($index))
                {
                    if ($this->_day > $day_now)
                    {
                    	$this->_hour = -1;
        	            $this->_minute = -1;
        	            $this->_next_hour($index);
        	            $this->_next_minute($index);
        	            return mktime($this->_hour, $this->_minute, 0, $this->_month, $this->_day, $this->_year);
                    }
                    if ($this->_day == $day_now)
                    {
                    	$this->_hour = $hour_now - 1;
                    	while ($this->_next_hour($index))
                    	{
                    		if ($this->_hour > $hour_now)
                    		{
                    			$this->_minute = -1;
                	            $this->_next_minute($index);
                	            return mktime($this->_hour, $this->_minute, 0, $this->_month, $this->_day, $this->_year);
                    		}
                    		if ($this->_hour == $hour_now)
                    		{
                    			$this->_minute = $minute_now - 1;
                    			while ($this->_next_minute($index))
                    			{
                    				if ($this->_minute > $minute_now)
                    				{
                    				    return mktime($this->_hour, $this->_minute, 0, $this->_month, $this->_day, $this->_year);
                    				}
                    				if ($this->_minute == $minute_now)
                    				{
                    					$this->_execute($index);
                    				}
                    			}
                    		}
                    	}
                    }
                }
            }
    	}	
	}
    	  
	/**
	 * Fournie les valeurs valides pour un interval.
	 * @param int $min Valeur minimale.
	 * @param int $max Valeur maximale.
	 * @param string $interval Valeur de l'interval : un chiffre, '*' pour tout et a-b pour les valeurs de a jusqu'à b.
	 * @return array Liste des valeurs de l'interval en clé et un boolean pour savoir si elles correspondent
	 */
	private function _get_interval($min, $max, $interval)
	{
	    $range = array();
	    if ($interval == '*')
	    {
	        for($i=$min; $i <= $max; $i++)
	        {
	            $range[$i] = TRUE;
	        }
	    }
	    else
	    {
            for($i=$min; $i <= $max; $i++)
	        {
	            $range[$i] = FALSE;
	        }
	        if (strpos($interval, '-') !== FALSE)
	        {
	        	$val = explode('-', $interval);
	        	if (count($val) == 2)
	        	{
	        	    for($i=0; $i < 2; $i++)
	        	    {
	        	        if ($val[$i] < $min)
	        	        {
	        	        	$val[$i] = $min;
	        	        }
	        	        elseif ($val[$i] > $max)
	        	        {
	        	        	$val[$i] = $max;
	        	        }
	        	    }
                    if ($val[0] <= $val[1])
                    {
                        for($i=$val[0]; $i <= $val[1]; $i++) 
                        {
                            $range[$i] = TRUE; 
                        }
                    }
                    else
                    {
                        for($i=$val[0]; $i <= $max; $i++) 
                        {
                            $range[$i] = TRUE;     
                        }
                        for($i=$min; $i <= $val[1]; $i++) 
                        {
                            $range[$i] = TRUE;    
                        }
                    }
	        	}
	        }
	        elseif (is_numeric($interval) && $interval >= $min && $interval <= $max)
	        {
	            $range[$interval] = TRUE;
	        }
	    }
	    return $range;
	}
	
	/**
	 * Calcule le mois précédant qui correspond à l'interval.
	 * @param int $index Identifiant de la tâche.
	 * @return boolean
	 */
	private function _next_month($index)
	{
	    $range = $this->_get_interval(1, 12, $this->_tasks[$index]['month']);
	    do
	    {
	        $this->_month++;
	        if ($this->_month == 13)
	        {
	        	$this->_month = 1;
	        	$this->_year++;
	        }
	    }
	    while ($range[$this->_month] == FALSE);
	    return TRUE;
	}
	
	/**
	 * Calcule le jour précédant qui correspond à l'interval.
	 * @param int $index Identifiant de la tâche.
	 * @return boolean
	 */
	private function _next_day($index)
	{
		$day_range = $this->_get_interval(1, 31, $this->_tasks[$index]['day']);
		// Jour de la semaine dans PHP 0-6 (dimanche = 0).
		$weekday_range = $this->_get_interval(0, 6, $this->_tasks[$index]['weekday']);
		do
		{
			$this->_day++;
			// On quite si on dépasse le nombre de jour dans le mois.
			if ($this->_day == date('t', mktime(0, 0, 0, $this->_month, 1, $this->_year))+1)
			{
			    return FALSE;
			}
			$wd = date('w', mktime(0, 0, 0, $this->_month, $this->_day, $this->_year));
		}
		while($day_range[$this->_day] == FALSE || $weekday_range[$wd] == FALSE);
		return TRUE;
	}
	
	/**
	 * Calcule l'heure précédante qui correspond à l'interval.
	 * @param int $index Identifiant de la tâche.
	 * @return boolean
	 */
	private function _next_hour($index)
	{
	    $range = $this->_get_interval(0, 23, $this->_tasks[$index]['hour']);
	    do
	    {
	    	$this->_hour++;
	    	if ($this->_hour == 24)
	    	{
	    	    return FALSE;
	    	}
	    }
	    while($range[$this->_hour] == FALSE);
	    return TRUE;
	}
	
	/**
	 * Calcule la minute précédante qui correspond à l'interval.
	 * @param int $index Identifiant de la tâche.
	 * @return boolean
	 */
	private function _next_minute($index)
	{
		$range = $this->_get_interval(0, 59, $this->_tasks[$index]['minute']);
		do
		{
			$this->_minute++;
			if ($this->_minute == 60)
			{
				return FALSE;
			}
		}
		while($range[$this->_minute] == FALSE);
		return TRUE;
	}
	
	/**
	 * Exécute la tâche.
	 * @param int $index Identifiant de la tâche.
	 */
	private function _execute($index)
	{
	    Debug::show('exec');
	    $this->_stop();
	    $begin = time();
	    if (is_callable($this->_tasks[$index]['call']))
	    {
	        call_user_func($this->_tasks[$index]['call']);
	    }
	    // Evite la double exécution par minute.
	    $end = time();
	    if ($end - $begin < 60)
	    {
	        sleep($begin + 60 - $end); 
	    }
	}
	
	/**
	 * Stoppe le Cron si un fichier NAME_CRON_END est présent dans le dossier des logs des crons.
	 */
	private function _stop()
	{
	    Debug::show('stop');
	    if (file_exists(self::$_dir.self::NAME_CRON_END))
	    {
	        $this->__destruct();
	        exit();
	    }
	}
	
	/**
	 * Définie le dossier de sauvegarde des logs.
	 * @param string $dir Chemin du dossier.
	 * @return boolean
	 */
	public static function set_dir($dir)
	{
	    if (file_exist($dir) && is_dir($dir))
	    {
	        self::$_dir = (substr($dir, -1) != '/') ? ($dir.'/') : ($dir);
	        return TRUE;
	    }
	    return FALSE;
	}
	
	
	public static function task1()
	{
		$i = 0;
		while (file_exists('task1-'.$i.'.txt')) $i++;
		file_put_contents('task1-'.$i.'.txt', time());
	}
	
}