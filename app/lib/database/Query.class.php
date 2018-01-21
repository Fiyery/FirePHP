<?php
/**
 * Query est la classe qui permet de gérer les requêtes SQL depuis les models.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 */
class Query
{	
	/**
	 * Type de requête possible.
	 * @var int
	 */
	const SELECT 		= 0;
	const DESCRIBE 		= 1;
	const SHOW 			= 2;
	const INSERT 		= 3;
	const UPDATE 		= 4;
	const DELETE 		= 5;
	const COUNT 		= 6;
	const TRUNCATE 		= 7;
	
	/**
	 * Instance de la base de données.
	 * @var Base
	 */
	private $_base = NULL;
	
	/**
	 * Type de requête SQL.
	 * @var int
	 */
	private $_type = 0;
	
	/**
	 * Code SQL de la requête.
	 * @var string
	 */
	private $_sql = NULL;
	
	/**
	 * Liste des valeurs pour la requête préparée.
	 * @var array
	 */
	private $_values = [];
	
	/**
	 * Nom des tables.
	 * @var array
	 */
	private $_tables = [];
	
	/**
	 * Nom de la classe des objets retournés.
	 * @var string
	 */
	private $_class = [];
	
	/**
	 * Constructeur.
	 * @param Database $base Instance de la base de données.
	 * @param string $class Nom de la classe de retour du resultat.
	 * @param string $table Nom de la table à requêter.
	 */
	public function __construct(Database $base = NULL, string $class = NULL, string $table = NULL)
	{
		$this->_base = $base;
		$this->_class = $class;
		$this->_tables[] = $table;
		$this->select();
	}
	
	/**
	 * Définie le nom de la table.
	 * @param string $name Nom de la table.
	 * @return Query
	 */
	public function table(string $name) : Query
	{
		$this->_tables = [$name];
		$this->select();
		return $this;
	}
	
	/**
	 * Définie le nom de la classe des objets de retour.
	 * @param string $name Nom de la classe.
	 * @return Query
	 */
	public function classe(string $name) : Query
	{
		$this->_class = $name;
		return $this;
	}
	
	/**
	 * Définie les champs à récupérer dans le SELECT.
	 * @param array $fields Tableau des champs à afficher.
	 * @return Query
	 */
	public function select(array $fields=[]) : Query
	{
		$this->_type = self::SELECT;
		$this->_sql = "SELECT ".((count($fields) > 0) ? (implode(", ", $fields)) : ("*"))." FROM `".$this->_tables[0]."`";
		return $this;
	}

	/**
	 * Vide la table via TRUNCATE et remet l'auto-incrémente à 0.
	 * @return Query
	 */
	public function clear() : Query
	{
		$this->_type = self::SELECT;
		$this->_sql = "TRUNCATE `".$this->_tables[0]."`; ALTER TABLE `".$this->_tables[0]."` AUTO_INCREMENT = 1;";
		return $this;
	}
	
	/**
	 * Définie un SELECT qui comptera les enregistrements retourné.
	 * @param array $fields Tableau des champs à afficher.
	 * @return Query
	 */
	public function count() : Query
	{
		$this->_type = self::COUNT;
		$this->_sql = "SELECT COUNT(*) nb FROM `".$this->_tables[0]."`";
		return $this;
	}
	
	/**
	 * Effectue une jointure simple entre deux tables.
	 * @param string $foreign_table Nom de la table de la requête.
	 * @param string $id_foreign Nom du champ à mettre en relation.
	 * @param string $id_table Nom du champ de la table à joindre à mettre en relation.
	 */
	public function join(string $foreign_table = NULL, string $id_foreign = NULL, string $id_table = NULL) : Query
	{
		return $this->_join("INNER", $foreign_table, $id_foreign, $id_table);
	}
	
	/**
	 * Effectue une jointure gauche entre deux tables.
	 * @param string $foreign_table Nom de la table de la requête.
	 * @param string $id_foreign Nom du champ à mettre en relation.
	 * @param string $id_table Nom du champ de la table à joindre à mettre en relation.
	 */
	public function join_left(string $foreign_table = NULL, string $id_foreign = NULL, string $id_table = NULL) : Query
	{
		return $this->_join("LEFT", $foreign_table, $id_foreign, $id_table);
	}
	
	/**
	 * Effectue une jointure droite entre deux tables.
	 * @param string $foreign_table Nom de la table de la requête.
	 * @param string $id_foreign Nom du champ à mettre en relation.
	 * @param string $id_table Nom du champ de la table à joindre à mettre en relation.
	 */
	public function join_right(string $foreign_table = NULL, string $id_foreign = NULL, string $id_table = NULL) : Query
	{
		return $this->_join("RIGHT", $foreign_table, $id_foreign, $id_table);
	}
	
