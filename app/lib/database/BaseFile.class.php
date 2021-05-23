<?php
namespace FirePHP\Database;

use FirePHP\Architecture\Singleton;
/**
 * Base est l'interface de connexion et de requetage à la base de données sous forme de fichiers.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 */
class BaseFile extends Singleton
{
	/**
	 * Types de variable.
	 * @var int
	 */
	const INT = 1;
	const NUMBER = 2;
	const FLOAT = 3;
	const BOOL = 4;
	const CHAR = 5;
	const STRING = 6;
	const DATE = 7;
	const TIMESTAMP = 8;
	
	/*
	 * Opérations de comparaison.
	 * @var string
	 */ 
	const COMP_EGAL = '=';
	const COMP_SUP = '>';
	const COMP_INF = '<';
	const COMP_DIFF = '<>';
	const COMP_IN = 'in';
	const COMP_NIN = 'not_in';

	/**
	 * Instance de singleton.
	 * @var BaseFile
	 */
	protected static $_instance = NULL;

	/**
	 * Dossier de sauvegarde de la base de données.
	 * @var string
	 */
	private static $_dir = NULL;
	
	/**
	 * Table courante sélectionnée.
	 * @var string
	 */
	private $_table = NULL;

	/**
	 * Liste des conditions de sélection.
	 * @var array
	 */
	private $_conditions = [];
	
	/**
	 * Liste des contenus des tables chargées et modifiées.
	 * @var array
	 */
	private $_contents = [];
	
	/**
	 * Constructeur.
	 */
	public function __construct()
	{
		
	}

	/**
	 * Destructeur.
	 * Sauvegarde les modifications des tables.
	 */
	public function __destruct()
	{
		foreach ($this->_contents as $name => $content)
		{
			file_put_contents($this->_get_file($name), json_encode($content));
		}
	}

	/**
	 * Crée une nouvelle table.
	 * @param string $name Nom de la table.
	 * @param array $columns Liste des noms des colonnes avec en valeur les types.
	 * @return bool
	 */
	public function create(string $name, array $columns) : bool
	{
		if (file_exists($this->_get_file($name)))
		{
			return FALSE;
		}
		$data = [
				'name' => $name,
				'fields' => $columns,
				'rows' => []
		];
		return file_put_contents($this->_get_file($name), json_encode($data));
	}
	
	/**
	 * Supprime un table.
	 * @param string $name Nom de la table.
	 * @return bool
	 */
	public function drop(string $name) : bool
	{
		$filename = $this->_get_file($name);
		if (file_exists($filename) == FALSE)
		{
			return FALSE;
		}
		return unlink($filename);
	}
	
	/**
	 * Sélectionne une table pour permettre la récupération, insertion, modification ou suppression des enregistrements.
	 * @param string $name Nom de la table.
	 * @return BaseFile
	 */
	public function select(string $name) : BaseFile
	{
		if (is_string($name) && empty($name) == FALSE)
		{
			if (file_exists($this->_get_file($name)))
			{
				if (array_key_exists($name, $this->_contents) == FALSE)
				{
					$content = json_decode(file_get_contents($this->_get_file($name)));
					if ($content == FALSE)
					{
						$this->_table = NULL;
					}
					else 
					{
						$this->_table = $name;
						$this->_contents[$name] = $content;
					}
				}
				else 
				{
					$this->_table = $name;
				}
			}
		}
		return $this;
	}
	
	/**
	 * Ajoute un nouvel enregistrement dans la table selectionnée.
	 * @param array $columns Liste des valeurs de l'enregistrement.
	 * @return bool
	 */
	public function insert(array $columns) : bool
	{
		if (empty($this->_table))
		{
			return FALSE;
		}
		if (count($columns) != count(get_object_vars($this->_contents[$this->_table]->fields)))
		{
			return FALSE;
		}
		$this->_contents[$this->_table]->rows[] = array_values($columns);
		return TRUE;
	}

	/**
	 * Ajoute une condition de sélection des enregistrements.
	 * @param string $name Nom du champ.
	 * @param string $value Valeur de comparaison.
	 * @param string $opp Opérateur de comparaison.
	 * @return BaseFile
	 */
	public function cond(string $name, string $value, string $opp) : BaseFile
	{
		$this->_conditions[] = [
			'name' => $name,
			'value' => $value,
			'opp' => $opp
		];
		return $this;
	}

	/**
	 * Recherche les enregistrements correspondants aux conditions. 
	 * @return array Liste des enregistrements trouvés.
	 */
	public function search() : array
	{
		if (empty($this->_table))
		{
			return [];
		}
		if (count($this->_contents[$this->_table]->rows) == 0)
		{
			return [];
		}
		$results = $this->_find($this->_parse($this->_contents[$this->_table]->rows));
		$this->_table = NULL;
		return $results;
	}

