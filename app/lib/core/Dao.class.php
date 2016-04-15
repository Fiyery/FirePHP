<?php
/**
 * Dao est la classe générale et mère de toutes les Dao spécifiques d'accès au table de la base de données.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @copyright 2011-2015 Yoann Chaumin
 * @uses Base
 * @uses Error
 */
abstract class Dao
{
    /**
     * Classe de la base de données.
     * @var Base
     */
    private static $_base = NULL;
    
    /**
     * Constructeur
     * @param array $data Liste des couples nom-valeur des attributs de l'instance.
     */
    public function __construct($data=array())
    {
    	foreach($data as $name => $value)
    	{
    		$this->$name = $value;
    	}
    }
    
    /**
     * Retourne un attribut.
     * @param mixed $name Valeur de l'attribut.
     * @throws Error
     */
    public function __get($name)
    {
        $caller = get_called_class();
    	if (property_exists($caller, $name) == FALSE)
    	{
    	    $d = debug_backtrace();
    	    throw new Error('Invalid property "'.$name.'" for class '.$caller, $d[0]['file'],  $d[0]['line']);
    	}
        return $this->$name;
    }
    
    /**
     * Définie un attribut.
     * @param string $name Nom de l'attribut.
     * @param mixed $valeur Valeur de l'attribut.
     */
    public function __set($name, $value)
    {
    	$set = 'set_'.$name;
    	if (method_exists($this, $set))
    	{
    		return $this->$set($value);
    	}
    	else 
    	{
			$this->$name = $value;
			return TRUE;
    	}
    }
    
    /**
     * Persiste l'objet courant en base de données.
     * @return bool
     */
    public function save()
    {
    	$table = $this->_to_table_name();
    	$fields = $this->fields();
    	$sql = "INSERT INTO `".$table."` VALUES (".implode(',', array_fill(0, count($fields), '?')).") ON DUPLICATE KEY UPDATE";
    	$value_sql = array();
    	$last = end($fields);
    	foreach ($fields as $f)
    	{
    		$sql .= " `".$f."`= VALUES(`".$f."`)";
    		if ($f != $last)
    		{
    			$sql .= ',';
    		}
    		$value_sql[] = (isset($this->$f)) ? ($this->$f) : (NULL);
    	}
    	$result = self::$_base->query($sql, $value_sql);
    	$this->id = self::$_base->last_id();
    	return ($result !== FALSE);
    }
    
    /**
     * Supprime l'instance courante de la base de données.
     * @return bool
     */
    public function remove()
    {
    	$keys = self::keys();
    	$last = end($keys);
    	$where = [];
    	foreach ($keys as $k)
    	{
    		$where[$k] = $this->$k;
    	}
    	return self::query()->delete()->where($where)->run();
    }
    
    /**
     * Retourne un tableau contenant les éventuelles dépendances d'un enregistrement.
     * @return array Liste des tables et des enregistrements dépendants.
     */
    public function dependances()
    {
    	$constraintes = self::$_base->foreign_key(self::_to_table_name());
    	$tables = array();
    	$done = array();
    	foreach ($constraintes as $c)
    	{
    		$table = $c['TABLE_NAME'];
    		if (array_key_exists($table, $done) == FALSE)
    		{
    			$foreign_field = $c['COLUMN_NAME'];
    			$field = $c['REFERENCED_COLUMN_NAME'];
    			$rows = self::$_base->query("
				 SELECT *
						FROM ".$table."
						WHERE
						".$foreign_field." = '".($this->$field)."';
						");
    			if (is_array($rows))
    			{
    				$tables[$table] = $rows;
    			}
    			$done[$table] = TRUE;
    		}
    	}
    	return $tables;
    }
    
    /**
     * Définie la connexion avec la base de données.
     * @param Base $b Instance de connexion et de requêtage à la base de données.
     */
    public static function set_base(Base $b)
    {
    	self::$_base = $b;
    }
    
    /**
     * Retourne une instance de Query qui permet d'effectuer des requêtes spécifiques.
     * @return Query
     */
    public static function query()
    {
    	return (new Query(self::$_base, get_called_class(), self::_to_table_name()));
    }
    
    /**
     * Renvoie la liste des champs de la table.
     * @return array Liste des champs.
     */
    public static function fields()
    {
    	$fields_details = self::$_base->fields(self::_to_table_name());
    	$names = [];
    	if (is_array($fields_details))
    	{
	    	foreach ($fields_details as $f)
	    	{
	    		$names[] = $f['Field'];
	    	}
    	}
    	return $names;
    }
    
    /**
     * Renvoie la liste des clés primaires de la table.
     * @return array Liste des champss.
     */
    public static function keys()
    {
    	$fields_details = self::$_base->fields(self::_to_table_name());
    	$names = [];
    	if (is_array($fields_details))
    	{
    		foreach ($fields_details as $f)
    		{
    			if ($f['Key'] === 'PRI')
    			{
    				$names[] = $f['Field'];
    			}
    		}
    	}
    	return $names;
    }
    
    /**
     * Charge une intance à partir d'un enregistrement de la base de données.
     * @param array|string $values Identifiant ou liste d'identifiants. Prends un nombre quelconque de paramètres pour chaque clé primaire.
     * @return object Instance de l'enregistrement.
     */
    public static function load($values)
    {
    	// Génération des bons paramètres.
    	$values = func_get_args();
    	
    	// Génération des informations des champs.
    	$keys = self::keys();
    	if (count($keys) !== count($values))
    	{
    		return NULL;
    	}
    	// Exécution de la requête.    	
    	$result = self::query()->where(array_combine($keys, $values))->run();
    	if (count($result) === 1)
    	{
    		return $result[0];
    	}
    	return NULL;
    }
    
    /**
     * Compte le nombre d'enregistrements.
     * @param array $fields Tableau associatif d'égalité entre champ valeur.
     * @return int Nombre d'enregistrements.
     */
    public static function count(Array $fields=[])
    {
    	return self::query()->count()->where($fields)->run();
    }
    
    /**
     * Transforme le nom de la classe en celui de la table.
     * @return string
     */
    protected static function _to_table_name()
    {
    	return strtolower(get_called_class());
    }
    
}
?>