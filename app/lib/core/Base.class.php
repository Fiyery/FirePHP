<?php
/**
 * Base est l'interface de connexion et de requetage à la base de données.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses BaseException
 */
class Base 
{	
	/**
	 * Temps par défaut en seconde pour la sauvegarde du cache.
	 * @var int
	 */
	const DEFAUT_TIME_CACHE = 3600;
	
	/**
	 * Variable de compteur du temps des requêtes.
	 * @var int
	 */
	private $_time = 0;

	/**
	 * Message d'erreur.
	 * @var string
	 */
	private $_error = NULL;
	
	/**
	 * Liste des tables et de leurs liaisons.
	 * @var array<string>
	 */
	private $_tables = NULL;
	
	/**
	 * Liste des requêtes exécutées.
	 * @var array
	 */
	private $_history = [];

	/**
	 * Instance de PDO pour la connexion
	 * @var PDO
	 */
	private $_connection = NULL;
	
	/**
	 * Encodage de la base de données.
	 * @var string
	 */
	private $_charset = 'utf-8';
	
	/**
	 * Adresse du serveur de la base de données.
	 * @var string
	 */
	private $_host = 'localhost';
	
	/**
	 * Nom de la base de données.
	 * @var string
	 */
	private $_name = NULL;
	
	/**
	 * Nom du système de gestion de base de données.
	 * @var string
	 */
	private $_engine = 'mysql';
	
	/**
	 * Définit si les requêtes seront mise en cache.
	 * @var string
	 */
	private $_cache_enable = FALSE;
	
	/**
	 * Définit le temps de cache des requêtes.
	 * @var int
	 */
	private $_cache_time = self::DEFAUT_TIME_CACHE;
	
	/**
	 * Nom du système de gestion de base de données.
	 * @var string
	 */
	private $_cache_dir = FALSE;
	
	/**
	 * Constructeur.
	 */
	public function __construct()
	{
        
	}
	
	/**
	 * Connecte une base de données.
	 * @throws PDOException
	 * @param string $host Nom du domaine.
	 * @param string $name Nom de la base de données.
	 * @param string $user Nom de l'utilisateur.
	 * @param string $pass Mot de passe.
	 * @param string $charset Encodage de la base.
	 * @param string $engine Nom du système de base de données.
	 */
	public function connect($host, $name, $user, $pass, $charset='utf-8', $engine='mysql')
	{
		if ($name == '')
		{
			return FALSE;
		}
		$this->_connection = new PDO($engine.":host=".$host.";dbname=".$name, $user, $pass);
		$this->_charset = strtolower($charset);
		$this->_tables = [];
		$this->_host = $host;
		$this->_name = $name;
		$this->_engine = $engine;	
		if ($charset === 'utf-8')
		{
			$sql = "SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'";
			$this->query($sql, $name);
		}
		return TRUE;
	}

	/**
	 * Exécute une requête sql. 
	 * @param string $sql Requête sql à exécuté.
	 * @param array $value Tableau contenant les valeurs "?" vérifier par PDO.
	 * @return boolean|array Retourne le résultat de la requête, TRUE si cette dernière ne retourne rien, ou FALSE s'il y a une erreur. 
	 * @throws BaseException
	 */
	public function query($sql, $value=NULL)
	{
		$this->_check_connection();
		$history = ['sql'=>$sql];	
		$cache = $this->_read_cache($sql);
		if (is_array($cache))
		{
		    $history['time'] = -1;
		    $this->_history[] = $history;
		    return $cache;
		}
		else
		{
			// Vérification du nombre de paramètres.
			if (substr_count($sql, '?') > count($value))
			{
				throw new BaseException("Il manque des paramètres pour la requête préparée");
			}
		    $time = microtime(TRUE);
		    $bd = $this->_connection;
		    $res = $bd->prepare($sql);
	    	if (is_array($value) && count($value) > 0)
	    	{
	    		$res->execute(array_values($value));
	    	}
	    	else
	    	{
	    		$res->execute();
	    	}
		    $end_time = microtime(TRUE);
		    $this->_time += $end_time - $time;
		    $history['time'] = $end_time - $time;
		    $this->_history[] = $history;
		    $this->_error = $res->errorInfo();
		    if ($res->rowCount() > 0)
		    {
		    	$result = $res->fetchAll(PDO::FETCH_ASSOC);
		    }
		    elseif ($res->errorCode() == '00000')
		    {
		    	$result = TRUE;
		    }
		    else
		    {
		    	$result = FALSE;
		    }
		    $this->_write_cache($sql, $result);
		    $this->_cache_time = 0;
		    return $result;
		}
	}
	
