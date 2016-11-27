<?php
/**
 * Config est la classe qui contient l'ensemmble des paramètres du site.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @use Singleton
 */
class Config extends Singleton
{
	/**
	 * Instance de singleton de Config.
	 * @var Config
	 */
	protected static $_instance = NULL;
	
	/**
	 * Object contenant l'ensemble des paramètres du site.
	 * @var stdClass
	 */
	private $_params;
	
	/**
	 * Constructeur.
	 */ 
	protected function __construct($file)
	{
		$content = file_get_contents($file);
		$content = preg_replace('#\/\/[^"\n]*$#m', '', $content);
		$this->_params = json_decode($content);
		if (is_object($this->_params))
		{
			$this->_parse($this->_params);
		}
		$this->_parse_root();
	}
	
	/**
	 * Remplace les variables du fichier de configuration par leur valeur.
	 * @param stdClass $var Objet des paramètres.
	 */
	private function _parse(&$var)
	{
		foreach ($var as &$v)
		{
			if (is_string($v) && empty($v) === FALSE)
			{
				if (preg_match('#{\$([\w\.]*)}#', $v, $m))
				{
					$name = $m[1];
					$m = explode('.', $name);
					$i = 0;
					$max = count($m);
					$value = $this->_params;
					while($i < $max && property_exists($value, $m[$i]))
					{
						$attr = $m[$i];
						$value = $value->$attr;
						$i++;
					}
					if ($i == $max && is_scalar($value))
					{
						$v = str_replace('{$'.$name.'}', $value, $v);
					}
					else
					{
						trigger_error('Propriété non définie "'.$name.'" dans le fichier de configuration');
					}
				}
			}
			elseif (is_object($v) || is_array($v))
			{
				$this->_parse($v);
			}
		}
	}
	
	/**   
	 * Définie le chemin absolute de l'application en répertoire et url. 
	 */
	private function _parse_root()
	{
	    $this->_params->path->root_dir = str_replace('\\', '/', realpath(__DIR__.'/../../../')).'/';
	    $root = (substr($_SERVER['DOCUMENT_ROOT'], -1) == '/') ? (substr($_SERVER['DOCUMENT_ROOT'], 0, -1)) : ($_SERVER['DOCUMENT_ROOT']);
	    $root = str_replace($root, 'http://' . $_SERVER['SERVER_NAME'], $this->_params->path->root_dir);
	    $this->_params->path->root_url = (substr($root, - 1) != '/') ? ($root . '/') : ($root);
	}
	
	/**
	 * Retourne la valeur d'un paramètre.
	 * @param string $name Nom du paramètre.
	 * @return mixed Valeur du paramètre ou FALSE.
	 */
	public function __get($name)
	{
		if (property_exists($this->_params, $name))
		{
			return $this->_params->$name;
		}
		else
		{
			return NULL;
		}
	}
	
	/**
	 * Définie la valeur d'un paramètre.
	 * @param string $name Nom du paramètre.
	 * @param string $value Valeur du paramètre.
	 */
	public function __set($name, $value)
	{
		$this->_params->$name = $value;
	}
	
	/**
	 * Vérifie l'existance d'un paramètre.
	 * @param string $name Nom du paramètre
	 * @return boolean
	 */
	public function __isset($name)
	{
		return (property_exists($this->_params, $name));
	}
	
	/**
	 * Supprime un paramètre.
	 * @param string $name Nom du paramètre
	 */
	public function __unset($name)
	{
		if (isset($this->$name))
		{
			unset($this->_params[$name]);
		}
	}
	
	/**
	 * Retourne une instance de la classe avec les arguments correctement ordonnés selon le constructeur de la classe.
	 * @param array $args Tableau d'arguments du constructeur.
	 * @return Browser
	 */
	protected static function __create($args)
	{
		return new self($args[0]);
	}
}
?>