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
	public function __construct(string $filename)
	{
		$content = file_get_contents($filename);
		$content = preg_replace('#\/\/[^"\n]*$#m', '', $content);
		$this->_values = new ConfigValue('config', json_decode($content));
		$this->_parse($this->_values);
	}
	
	/**
	 * Remplace les variables du fichier de configuration par leur valeur.
	 * @param stdClass $var Objet des paramètres.
	 */
	private function _parse($values)
	{
		$keys = $values->keys();
		foreach ($keys as $key)
		{
			if (is_object($values->$key))
			{
				$this->_parse($values->$key);
			} 
			else
			{
				if (preg_match('#{\$([\w\.]*)}#', $values->$key, $m))
				{
					$name = $m[1];
					$m = explode('.', $name);
					$i = 0;
					$max = count($m);
					$val = $this->_values;
					while(isset($m[$i]) && ($attr = $m[$i]) && isset($val->$attr))
					{
						$val = $val->$attr;
						$i++;
					}
					if ($i == $max && is_scalar($val))
					{
						$values->$key = str_replace('{$'.$name.'}', $val, $values->$key);
					}
					else
					{
						trigger_error('Undefined var "'.$name.'" in the configuration file');
					}
				}
			}
		}
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
		return (isset($this->_values, $name));
	}
	
	/**
	 * Supprime un paramètre.
	 * @param string $name Nom du paramètre
	 */
	public function __unset(string $name)
	{
		if (isset($this->$name))
		{
			unset($this->_values[$name]);
		}
	}
}
?>