	/**
	 * Sauvegarde de la base de donnée.
	 * @param string $filename Chemin de l'exécutable MySQLDump.
	 * @param array $excludes Liste des tables à exclure de la sauvegarde.
	 * @return string Contenu de la sauvegarde de la base de données.
	 * @throws BaseException
	 */
	public function save($filename=NULL, $excludes=NULL)
	{
	    $this->_check_connection();
	    $save = '';
	    if (empty($save))
	    {
        	$pdo = $this->_connection;
        	$bd_save = '-- SAUVEGARDE de '.$this->_name.' le '.date('d/m/Y à H:G:s')."\n";
        	$tables = $this->get_tables();
        	$res = $pdo->prepare( "SHOW CREATE DATABASE `".$this->_name."`;");
        	$res->execute();
        	$create_database = $res->fetchAll(PDO::FETCH_NUM);
        	$bd_save .= str_replace('CREATE DATABASE','CREATE DATABASE IF NOT EXISTS', $create_database[0][1]);
        	$bd_save .= "\nUSE `".$this->_name."`;\n";
        	foreach ($tables as $t)
        	{
        		$bd_save .= "\n\n".'DROP TABLE IF EXISTS `'.$t.'`;'."\n";
        		$res = $pdo->prepare('SHOW CREATE TABLE `'.$t.'`;');
        		$res->execute();
        		$create_table = $res->fetchAll(PDO::FETCH_NUM);
        		$bd_save .= $create_table[0][1]."\n";
        		$res = $pdo->prepare('SELECT * FROM `'.$t.'`;');
        		$res->execute();
        		if ($res->rowCount() > 0)
        		{
        			$rows = $res->fetchAll(PDO::FETCH_NUM);
        			$insert = 'INSERT INTO `'.$t.'` VALUES '."\n";
        			foreach ($rows as $r)
        			{
        				$insert .= "(";
        				$values = array();
        				foreach ($r as $c)
        				{
        					$values[] = str_replace("'","\'",$c);
        				}
        				$insert.= "'".implode("','",$values)."'),\n";
        			}
        			$bd_save .= substr($insert,0 , -2);
        			$bd_save .=";\n";
        		}
        	}
        	$save .= ($value['charset'] != 'utf-8') ? (utf8_encode($data)) : ($bd_save); 
	    }
		return $save;
	}
	
	/**
	 * Retourne le moteur de la base de données.
	 * @return string
	 */
	public function engine()
	{
		return $this->_engine;
	}
	
	/**
	 * Retourne le nom de la base de données.
	 * @return string
	 */
	public function name()
	{
		return $this->_name;
	}

	/**
	 * Retourne la liste les tables de la base de données.
	 * @return array<string> Liste des tables.
	 * @throws BaseException
	 */
	public function tables()
	{
		$this->_check_connection();
		if (count($this->_tables) === 0)
		{
			$res = $this->cache(604800)->query('SHOW TABLES');
			if ($res !== FALSE && is_array($res))
			{
				while (($v = current($res)))
				{
					$v = array_values($v);
					$tab[$v[0]] = [];
					next($res);
				}
				$this->_tables = $tab;
			}
		}
		return $this->_tables;
	}

	/**
	 * Retourne la liste des champs d'une table.
	 * @param string $name Nom de la table.
	 * @return array Information sur les colonnes.
	 * @throws BaseException
	 */
	public function fields($name)
	{
		$this->_check_connection();
		$name = strtolower($name);
		if (array_key_exists($name, $this->_tables) === FALSE)
		{
			$res = $this->cache(604800)->query('DESCRIBE `'.$name.'`;');
			$this->_tables[$name] = (is_array($res)) ? ($res) : (NULL);
		}
		return $this->_tables[$name];
	}
	
