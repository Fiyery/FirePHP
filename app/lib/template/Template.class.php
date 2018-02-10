<?php
/**
 * Template est le moteur de template.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 */
class Template 
{
	/**
	 * Constant pour la syntaxe d'interpréation en php.
	 * @var int
	 */
	const PHP = 1;
	
	/**
	 * Constant pour la syntaxe d'interpréation en Smarty (perte de performance).
	 * @var int
	 */
	const SMARTY = 2;
	
	/**
	 * Constant pour la syntaxe d'interpréation en Smarty (plus performant que SMARTY).
	 * @var int
	 */
	const SMARTY_STRICT = 3;
	
	/**
	 * Syntaxe d'interprétation du moteur de template.
	 * @var int
	 */
	private $_syntaxe = self::SMARTY_STRICT;
	
	/**
	 * Dossier temporaire pour les templates.
	 * @var string
	 */
	private $_tmp_dir;
	
	/**
	 * Tableau contenant les valeurs à assigner aux templates.
	 * @var string[]
	 */
	private $_assigns;
	
	/**
	 * Liste des erreurs.
	 * @var array
	 */
	private $_list_errors;
	
	/**
	 * Si TRUE, les templates ne seront plus automatiquement supprimés.
	 * @var boolean
	 */
	private $_save_tpl = FALSE;

	/**
	 * Constructeur.
	 * @param string $tmp_dir Dossier temporaire.
	 */
	public function __construct(string $tmp_dir)
	{
		if (file_exists($tmp_dir) === FALSE)
		{
			mkdir($tmp_dir, 0755, TRUE);
		}
		$this->_tmp_dir = (substr($tmp_dir, -1) != '/') ? ($tmp_dir.'/') : ($tmp_dir);
		$this->_list_errors = array(
			'TPL_ERROR_SUCCES' => array('code'=>0,'message'=>'No error'),
			'TPL_ERROR_FILE_NOT_FOUND' => array('code'=>1,'message'=>'Template "$var" not find or not readable'),
			'TPL_ERROR_UNDEFINED_VARIABLE' => array('code'=>2,'message'=>'Undefined variable "$var" for Template'),
			'TPL_ERROR_ASSIGN' => array('code'=>3,'message'=>'Name of variable "$var" is wong : It must only content [a-zA-Z0-9]')
		);
		$this->_assigns = [];
	}	
	
	/**
	 * Définie la syntaxe d'interprétation du moteur de template.
	 * @param int $syntaxe Syntaxe d'interprétation
	 */
	public function set_syntaxe($syntaxe)
	{
		if (is_numeric($syntaxe))
		{
			switch ($syntaxe)
			{
				case self::SMARTY : $this->_syntaxe = self::SMARTY; break;
				case self::SMARTY_STRICT : $this->_syntaxe = self::SMARTY_STRICT; break;
				default : $this->_syntaxe = self::PHP; break;
			}
		}
	}
	/**
	 * Assigne une valeur à une variable du template.
	 * @param mixed $name Nom de la variable.
	 * @param mixed $value Valeur de la variable.
	 * @return boolean
	 */
	public function assign($name, $value = NULL)
	{
		if (is_array($name))
		{
			$error = FALSE;
			while ((list($n,$v) = each($name)))
			{
				if (is_numeric($n) == FALSE && preg_match('/^(\w+)$/',$n))
				{
					$this->_assigns[$n] = $v;
				}
				else 
				{
					$this->error('TPL_ERROR_ASSIGN',$n);
					$error = TRUE;
				}
			}
			if ($error)
			{
				return FALSE;
			}
		}
		else
		{
			if (is_numeric($name) == FALSE && preg_match('/^(\w+)$/',$name))
			{
				$this->_assigns[$name] = $value;
			}
			else
			{
				$this->error('TPL_ERROR_ASSIGN',$name);
				return FALSE;
			}
		}
		return TRUE;
	}

	/**
	 * Retourne la valeur de la variable.
	 * @param string $name Nom de la variable.
	 * @return mixed Valeur de la variable ou FALSE si elle n'est pas définie.
	 */
	public function get($name)
	{
		return ($this->_assigns[$name]) ?? (FALSE);
	}

	/**
	 * Affiche le template.
	 * @param string $template Chemin du template.
	 */
	public function display($template=NULL)
	{
		echo $this->fetch($template);
	}
	
