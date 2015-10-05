<?php
/**
 * Base est l'interface de connexion et de requetage à la base de données.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @copyright 2011-2015 Yoann Chaumin
 * @uses Singleton
 */
class Base extends Singleton
{
	/**
	 * Variable d'instance de singleton.
	 * @var Base
	 */
	protected static $_instance = NULL;
	
	/**
	 * Variable de comptage du temps des requêtes.
	 * @var int
	 */
	private $_time = 0;

	/**
	 * Message d'erreur.
	 * @var string
	 */
	private $_error = NULL;
	
	/**
	 * Liste des bases.
	 * @var array
	 */
	private $_data_bases = NULL;
	
	/**
	 * Nombre de base de données.
	 * @var int
	 */
	private $_data_bases_nb = 0;
	
	/**
	 * Liste des tables et de leurs liaisons.
	 * @var array<string>
	 */
	private $_tables = NULL;
	
	/**
	 * Liste des requêtes exécutées.
	 * @var array
	 */
	private $_history = array();
	
	/**
	 * Dossier de cache.
	 * @var string
	 */
	private $_cache_dir = NULL;
	
	/**
	 * Temps de mise en cache qui est réinitiaisé à chaque appel de requête.
	 * @var int
	 */
	private $_cache_time = 0;
	
	/**
	 * Constructeur.
	 */
	protected function __construct()
	{
		$this->_tables = array();
	}