	/**
	 * Effectue une jointure entre deux tables.
	 * @param string $type Type de jointure.
	 * @param string $id_table Nom du champ de la table à joindre à mettre en relation.
	 * @param string $foreign_table Nom de la table de la requête.
	 * @param string $id_foreign Nom du champ à mettre en relation.
	 */
	private function _join(string $type, string $foreign_table, ?string $id_foreign, ?string $id_table) : Query
	{
		$foreign_table = strtolower($foreign_table);
		$this->_tables[] = $foreign_table;
		if ($foreign_table === NULL)
		{
			return NULL;
				}
		if ($id_table === NULL || $id_foreign === NULL)
		{
			$foreign_fields = $this->_base->foreign_keys($this->_tables[0]);
			$find = FALSE;
			foreach ($foreign_fields as $f)
			{
				if (strtolower($f["REFERENCED_TABLE_NAME"]) === $foreign_table)
				{
					$find = TRUE;
					$id_table = $f["COLUMN_NAME"];
					$id_foreign = $f["REFERENCED_COLUMN_NAME"];
				}
			}
			if ($find === FALSE) 
			{
				$id_table = ($id_table != NULL) ? ($id_table) : ("id");
				$id_foreign = ($id_foreign != NULL) ? ($id_foreign) : ($this->_tables[0].".id_".$this->_tables[0]);
		}
		}
		$this->_sql .= " ".$type." JOIN `".$foreign_table."` ON `".$this->_tables[0]."`.`".$id_table."` = `".$foreign_table."`.`".$id_foreign."`";
		return $this;
	}
	
	/**
	 * Définie le type de requête à DESCRIBE.
	 * @return Query
	 */
	public function describe() : Query
	{
		$this->_type = self::DESCRIBE;
		$this->_sql = "DESCRIBE ".$this->_tables[0];
		return $this;
	}
	
	/**
	 * Définie le type de requête à DELETE.
	 * @return Query
	 */
	public function delete() : Query
	{
		$this->_type = self::DELETE;
		$this->_sql = "DELETE FROM ".$this->_tables[0];
		return $this;
	}
	
	/**
	 * Définie le type de requête à INSERT.
	 * @param array $fields Nom des champs pour l'insertion.
	 * @return Query
	 */
	public function insert(array $fields=[]) : Query
	{
		$this->_type = self::INSERT;
		$this->_sql = "INSERT INTO ".$this->_tables[0];
		if (count($fields) > 0)
		{
			$this->_sql .= " (`".implode("`, `", $fields)."`)";
		}
		$this->_sql .= " VALUES";
		return $this;
	}
	
	/**
	 * Ajoute une liste de valeurs à insérer.
	 * @param array $values Liste de valeur.
	 * @return Query
	 */
	public function values(array $values) : Query
	{
		if (count($this->_values) > 0)
		{
			$this->_sql .= ", ";
		}
		$this->_sql .= "(".implode(", ", array_fill(0, count($values), "?")).")";
		$this->_values = array_merge($this->_values, $values);
		return $this;
	}
	
	/**
	 * Ajoute la clause qui permet de mettre à jour certain champ en cas de doublon de clé primaire.
	 * @param array $fields Liste des noms des champs à mettre à jour.
	 * @return Query
	 */
	public function insert_update(array $fields) : Query
	{
		$this->_sql .= " ON DUPLICATE KEY UPDATE";
    	$i = count($fields);
		foreach ($fields as $f)
		{
			$this->_sql .= " `".$f."`= VALUES(`".$f."`)";
			if (--$i > 0)
			{
				$this->_sql .= ",";
			}
		}
		return $this;
	}
	
	/**
	 * Enregistre une requête SQL.
	 * @param string $sql Requête SQL.
	 * @param array $values Liste des valeurs pour la requête préparée.
	 * @return Query
	 */
	public function raw(string $sql, array $values=[]) : Query
	{
		$this->_sql = $sql;
		$this->_values = $values;
		return $this;
	}
	