	/**
	 * Modifie des enregistrements sélectionnés.
	 * @param array $values Liste des noms de champs en clé et la nouvelle valeur en valeur de clé.
	 * @return boolean
	 */
	public function update($values) 
	{
		if (empty($this->_table))
		{
			return FALSE;
		}
		$find = TRUE;
		$fields = $this->fields();
		$field_numbers = []; 
		foreach ($values as $n => $v)
		{
			$key = array_search($n, $fields);
			if ($key === FALSE)
			{
				$find = FALSE;
			}
			else 
			{
				$field_numbers[] = $key;
			}
		}
		if ($find == FALSE)
		{
			return FALSE;
		}
		$name = $this->_table;
		$keys = array_keys($this->search());
		$values = array_combine($field_numbers, $values);
		foreach ($keys as $k)
		{
			foreach ($values as $n => $v)
			{
				$this->_contents[$name]->rows[$k][$n] = $v;
			}
		}	
		return TRUE;	
	}
	
	/**
	 * Supprime les enregistrements sélectionnés.
	 * @return boolean
	 */
	public function delete()
	{
		if (empty($this->_table))
		{
			return FALSE;
		}
		$name = $this->_table;
		$keys = array_keys($this->search());
		foreach ($keys as $k)
		{
			if (array_key_exists($k, $this->_contents[$name]->rows))
			{
				unset($this->_contents[$name]->rows[$k]);
			}
			
		}
		return TRUE;
	}
	
	/**
	 * Retourne les champs de la table sélectionnée.
	 * @return array Liste des noms des champs.
	 */
	public function fields()
	{
		if (empty($this->_table))
		{
			return [];
		}
		return array_keys(get_object_vars($this->_contents[$this->_table]->fields));
	}
	
	/**
	 * Définie le dossier de sauvegarde de la base de données.
	 * @param string $dir Chemin du dossier
	 */
	public static function set_dir($dir)
	{
		if (file_exists($dir) == FALSE)
		{
			mkdir($dir, 0755, TRUE);
		}
		self::$_dir = (substr($dir, - 1) != '/') ? ($dir . '/') : ($dir);
	}

	/**
	 * Récupère le fichier correspondant à la table.
	 * @param string $name Nom obtionnel de la table.
	 * @return string Chemin du fichier.
	 */
	private function _get_file($name=NULL)
	{
		if (empty($name))
		{
			if (empty($this->_table) == FALSE)
			{
				$name = $this->_table;
			}
			else
			{
				return NULL;
			}
		}
		return self::$_dir.$name.'.json';
	}
	
	/**
	 * Retourne les enregistrements en fonction des conditions.
	 * @param array $rows Liste des enregistrements à tester.
	 * @return array Liste des enregistrements répondant aux conditions.
	 */
	private function _find($rows)
	{
		if (isset($this->_conditions[0]) == FALSE)
		{
			$results = $rows;
		}
		else 
		{
			$results = array_filter($rows, [$this, '_check']);
		}
		$this->_conditions = [];
		return $results;
	}
	
	/**
	 * Définie si un enregistrement répond ou non à l'ensemble des conditions.
	 * @param array $e Enregistrement à tester.
	 * @return boolean
	 */
	private function _check($e)
	{
		$valid = TRUE;
		$i = 0;
		while($valid && isset($this->_conditions[$i]))
		{
			$cond = $this->_conditions[$i];
			if (isset($e[$cond['name']]) == FALSE)
			{
				$valid = FALSE;
			}
			else
			{
				$value = $e[$cond['name']];
				$compare = $cond['value'];
				switch($cond['opp'])
				{
					case self::COMP_EGAL:
						$valid = ($value == $compare);
						break;
					case self::COMP_EGAL | self::COMP_INF:
						$valid = ($value <= $compare);
						break;
					case self::COMP_EGAL | self::COMP_SUP:
						$valid = ($value >= $compare);
						break;
					case self::COMP_INF:
						$valid = ($value < $compare);
						break;
					case self::COMP_SUP:
						$valid = ($value > $compare);
						break;
					case self::COMP_DIFF:
						$valid = ($value != $compare);
						break;
					case self::COMP_IN :
						$valid = ((is_array($compare) && in_array($value, $compare)) || $value == $compare);
						break;
					case self::COMP_NIN :
						$valid = ((is_array($compare) && !in_array($value, $compare)) || (is_string($compare) && $value != $compare));
						break;
					default:
						$valid = FALSE;
				}
			}
			$i ++;
		}
		return $valid;
	}

	/**
	 * Ajoute le nom des colonnes en clé de résultat.
	 * @param array $rows Liste des enregistrements à modifier.
	 * @return array Liste des enregistrements avec les noms des colonnes en clé.
	 */
	private function _parse($rows)
	{
		$objects = [];
		$fields = $this->fields();
		$count = count($fields);	
		foreach ($rows as $r)
		{
			$o = [];
			for($i=0; $i < $count; $i++)
			{
				$o[$fields[$i]] = $r[$i];
			}
			$objects[] = $o;
		}
		return $objects;
	}
}
?>