	/**
	 * Exécute une requête sql. 
	 * @param string|Query $sql Requête sql à exécuté ou un objet Query.
	 * @param string $name_base Nom de la base.
	 * @param array $value Tableau contenant les valeurs vérifier par PDO.
	 * @return boolean|array Retourne le résultat de la requête, TRUE si cette dernière ne retourne rien, ou FALSE s'il y a une erreur. 
	 */
	public function query($sql,$name_base=NULL,$value=NULL)
	{
		$this->_check_connection();
		$history = array('sql'=>$sql);
		// Vérification des paramètres.
		if (is_string($name_base) == FALSE || array_key_exists($name_base, $this->_data_bases) ==  FALSE)
		{
			if (is_object($sql) == FALSE || get_class($sql) != 'Query')
			{
				if ($this->_data_bases_nb > 1)
				{
					$this->_error = 'Invalide params';
					$this->_cache_time = 0;
					return FALSE;
				}
			}
		}		
		// Auto-assignement de valeur de $name_base si variable non-remplie.
		if (is_object($sql))
		{
			$name_base = $sql->get_base();
		}
		elseif ($this->_data_bases_nb == 1)
		{
			reset($this->_data_bases);
			$name_base = key($this->_data_bases);
		}
		if (array_key_exists($name_base, $this->_data_bases) == FALSE)
		{
			$this->_error = 'Invalide base';
			$this->_cache_time = 0;
			return FALSE;
		}
		$cache = $this->_read_cache($name_base, $sql);
		if (is_array($cache))
		{
		    $history['time'] = -1;
		    $this->_history[] = $history;
		    $this->_cache_time = 0;
		    return $cache;
		}
		else
		{
		    $time = microtime(TRUE);
		    $bd = $this->_data_bases[$name_base]['connexion'];
		    $res = $bd->prepare($sql);
		    if (is_array($value) && count($value) > 0)
		    {
		    	$res->execute($value);
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
		    $this->_write_cache($name_base, $sql, $result);
		    $this->_cache_time = 0;
		    return $result;
		}
	}
	
	/**
	 * Sauvegarde de la base de donnée.
	 * @param string $filename Chemin de l'exécutable MySQLDump.
	 * @param array $excludes Liste des tables à exclure de la sauvegarde.
	 * @return string Contenu de la sauvegarde de la base de données.
	 */
	public function save($filename=NULL, $excludes=NULL)
	{
	    $this->_check_connection();
	    $begin_cmd = '';
	    $save = '';
	    if (stripos(php_uname('s'), 'windows') !== FALSE && file_exists($filename))
	    {
	    	$begin_cmd = $filename;
	    }
	    else
	    {
	    	$begin_cmd = 'mysqldump';
	    }
	    if (empty($begin_cmd) == FALSE)
	    {
	    	foreach ($this->_data_bases as $name => $v)
	    	{
	    		$cmd = $begin_cmd.' --no-tablespace --opt -h"'.$v['host'].'"';
	    		$cmd .= ' -u'.$v['user'].' -p"'.$v['pass'].'" "'.$name.'"';
	    		if (is_array($excludes))
	    		{
	    			foreach ($excludes as $e)
	    			{
	    				$cmd .= ' --ignore-table='.$name.'.'.$e;
	    			}
	    		}
	    		ob_start();
	    		system($cmd);
	    		$save .= ob_get_clean();
	    	}
	    		
	    }
	    if (empty($save))
	    {
	        foreach ($this->_data_bases as $name_base => $value)
	        {
	        	$pdo = $value['connexion'];
	        	$bd_save = '-- SAUVEGARDE de '.$name_base.' le '.date('d/m/Y à H:G:s')."\n";
	        	$tables = $this->get_tables($name_base);
	        	$res = $pdo->prepare( "SHOW CREATE DATABASE `".$name_base."`;");
	        	$res->execute();
	        	$create_database = $res->fetchAll(PDO::FETCH_NUM);
	        	$bd_save .= str_replace('CREATE DATABASE','CREATE DATABASE IF NOT EXISTS', $create_database[0][1]);
	        	$bd_save .= "\nUSE `".$name_base."`;\n";
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
	    }
		return $save;
	}

	/**
	 * Retourne la liste les tables des bases de données.
	 * @param string $base_name Nom de la base. Si NULL, la liste portera sur toutes les bases.
	 * @return array<string> Liste des tables.
	 */
	public function get_tables($base_name=NULL)
	{
		$this->_check_connection();
		$list_tables = array();
		if ($base_name == NULL)
		{
			foreach ($this->_data_bases as $b => $v)
			{
				if ($this->_data_bases[$b]['tables'] == NULL)
				{
					$res = $this->cache(604800)->query('SHOW TABLES',$b);
					$tab = array();
					if (is_array($res))
					{
						foreach ($res as $v)
						{
							$v = array_values($v);
							$tab[] = $v[0];
						}
						$this->_data_bases[$b]['tables'] = $tab;
					}
				}
				$list_tables = array_merge($list_tables,$this->_data_bases[$b]['tables']);
			}
		}
		else
		{
			if (isset($this->_data_bases[$base_name]) != FALSE)
			{
				if ($this->_data_bases[$base_name]['tables'] == NULL)
				{
					$res = $this->cache(604800)->query('SHOW TABLES',$base_name);
					if ($res !== FALSE && is_array($res))
					{
						$list_tables = $res;
						while (($v = current($res)))
						{
							$v = array_values($v);
							$tab[] = $v[0];
							next($res);
						}
						$this->_data_bases[$base_name]['tables'] = $tab;
					}
				}
				$list_tables = $this->_data_bases[$base_name]['tables'];
			}
		}
		return $list_tables;
	}

	/**
	 * Retourne la liste des champs d'une table.
	 * @param string $name Nom de la table.
	 * @return array Information sur les colonnes.
	 */
	public function get_fields($name)
	{
		$this->_check_connection();
		$name = strtolower($name);
		if (array_key_exists($name, $this->_tables) === FALSE)
		{
			$res = $this->cache(604800)->query('DESCRIBE `'.$name.'`;', $this->select_base($name));
			$this->_tables[$name] = (is_array($res)) ? ($res) : (NULL);
		}
		return $this->_tables[$name];
	}
	
	/**
	 * Retourne l'ensemble des tables ayant une clé étrangère sur la table passée en paramètre.
	 * @param string $table Nom de la table.
	 * @param string $base Nom de la base de données.
	 * @return array La liste des clés étrangères et de leurs table.
	 */
	public function get_foreign_key($table, $base=NULL)
	{
		if (is_string($base) && is_null($base) == FALSE)
		{
			$base = array($base);
		}
		else
		{
			$base = $this->get_bases();
		}
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
				AND k.REFERENCED_TABLE_SCHEMA IN ('".implode("','", $base)."')
				AND k.REFERENCED_TABLE_NAME = '".$table."'
		");
		return (is_array($constraintes)) ? ($constraintes) : (array());
	}
	
	/**
	 * Retourne la liste des bases de données
	 * @return array<string> Tableau des noms de base de données.
	 */ 
	public function get_bases()
	{
		$this->_check_connection();
		return array_keys($this->_data_bases);
	}
	
	/**
	 * Retourne le message d'erreur de la dernière requête exécutée.
	 * @return string
	 */
	public function get_error()
	{
		return $this->_error;
	}

	/**
	 * Retourne l'historique des requêtes;
	 * @return array Liste des requêtes sql et leur temps d'exécution en seconde.
	 */
	public function get_history()
	{
		return $this->_history;
	}
	
	/**
	 * Retourne le nombre de requêtes effectuées.
	 * @return int
	 */
	public function get_count()
	{
		return count($this->_history);
	}
	
	/**
	 * Retourne le nombre totale de temps passé sur les requêtes.
	 * @return int Temps en ms.
	 */
	public function get_time()
	{
		return $this->_time;
	}
	
	/**
	 * Retourne la version de mysql.
	 * @return string
	 */
	public function get_version()
	{
		$this->_check_connection();
		reset($this->_data_bases);
		$base = current($this->_data_bases);
		return $base['connexion']->getAttribute(PDO::ATTR_SERVER_VERSION);
	}
	
	/**
	 * Retourne la version de mysql.
	 * @return string
	 */
	public function get_engine()
	{
		$this->_check_connection();
		$base = current($this->_data_bases);
		return $base['engine'];
	}
	
	/**
	 * Ajoute et connecte une base de données.
	 * @throws PDOException
	 * @param string $host Nom du domaine.
	 * @param string $name Nom de la base de données.
	 * @param string $user Nom de l'utilisateur.
	 * @param string $pass Mot de passe.
	 * @param string $charset Encodage de la base.
	 * @param string $engine Nom du système de base de données.
	 */
	public function add_base($host, $name, $user, $pass, $charset='utf-8', $engine='mysql')
	{
		$connexion = new PDO($engine.":host=".$host.";dbname=".$name,$user,$pass);
		$charset = strtolower($charset);
		$this->_data_bases[$name] = array (
				'connexion'=> $connexion,
				'tables' => NULL,
				'charset' => $charset,
				'host' => $host,
				'name' => $name,
				'user' => $user,
				'pass' => $pass,
				'engine' => $engine
		);
		if ($charset == 'utf-8')
		{
			$sql = "SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'";
			$this->query($sql, $name);
		}
		$this->_data_bases_nb++;
	}
	
	
	/**
	 * Retourne le nom de la base de données à utiliser pour une requete en fonction du nom de la table.
	 * @param string $table Nom de la table.
	 * @return string Nom de la base de données.
	 */
	public function select_base($table)
	{
		$this->_check_connection();
		if (is_string($table) == FALSE)
		{
			return NULL;
		}
		if ($this->_data_bases_nb == 1)
		{
			reset($this->_data_bases);
			return key($this->_data_bases);
		}
		$table = strtolower($table);
		$name = NULL;
		$continuer = TRUE;
		reset($this->_data_bases);
		while ((list($n,$v) = each($this->_data_bases)) && $name == NULL)
		{
			if ($this->_data_bases[$n]['tables'] == NULL)
			{
				$this->get_tables($n);
			}
			if (in_array($table,$this->_data_bases[$n]['tables']))
			{
				$name = $n;
			}
		}
		return $name;
	}
	
	/**
	 * Vérifie si la table passée en paramètre existe.
	 * @param string $name Nom de la table.
	 * @return boolean
	 */
	public function table_exists($name)
	{
		$this->_check_connection();
		return (is_string($name) && in_array($name,$this->get_tables()));
	}

	/**
	 * Vérifie si la base de données passée en paramètre existe.
	 * @param string $name Nom de la base de données.
	 * @return boolean
	 */
	public function base_exists($name)
	{
		return (is_string($name) && is_array($this->_data_bases) && array_key_exists($name,$this->_data_bases));
	}
		
	/**
	 * Active le cache des requêtes.
	 * @param string $dir Dossier qui contiendra les caches.
	 * @return boolean
	 */
	public function enable_cache($dir)
	{
	    if (is_string($dir))
	    {
	        if (file_exists($dir) == FALSE)
	        {
	            if (mkdir($dir, 0755, TRUE))
	            {
	                $this->_cache_dir = (substr($dir, -1) != '/') ? ($dir.'/') : ($dir);
	                return TRUE;
	            }
	        }
            elseif (is_dir($dir))
            {
                $this->_cache_dir = (substr($dir, -1) != '/') ? ($dir.'/') : ($dir);
                return TRUE;
            } 
	       
	    }
	    return FALSE;
	}
	
	/**
	 * Désactive le cache des requêtes.
	 */
	public function disable_cache()
	{
	    $this->_cache_dir = NULL;
	}
	
	/**
	 * Définie le temps de sauvegarde du cache.
	 * @param int $seconds Nombre de secondes.
	 * @return Base
	 */
	public function cache($seconds)
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
		foreach ($this->_data_bases as &$b)
		{
			$b['tables'] = NULL;
		}
		$this->_tables = array();
		if ($this->_cache_dir != NULL)
		{
		    $caches = array_diff(scandir($this->_cache_dir), array('..', '.'));
		    foreach ($caches as $c)
		    {
		        unlink($this->_cache_dir.$c);
		    }
		}
	}
	
	/**
	 * Tente de récupérer le cache de la requête.
	 * @param string $name_base Nom de la base de la requête.
	 * @param string $sql Requête à chercher dans le cache.
	 * @return array Renvoie le résultat ou NULL.
	 */
	private function _read_cache($name_base, $sql)
	{
	    if ($this->_cache_dir == NULL || $this->_cache_time <= 0)
	    {
	        return NULL;
	    }
	    $sql = trim($sql);
	    $type = strtolower(substr($sql, 0, strpos($sql, ' ')));
	    if (in_array($type, array('select', 'show', 'describe')) == FALSE)
	    {
	        return NULL;
	    }
	    $file = $this->_cache_dir.$name_base.'-'.md5($sql).'.sql.tmp';
	    if (file_exists($file) && filemtime($file) + $this->_cache_time >= time())
	    {
	    	return unserialize(file_get_contents($file));
	    }
	    return NULL;
	}
	
	/**
	 * Tente de mettre en cache la requête.
	 * @param string $name_base Nom de la base de la requête.
	 * @param string $sql Requête à chercher dans le cache.
	 * @param array $result Résultat de la requête.
	 * @return boolean
	 */
	private function _write_cache($name_base, $sql, $result)
	{
		if ($this->_cache_dir == NULL || $this->_cache_time <= 0)
		{
			return FALSE;
		}
		$type = strtolower(substr($sql, 0, strpos($sql, ' ')));
		if (in_array($type, array('select', 'show', 'describe')) == FALSE)
		{
			return FALSE;
		}
		$file = $this->_cache_dir.$name_base.'-'.md5($sql).'.sql.tmp';
		return (file_put_contents($file, serialize($result)));
	}
	
	/**
	 * Vérifie si la base est connectées à au moins une base de donnée.
	 */
	private function _check_connection()
	{
		if ($this->_data_bases == 0)
		{
			$d = debug_backtrace();
			throw new Error('No database found', $d[1]['file'], $d[1]['line']);
		}
	}
}
?>