	/**
	 * Retourne l'ensemble des tables ayant une clé étrangère sur la table passée en paramètre.
	 * @param string $table Nom de la table.
	 * @return array La liste des clés étrangères et de leurs table.
	 * @throws BaseException
	 */
	public function foreign_key($table)
	{
		$this->_check_connection();
		$constraintes = $this->query("
			SELECT
				-- k.CONSTRAINT_SCHEMA,
				-- k.CONSTRAINT_NAME,
				k.TABLE_NAME,
				k.COLUMN_NAME,
				-- k.REFERENCED_TABLE_SCHEMA,
				-- k.REFERENCED_TABLE_NAME,
				k.REFERENCED_COLUMN_NAME
			FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS k
			INNER JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS AS c ON k.CONSTRAINT_SCHEMA = c.CONSTRAINT_SCHEMA
			AND k.CONSTRAINT_NAME = c.CONSTRAINT_NAME
			WHERE
				c.CONSTRAINT_TYPE = 'FOREIGN KEY'
				AND k.REFERENCED_TABLE_SCHEMA = '".$this->_name."'
				AND k.REFERENCED_TABLE_NAME = '".$table."'
		");
		return (is_array($constraintes)) ? ($constraintes) : ([]);
	}
	
	/**
	 * Retourne le message d'erreur de la dernière requête exécutée.
	 * @return string
	 */
	public function error()
	{
		return $this->_error;
	}

	/**
	 * Retourne l'historique des requêtes;
	 * @return array Liste des requêtes sql et leur temps d'exécution en seconde.
	 */
	public function history()
	{
		return $this->_history;
	}
	
	/**
	 * Retourne le nombre de requêtes effectuées.
	 * @return int
	 */
	public function count()
	{
		return count($this->_history);
	}
	
	/**
	 * Retourne le nombre totale de temps passé sur les requêtes.
	 * @return int Temps en ms.
	 */
	public function time()
	{
		return $this->_time;
	}
	
	/**
	 * Retourne le dernier identifiant inséré.
	 * @return int.
	 * @throws BaseException
	 */
	public function last_id()
	{
		$this->_check_connection();
		return $this->_connection->lastInsertId();
	}
	
	/**
	 * Retourne la version de la base de données.
	 * @return string
	 * @throws BaseException
	 */
	public function version()
	{
		$this->_check_connection();
		return $this->_connection->getAttribute(PDO::ATTR_SERVER_VERSION);
	}
	
	/**
	 * Vérifie si la table passée en paramètre existe.
	 * @param string $name Nom de la table.
	 * @return boolean
	 * @throws BaseException
	 */
	public function table_exists($name)
	{
		$this->_check_connection();
		return (is_string($name) && in_array($name, $this->get_tables()));
	}
	
	/**
	 * Définie les paramètres du cache.
	 * @param bool $enable Définit si l'on active le système de cache des requêtes.
	 * @param string $dir Dossier qui contiendra les caches.
	 * @param int $seconds Nombre de secondes.
	 * @return bool
	 */
	public function set_cache($enable=TRUE, $dir='.', $seconds=self::DEFAUT_TIME_CACHE)
	{
	    if ($enable)
	    {
    	    $this->_cache_enable = TRUE;
	    	if (is_string($dir))
    	    {
    	        if (file_exists($dir) == FALSE)
    	        {
    	            if (mkdir($dir, 0755, TRUE))
    	            {
    	                $this->_cache_dir = (substr($dir, -1) != '/') ? ($dir.'/') : ($dir);
    	            }
    	            else
    	            {
    	            	return FALSE;
    	            }
    	        }
    	        elseif (is_dir($dir))
    	        {
    	            $this->_cache_dir = (substr($dir, -1) != '/') ? ($dir.'/') : ($dir);
    	        }
    	    }    
    	    if (is_numeric($seconds))
    	    {
    	        $this->_cache_time = $seconds;
    	    }
	    }
	    else 
	    {
	    	$this->_cache_enable = FALSE;
	    }
	    return TRUE;
	}
	
	/**
	 * Définie le temps de sauvegarde du cache.
	 * @param int $seconds Nombre de secondes.
	 * @return Base
	 */
	public function cache($seconds=self::DEFAUT_TIME_CACHE)
	{
		if (is_numeric($seconds))
		{
			$this->_cache_time = $seconds;
		}
		return $this;
	}
	
	/**
	 * Réinitialise la liste des tables enregistrés en cache.
	 */
	public function reset_cache()
	{
		$this->_tables = [];
		if ($this->_cache_dir !== NULL)
		{
		    $caches = array_diff(scandir($this->_cache_dir), ['..', '.']);
		    foreach ($caches as $c)
		    {
		        unlink($this->_cache_dir.$c);
		    }
		}
	}
	
	/**
	 * Tente de récupérer le cache de la requête.
	 * @param string $sql Requête à chercher dans le cache.
	 * @return array Renvoie le résultat ou NULL.
	 */
	private function _read_cache($sql)
	{
	    if ($this->_cache_dir === NULL || $this->_cache_time <= 0)
	    {
	        return NULL;
	    }
	    $sql = trim($sql);
	    $type = strtolower(substr($sql, 0, strpos($sql, ' ')));
	    if (in_array($type, ['select', 'show', 'describe']) === FALSE)
	    {
	        return NULL;
	    }
	    $file = $this->_cache_dir.sha1($sql).'.sql.tmp';
	    if (file_exists($file) && filemtime($file) + $this->_cache_time >= time())
	    {
	    	return unserialize(file_get_contents($file));
	    }
	    return NULL;
	}
	
	/**
	 * Tente de mettre en cache la requête.
	 * @param string $sql Requête à chercher dans le cache.
	 * @param array $result Résultat de la requête.
	 * @return boolean
	 */
	private function _write_cache($sql, $result)
	{
		if ($this->_cache_dir == NULL || $this->_cache_time <= 0)
		{
			return FALSE;
		}
		$type = strtolower(substr($sql, 0, strpos($sql, ' ')));
		if (in_array($type, ['select', 'show', 'describe']) == FALSE)
		{
			return FALSE;
		}
		$file = $this->_cache_dir.sha1($sql).'.sql.tmp';
		return (file_put_contents($file, serialize($result)));
	}
	
	/**
	 * Vérifie si la base est connectées à au moins une base de donnée.
	 * @throws BaseException
	 */
	private function _check_connection()
	{
		if ($this->_connection === NULL)
		{
			$d = debug_backtrace();
			throw new BaseException('Aucune base de donnée trouvée', $d[1]['file'], $d[1]['line']);
		}
	}
}
?>