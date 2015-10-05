<?php
class Query
{
	private $sql = NULL;
	private $type = NULL;
	private $tables = array();
	private static $bases = NULL;
	
	
	// Constructeur.
	public function __construct($sql)
	{
		if (self::$bases == NULL)
		{
			$base = Base::get_instance();
			$bases_names = $base->get_bases();
			$links = array();
			foreach ($bases_names as $b)
			{
				$links[$b] = $base->get_tables($b);
			}
			self::$bases = $links;
		}
		$this->sql = $sql;
		$this->analyse();
	}
	
	// Fonction qui retourne la requete.
	public function get_sql()
	{
		return $this->sql;
	}
	
	// Fonction qui retourne la base cible de la requete.
	public function get_base()
	{
		$base = NULL;
		if (isset($this->tables[0]))
		{
			foreach (self::$bases as $base_name => $tables)
			{
				$find = FALSE;
				if (is_array($tables))
				{
					foreach ($tables as $t)
					{
						if ($this->tables[0] == $t)
						{
							$find = TRUE;
						}
					}
				}
				if ($find)
				{
					$base = $base_name;
				}
			}
		}
		return $base;
	}
	
	// Fonction qui analyse la requete.
	private function analyse()
	{
		preg_match('/\s*([a-zA-Z]*)/',$this->sql,$match);
		if (isset($match[1]))
		{
			$this->type = strtolower($match[1]);
			if ($this->type == 'select') 
			{
				preg_match_all('/from(.*)(where|groupe\sby|limit|order\sby|$)/isU',$this->sql,$match);
				$tables = array();
				foreach ($match[1] as $m)
				{
					$m = preg_replace('/select(.*)from/isU','',$m);
					$tables = array_merge($tables,explode(',',$m));
				}
				$this->tables = $tables;
			}
			elseif ($this->type == 'insert')
			{
				$pos = stripos($this->sql,'into');
				$tables = substr($this->sql,$pos+4);
				$pos = strpos($tables,'(');
				$tables = array(substr($tables,0,$pos));
				preg_match_all('/from(.*)(where|groupe\sby|limit|order\sby|$)/isU',$this->sql,$match);
				if (isset($match[1]))
				{
					foreach ($match[1] as $m)
					{
						$tables = array_merge($tables,explode(',',$m));
					}
				}
				$this->tables = $tables;
			}
			elseif ($this->type == 'update')
			{
				preg_match('/update(.*)set/is',$this->sql,$match);
				$tables = str_ireplace(array('ignore','low_priority'),'',$match[1]);
				$this->tables = array($tables);
			}
			elseif ($this->type == 'delete')
			{
				preg_match('/from(.*)(where|$)/isU',$this->sql,$match);
				$this->tables = array($match[1]);
			}
			elseif ($this->type = 'truncate')
			{
				$tables = preg_replace('/truncate\s+table/is','',$this->sql);
				$this->tables = array($tables);
			}
			$this->parse_table();
		}
	}
	
	// Fonction qui traite les noms des tables récupérés dans la requête.
	private function parse_table()
	{
		$new_tables = array();
		foreach ($this->tables as $t)
		{
			$t = trim($t);
			if (preg_match('/[^\s]+\s[^\s]+/',$t))
			{
				$t = substr($t,0,strpos($t,' '));
			}
			else
			{
				$t = preg_replace('/\s/','',$t);
			}
			$new_tables[] = str_replace(array("'",'"','`',';','(',')'),'',$t);
		}
		$this->tables = $new_tables;
	}
	
	// Fonction magique qui retourne la requete sql.
	public function __toString()
	{
		return $this->sql;
	}
	
	// Fonction qui lie une table à une base.
	public static function set_table_links($links)
	{
		self::$bases = $links;
	}
}
?>