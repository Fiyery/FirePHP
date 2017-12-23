<?php
/**
 * Base est l'interface de connexion et de requetage à la base de données.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses DataBaseException
 */
class DataBase 
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
	 * @var string[]
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
	private $_charset = 'utf8mb4';
	
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
	private $_cache_dir = NULL;
	
	/**
	 * Définie si la prochaine requête sera mise en tampon (résultat chargé totalement) ou non. 
	 * @var bool
	 */
	private $_use_buffered_query = TRUE;

	/**
	 * Requête PDO.
	 * @var PDOStatement
	 */
	private $_pdo_statement = NULL;
	
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
	public function connect($host, $name, $user, $pass, $charset='utf8mb4', $engine='mysql')
	{
		if ($this->_connection === NULL)
		{
			$this->_charset = strtolower($charset);
			$this->_tables = [];
			$this->_host = $host;
			$this->_name = $name;
			$this->_engine = $engine;	
			$this->_connection = new PDO($this->_engine.":host=".$this->_host.";dbname=".$this->_name, $user, $pass);
			if ($this->_connection !== NULL)
			{
				$this->query("SET NAMES ".$this->_charset, $name);
			}
		}
	}

	/**
	 * Exécute une requête sql. 
	 * @param string $sql Requête sql à exécuté.
	 * @param array $value Tableau contenant les valeurs "?" vérifier par PDO.
	 * @return boolean|array Retourne le résultat de la requête, TRUE si cette dernière ne retourne rien, ou FALSE s'il y a une erreur. 
	 * @throws DataBaseException
	 */
	public function query(string $sql, array $value=NULL)
	{
		$this->_prepare_query($sql, $value);
		if ($this->_pdo_statement === FALSE)
		{
			 return FALSE;
		}
		if (get_class($this->_pdo_statement) === 'PDOStatement')
		{
			$result = $this->_pdo_statement->fetchAll(PDO::FETCH_ASSOC);
		}
		return $result;
	}

	/**
	 * Exécute une requête sql et retourne un interateur. 
	 * @param string $sql Requête sql à exécuté.
	 * @param array $value Tableau contenant les valeurs "?" vérifier par PDO.
	 * @return boolean|array Retourne le résultat de la requête, TRUE si cette dernière ne retourne rien, ou FALSE s'il y a une erreur. 
	 * @throws DataBaseException
	 */
	public function yield_query(string $sql, array $value=NULL)
	{
		$this->_prepare_query($sql, $value);
		if (get_class($this->_pdo_statement) === 'PDOStatement')
		{
			foreach ($this->_pdo_statement as $r)
			{
				yield (array_filter($r, function($k){
					return (is_numeric($k) === FALSE);
				}, ARRAY_FILTER_USE_KEY));
			}
		}
		return $result;
	}

	public function _prepare_query($sql, $value=NULL)
	{
		$this->_check_connection();
		$history = $this->_format_query_history($sql, $value);
		$cache = $this->_read_cache($sql);
		if (is_array($cache))
		{
		    $history['time'] = -1;
		    $this->_history[] = $history;
		    $this->_pdo_statement = $cache;
		}
		else
		{
			// Vérification du nombre de paramètres.
			if (substr_count($sql, '?') > count($value))
			{
				throw new DataBaseException("Il manque des paramètres pour la requête préparée");
			}
		    $time = microtime(TRUE);
		    $pdo = $this->_connection;
			if ($this->_use_buffered_query === FALSE)
			{
				$pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, FALSE);
			}
			$this->_pdo_statement = $pdo->prepare($sql);
	    	if (is_array($value) && count($value) > 0)
	    	{
	    		$this->_pdo_statement->execute(array_values($value));
	    	}
	    	else
	    	{
	    		$this->_pdo_statement->execute();
	    	}
			$pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, TRUE);
		    $end_time = microtime(TRUE);
		    $this->_time += $end_time - $time;
		    $history['time'] = $end_time - $time;
		    $this->_history[] = $history;
		    $this->_error = $this->_pdo_statement->errorInfo();
		}
	}

	/**
	 * Format la requête SQL avec les valeurs.
	 * @param string $sql Requete SQL.
	 * @param string[] $values Liste des valeurs.
	 * @return string
	 */
	private function _format_query_history($sql, $values)
	{
		if (is_array($values))
		{
			while (($pos = strpos($sql, '?')) !== FALSE && count($values) > 0)
			{
				$v = str_replace("'", "''", array_shift($values));
				if (is_string($v) && substr($sql, $pos - 1, 3) === ' ? ')
				{
					$v = "'".$v."'";
					$sql = substr($sql, 0, $pos - 1).' '.$v.' '.substr($sql, $pos + 2);
				}
				else
				{
					$sql = substr($sql, 0, $pos ).$v.substr($sql, $pos + 1);
				}
			}
		}
		return ['sql' => $sql];
	}
	
	/**
	 * Sauvegarde de la base de donnée.
	 * @param string $filename Chemin de l'exécutable MySQLDump.
	 * @param array $excludes Liste des tables à exclure de la sauvegarde.
	 * @return string Contenu de la sauvegarde de la base de données.
	 * @throws DataBaseException
	 */
	public function save($filename=NULL, $excludes=NULL)
	{
	    $this->_check_connection();
	    $save = '';
	    if (empty($save))
	    {
        	$pdo = $this->_connection;
        	$bd_save = '-- SAUVEGARDE de '.$this->_name.' le '.date('d/m/Y à H:G:s')."\n";
        	$tables = $this->tables();
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
	 * @return string[] Liste des tables.
	 * @throws DataBaseException
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
		return array_keys($this->_tables);
	}

	/**
	 * Retourne la liste des champs d'une table.
	 * @param string $name Nom de la table.
	 * @return array Information sur les colonnes.
	 * @throws DataBaseException
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
	 * Retourne l'ensemble des clés étrangère de la table passée en paramètre.
	 * @param string $table Nom de la table.
	 * @return array La liste des clés étrangères et de leur table.
	 * @throws DataBaseException
	 */
	public function foreign_keys($table)
	{
		$this->_check_connection();
		$constraintes = $this->query("
			SELECT
				-- k.CONSTRAINT_SCHEMA,
				-- k.CONSTRAINT_NAME,
				-- k.TABLE_NAME,
				k.COLUMN_NAME,
				-- k.REFERENCED_TABLE_SCHEMA,
				k.REFERENCED_TABLE_NAME,
				k.REFERENCED_COLUMN_NAME
			FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS k
			INNER JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS AS c ON k.CONSTRAINT_SCHEMA = c.CONSTRAINT_SCHEMA
			AND k.CONSTRAINT_NAME = c.CONSTRAINT_NAME
			WHERE
				c.CONSTRAINT_TYPE = 'FOREIGN KEY'
				AND k.REFERENCED_TABLE_SCHEMA = '".$this->_name."'
				AND k.TABLE_NAME = '".$table."'
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
	 * @return int
	 * @throws DataBaseException
	 */
	public function last_id()
	{
		$this->_check_connection();
		return $this->_connection->lastInsertId();
	}
	
	/**
	 * Retourne la version de la base de données.
	 * @return string
	 * @throws DataBaseException
	 */
	public function version()
	{
		if ($this->_connection === NULL)
		{
			return NULL;
		}
		return $this->_connection->getAttribute(PDO::ATTR_SERVER_VERSION);
	}
	
	/**
	 * Vérifie si la table passée en paramètre existe.
	 * @param string $name Nom de la table.
	 * @return boolean
	 * @throws DataBaseException
	 */
	public function table_exists($name)
	{
		$this->_check_connection();
		return (is_string($name) && in_array($name, $this->tables()));
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
	 * @return DataBase
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
		if ($this->_cache_dir !== NULL && file_exists($this->_cache_dir))
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
	 * @throws DataBaseException
	 */
	private function _check_connection()
	{
		if ($this->_connection === NULL)
		{
			throw new DataBaseException('Aucune base de donnée trouvée', 2);
		}
	}

	/**
	 * Retourne le dernier id inséré.
	 * @param string $base Nom de la base.
	 * @return string
	 */
	public function last_insert_id() : string
	{
		$this->_check_connection();
		return $this->_connection->lastInsertId();
	}

	/**
	 * Retourne le nombre d'enregistrement affectés.
	 * @param string $base Nom de la base.
	 * @return int
	 */
	public function row_count() : int
	{
		return $this->_pdo_statement->rowCount();
	}

	/**
	 * Ouvre une requête non bufferisée qui doit impérativement être fermé.
	 */
	public function open_unbuffered_query()
	{
		$this->_use_buffered_query = FALSE;
	}

	/**
	 * Définie si les requêtes doit être bufferisée.
	 * @param bool $bool 
	 */
	public function close_unbuffered_query()
	{
		$this->_pdo_statement->closeCursor();
	}
}
?>