	/**
	 * Vérifie l'existance du template et retourne le template interprété.
	 * @param string $template Chemin du template.
	 * @return string Template complété.
	 */
	public function fetch($template=NULL)
	{
		if (empty($template) || !file_exists($template) || !is_readable($template))
		{
			$this->error('TPL_ERROR_FILE_NOT_FOUND', $template);
			return NULL;
		}
		return $this->parse($template);
	}

	
	/**
	 * Retourne le template interprété.
	 * @param string $template Chemin du template.
	 * @return string Template complété.
	 */
	private function parse($template)
	{
		foreach ($this->_assigns as $name => $value)
		{
			$$name = $value;
		}
		ob_start();
		if ($this->_syntaxe == self::SMARTY || $this->_syntaxe == self::SMARTY_STRICT)
		{
		    $content = file_get_contents($template);
		    if ($this->_syntaxe == self::SMARTY)
		    {
		    	// Variable tableau
				do 
				{
					$content = preg_replace_callback('#\{([^\{]*)(\$\w+)\.([a-zA-Z\_\.]+)([^\}]*)\}#', function($match){
						return "{".$match[1].$match[2]."['".implode("']['", explode(".", $match[3]))."']".$match[4]."}";
					}, $content, -1, $count);
				} while ($count > 0);
		    		
		    	// Instruction if.
		    	$content = preg_replace("#\{\s*if([^\}]+)\}#", "<?php if($1):?>", $content);
		    	$content = preg_replace("#\{\s*else\s*\}#", "<?php else:?>", $content);
		    	$content = preg_replace("#\{\s*(\/if)\s*\}#", "<?php endif;?>", $content);
		    		
		    	// Instruction foreach.
		    	$content = preg_replace("#\{\s*foreach([^\}]+)\}#", "<?php foreach($1):?>", $content);
		    	$content = preg_replace("#\{\s*(\/foreach)\s*\}#", "<?php endforeach;?>", $content);
		    		
		    	// Commentaires Smarty.
		    	$content = preg_replace("#\{\s*\*#", "<?php /*", $content);
		    	$content = preg_replace("#\*\s*\}#", "*/ ?>", $content);
		    		
		    	// Balise PHP.
		    	$content = preg_replace('#([\{]\s*[\$])#', '<?php echo $', $content);
		    	$content = str_replace(array("}", "{"), array("?>", "<?php "), $content);
		    }
		    elseif ($this->_syntaxe == self::SMARTY_STRICT)
		    {
		    	// Variable tableau
				do 
				{
					$content = preg_replace_callback('#\{([^\{]*)(\$\w+)\.([a-zA-Z\_\.]+)([^\}]*)\}#', function($match){
						return "{".$match[1].$match[2]."['".implode("']['", explode(".", $match[3]))."']".$match[4]."}";
					}, $content, -1, $count);
				} while ($count > 0);
		    
		    	// Instruction if.
		    	$content = preg_replace("#\{if([^\}]+)\}#", "<?php if($1):?>", $content);
		    	$content = str_replace("{else}", "<?php else:?>", $content);
		    	$content = str_replace("{/if}", "<?php endif;?>", $content);
		    
		    	// Instruction foreach.
		    	$content = preg_replace("#\{\s*foreach([^\}]+)\}#", "<?php foreach($1):?>", $content);
		    	$content = str_replace("{/foreach}", "<?php endforeach;?>", $content);
		    
		    	// Commentaires Smarty.
		    	$content = str_replace("{*", "<?php /*", $content);
		    	$content = str_replace("*}", "*/ ?>", $content);
		    
		    	// Balise PHP.
		    	$content = str_replace('{$', '<?php echo $', $content);
		    	$content = str_replace(array("}", "{"), array("?>", "<?php "), $content);
		    }
		    $filename = $this->_tmp_dir.basename($template).'-'.md5(time()).rand(0,100).'.php';
		    file_put_contents($filename, $content);
		    require($filename);
		    if ($this->_save_tpl === FALSE)
		    {
		    	unlink($filename);
		    }
		}
		else 
		{
		    require($template);
		} 
		return ob_get_clean();
	}
	
	/**
	 * Génère une erreur.
	 * @param string $type Type d'erreur parmis les clés du tableau d'erreurs.
	 * @param string $var Valeur de remplacement de la variable dans le message d'erreur.
	 * @param string $level Niveau d'erreur.
	 */
	private function error($type, $var=NULL)
	{
		throw new FireException(str_replace('$var', $var, $this->_list_errors[$type]['message']), 2);
	}
	
	/**
	 * Sauvegarde les templates au lieu de les supprimer à la fin de leur utilisation.
	 */
	public function save_tpl()
	{
	    $this->_save_tpl = TRUE;
	}
}
?>