	/**
	 * Ajoute une close WHERE AND.
	 * @param array|string $field Tableau associatif de champs valeur ou le nom du champ. 
	 * @param string $operator Opérateur de comparaison SQL.
	 * @param string $value Valeur du champ dans le cas ou $field est une string.
	 * @param string $logic Opérateur logique utilisé pour raccroché les conditions entre elles.
	 * @return Query
	 */
	public function where($field, string $operator = "=", string $value = NULL, string $logic = "AND") : Query
	{
		if (is_array($field))
		{
			$values = array_values($field);
			$fields = array_keys($field);
			if (isset($fields[0]))
			{
				if (strpos($this->_sql, " WHERE ") === FALSE)
				{
					$field = array_shift($fields);
					$value = array_shift($values);
					$this->_sql .= " WHERE `".$field."` ".$operator." ?";
					$this->_values[] = $value;
				}
				$count = count($fields);
				for($i=0; $i < $count; $i++)
				{
					$this->_sql .= " ".$logic." `".$fields[$i]."` ".$operator." ?";
					$this->_values[] = $values[$i];
				}
			}
		}
		else 
		{
			if (strpos($this->_sql, " WHERE ") === FALSE)
			{
				$this->_sql .= " WHERE `".$field."` ".$operator." ?";
				$this->_values[] = $value;
			}
			else 
			{
				$this->_sql .= " ".$logic." `".$field."` ".$operator." ?";
				$this->_values[] = $value;
			}
		}		
		return $this;
	}
	
	/**
	 * Ajoute une parenthèse ouvrante.
	 * @return Query
	 */
	public function open_parenthesis() : Query
	{
		$this->_sql .= "(";
		return $this;
	}
	
	/**
	 * Ajoute une parenthèse fermante.
	 * @return Query
	 */
	public function close_parenthesis() : Query
	{
		$this->_sql .= ")";
		return $this;
	}
	
	/**
	 * Ajout un paramètre à l'ORDER BY.
	 * @param array|string $field Tableau associatif champ, ordre de trie ou nom du champ à trier.
	 * @param string $order Type de trie parmi "asc" et "desc".
	 * @return Query
	 */
	public function order($field, string $order = "ASC") : Query
	{
		if (is_array($field))
		{
			$orders = array_values($field);
			$fields = array_keys($field);
			if (strpos($this->_sql, " ORDER BY ") === FALSE && isset($fields[0]))
			{
				$field = array_shift($fields);
				$order = array_shift($orders);
				$this->_sql .= " ORDER BY `".$field."` ".strtoupper($order);
				
			}
			$count = count($fields);
			for($i=0; $i < $count; $i++)
			{
				$this->_sql .= ", `".$fields[$i]."` ".strtoupper($orders[$i]);
			}
		}
		else 
		{
			$order = strtoupper($order);
			if (strpos($this->_sql, " ORDER BY ") === FALSE)
			{
				$this->_sql .= " ORDER BY `".$field."` ".$order;
			}
			else
			{
				$this->_sql .= ", `".$field."` ".$order;
			}
		}
		return $this;
	}
	
	/**
	 * Définie le nombre d'enregistrements à retourner.
	 * @param int $begin Nombre d'enregistrement à partir duquel on commence l'extraction.
	 * @param int $end Position du dernier enregistrement qui sera prit en compte.
	 * @return Query
	 */
	public function limit($begin, $end=NULL) : Query
	{
		if ($end === NULL)
		{
			$end = $begin - 1;
			$begin = 0;
		}
		$this->_sql .= " LIMIT ".($end-$begin+1)." OFFSET ".$begin;
		return $this;
	}
	
	/**
	 * Définie le nombre d'enregistrements au premier enregistrement retourné.
	 * @return Query
	 */
	public function first() : Query
	{
		$this->_sql .= " LIMIT 1";
		return $this;
	}
	
	/**
	 * Exécute la requête SQL et retourne le résultat.
	 * @return bool|array
	 */
	public function run() 
	{
		$result = $this->_base->query($this->_sql, array_values($this->_values));
		if ($this->_type === self::COUNT)
		{
			return (isset($result[0]["NB"])) ? (isset($result[0]["NB"])) : (0);
		}
		if ($this->_type !== self::SELECT)
		{
			return $result;
		}
		$objects = [];
		if (is_array($result))
		{
			foreach ($result as $r)
			{
				$objects[] = new $this->_class($r);
			}		
		}
		return $objects;
	}
	
	/**
	 * Retourne le status de retour de la dernière requête exécutée.
	 * @return array
	 */
	public function error() : array
	{
		return $this->_base->error();
	}
	
	/**
	 * Retourne la requête SQL.
	 * @return string
	 */
	public function sql() : Query
	{ 
		return $this->_sql;
	}
	
	/**
	 * Retourne les valeurs SQL des champs pour la requête préparée.
	 * @return array
	 */
	public function sql_values() : Query
	{
		return $this->_values;
	}
}
?>