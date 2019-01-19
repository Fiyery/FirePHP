<?php
/**
 * Config est la classe qui contient l'ensemble des paramètres du site.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses ConfigValue
 */
class Config 
{
	/**
	 * Object contenant l'ensemble des paramètres du site.
	 * @var ConfigValue
	 */
	private $_values;
	
	/**
	 * Constructeur.
	 * @param string $filename Chemin du fichier JSON de config.
	 */ 
	public function __construct(string ...$filenames)
	{
		$values = new stdClass();
		foreach ($filenames as $filename)
		{
			if (file_exists($filename))
			{
				$content = file_get_contents($filename);
				if (trim($content) !== "")
				{
					$content = preg_replace("#\/\/[^\"\n]*$#m", "", $content);
					$values = $this->_merge_object($values, json_decode($content));
				}
			}
		}		
		$this->_values = new ConfigValue("config", $values);
		$this->_parse($this->_values);
	}

	/**
	 * Permet la surcharge des configurations.
	 * @param stdClass $object1 Configuration mère.
	 * @param stdClass $object2 Configuration fille.
	 */ 
	private function _merge_object(stdClass $object1, stdClass $object2)	
	{
		foreach ($object2 as $name => $value)
		{
			if (isset($object1->$name) && is_object($object1->$name) && is_object($object2->$name))
			{
				$object1->$name = $this->_merge_object($object1->$name, $object2->$name);
			}
			else
			{
				$object1->$name = $value;
			}
		}
		return $object1;
	}
	
	/**
	 * Remplace les variables du fichier de configuration par leur valeur.
	 * @param stdClass $data Objet des paramètres.
	 */
	private function _parse($data)
	{
		foreach ($data->iterate() as &$d)
		{
			if (is_object($d))
			{
				$this->_parse($d);
			} 
			elseif (is_array($d))
			{
				foreach ($d as $n => $v)
				{
					if (preg_match("#{\\$([\w\.]*)}#", $v, $m))
					{
						$d[$n] = str_replace("{\$".$m[1]."}", $this->resolve(explode('.', $m[1])), $v);
					}
				}
			}
			else 
			{
				if (preg_match("#{\\$([\w\.]*)}#", $d, $m))
				{
					$d = str_replace("{\$".$m[1]."}", $this->resolve(explode('.', $m[1])), $d);
				}
			}
		}
	}

	public function resolve(array $args) 
	{
		$i = 0;
		$max = count($args);
		$value = $this->_values;
		while(isset($args[$i]) && ($attr = $args[$i]) && isset($value->$attr))
		{
			$value = $value->$attr;
			$i++;
		}
		if ($i !== $max) 
		{
			throw FireException("Undefined var \"$".implode(".", $args)."\" in the configuration file");
		}
		return $value;
	}
	
	/**
	 * Retourne la valeur d'un paramètre.
	 * @param string $name Nom du paramètre.
	 * @return mixed Valeur du paramètre ou FALSE.
	 */
	public function __get($name)
	{
		return $this->_values->$name;
	}
	
	/**
	 * Définie la valeur d'un paramètre.
	 * @param string $name Nom du paramètre.
	 * @param string $value Valeur du paramètre.
	 */
	public function __set(string $name, $value)
	{
		$this->_values->$name = $value;
	}
	
	/**
	 * Vérifie l'existance d'un paramètre.
	 * @param string $name Nom du paramètre
	 * @return boolean
	 */
	public function __isset(string $name) : bool
	{
		return (isset($this->_values->$name));
	}
	
	/**
	 * Supprime un paramètre.
	 * @param string $name Nom du paramètre
	 */
	public function __unset(string $name)
	{
		if (isset($this->$name))
		{
			unset($this->_values->$name);
		}
	}
}
?>