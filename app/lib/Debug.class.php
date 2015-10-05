<?php
/**
 * Debug gère la gestion du debugage du site par les affichages de variable, de la consommation en mémoire, du temps d'exécution d'un bout de code, l'affichage du backtrace et la création manuelle de log.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @copyright 2011-2015 Yoann Chaumin
 */
class Debug 
{
	/**
	 * Dossier de débugage.
	 * @var string
	 */
	public static $debug_dir = './';
	
	/**
	 * Variable de stockage du démarrage du compteur.
	 * @var int
	 */
	private static $time1 = -1;
	
	/**
	 * Variable de cache du calcul de la limite de mémoire allouée à PHP.
	 * @var int
	 */
	private static $memory_limit = NULL;
	
	/**
	 * Constructeur.
	 */
	public function __construct()
	{
		 
	}
	
	/**
	 * Démarre le compteur du temps d'exécution.
	 */
	public static function start_time()
	{
		self::$time1 = microtime(TRUE);
	}
	
	/**
	 * Calcul le temps d'exécution d'un bout de code de start_time() jusqu'à l'appel de cette fonction.
	 * @return number Temps d'exécution du code en seconde.
	 */
	public static function end_time()
	{
		$t2 = microtime(TRUE);
		$diff = (self::$time1 != -1) ? ($t2-self::$time1) : (0);
		$i = 5;
		while (number_format($diff,$i) == 0)
		{
			$i++;
		}
		return $diff;
	}
	
	/**
	 * Écrit le résultat du temps de chargement dans un fichier pour faire une moyenne.
	 * @param string $name Nom du fichier.
	 */
	public static function write_time($name=NULL)
	{
		if ($name == NULL)
		{
			return FALSE;
		}
		$name_file = 'Debug_'.$name;
		$fp = fopen($name_file,'a+');
		if (self::$time1 != -1) 
		{
    		fwrite($fp,microtime(TRUE)-self::$time1."\n");
    		fclose($fp);
		}
	}
	
	/**
	 * Affiche puis retourne la moyenne des stats d'un fichier de stats.
	 * @param string $name Nom du fichier.
	 * @return boolean|number Temps moyen d'exécution en seconde
	 */
	public static function average($name=NULL)
	{
		$name_file = 'Debug_'.$name;
		if (empty($name_file) || !file_exists($name_file) || !is_readable($name_file))
		{
			return FALSE;
		}
		$tab = file($name_file);
		if (count($tab) == 0)
		{ 
		    return FALSE;
		}
		$nb = 0;
		$som = 0;
		foreach ($tab as $f)
		{
			$som += $f;
			$nb++;
		}
		$som = $som/count($tab);
		echo 'Moyenne du fichier '.$name_file.' pour '.$nb.' enregistrements : '.number_format($som,3).' seconde(s).<br/>';
		return $som;
	}
	
	/**
	 * Affiche le paramètre puis le retourne.
	 * @param mixed $var Variable à afficher.
	 * @param boolean $hide Si TRUE, aucun affichage sinon la variable est affichée si FALSE.
	 * @return string Affichage de la variable.
	 */
	public static function show($var,$hide=FALSE)
	{
		$chaine = '<p><pre>';
		if (is_array($var) || is_object($var))
		{
			$chaine .= print_r($var,TRUE);
		}
		elseif ($var === FALSE)
		{
			$chaine .= "FALSE";
		}
		elseif ($var === TRUE)
		{
			$chaine .= "TRUE";
		}
		elseif (is_numeric($var))
		{
			$chaine .= $var;
		}
		elseif ($var === NULL)
		{
			$chaine .= "NULL";
		}
		elseif (empty($var))
		{
			$chaine .= "EMPTY";
		}
		else
		{
			$var = htmlentities($var);
			$chaine .= $var;
		}
		$chaine .= '</pre></p>';
		if ($hide == FALSE)
		{
			echo $chaine;
		}
		return $chaine;
	}
	
	/**
	 * Affiche puis retourne les appels de fonctions jusqu'au script courant.
	 * @return string Trace de l'appel de cette fonction.
	 */
	public static function trace()
	{
		ob_start();
		debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		$trace = ob_get_clean();
		$trace = str_replace('#','<br/><div><b><u>',$trace);
		$trace = str_replace('called at [','called at : </u></b><ul><li>File : ',$trace);
		$trace = preg_replace('/:([0-9]*)\]/','</li><li>Ligne : $1</li></ul></div>',$trace);
		echo $trace;
		return $trace;
	}
	
	/**
	 * Duplique tous les affichages avec show() dans le fichier debug_show.txt.
	 * @param boolean $buffer Si TRUE, le buffer fichier est activé.
	 * @param boolean $clean Si TRUE, le buffer fichier est vidé.
	 */
	public static function buffer($buffer=TRUE,$clean=FALSE)
	{
		self::$buffer = $buffer;
		if ($clean && file_exists('debug_show.txt'))
		{
			unlink('debug_show.txt');
		}
	}
	
	/**
	 * Ecrit dans le buffer fichier.
	 * @param string $string Chaîne à écrire.
	 * @param boolean $reset Si TRUE, le buffer fichier est vidé.
	 */
	public static function log($string,$reset=FALSE)
	{
		$fp = ($reset) ? (fopen('debug_show.txt','w+')) : (fopen('debug_show.txt','a+'));
		$string = str_replace(array('<pre>','<br/>','</pre>'),'',$string);
		fwrite($fp,$string."\n");
		fclose($fp);
	}
	
	/**
	 * Calcule le pourcentage d'utilisation du script par rapport à la mémoire maximale de PHP et l'affiche puis le retourne.
	 * @return int Pourcentage d'utilisation du script.
	 */
	public static function memory()
	{
		if (self::$memory_limit == NULL)
		{
			self::$memory_limit = ini_get('memory_limit');
			self::$memory_limit = (substr(self::$memory_limit,-1) == 'M') ? (self::$memory_limit*1048576) : (self::$memory_limit);
		}
		$percent = number_format((memory_get_usage()*100)/self::$memory_limit);
		echo '<div>Utilisation de la mémoire est de <strong>'.$percent.'%</strong> de PHP.</div>';
		return $percent;
	}
	
	/**
	 * Retourne la memoire maximale utilisée.
	 * @return string Mémoire utilisée formatée en octet.
	 */
	public static function memory_max_usage()
	{
	    $size = memory_get_peak_usage();
		$unit = array('o','Ko','Mo','Go', 'To');
		$i = floor(log($size, 1024));
		$size = round($size/pow(1024, $i), 2);
		$size .= ' '.$unit[$i];
		return $size;
		return self::$max_memory;
	}
	
	/**
	 * Retourne l'appel précedent en fonction de la position.
	 * @param int $pos Nombre de saut en arrière.
	 * @return array<string> Liste des paramètre de l'appelant.
	 */
	public static function get_caller($pos=1)
	{
	    $debug = debug_backtrace();
	    return (isset($debug[$pos])) ? ($debug[$pos]) : (array());
	}
}
?>