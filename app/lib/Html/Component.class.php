<?php
namespace FirePHP\Html;

use ReflectionClass;

/**
 * Component permet la segmentation de code HTML et leur réusilisation.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses Template
 */
class Component
{
	/**
	 * Template
	 * @var Template
	 */
    private $_tpl = NULL;

	/**
	 * Constructeur
	 * @param string $tmp_dir
	 */
    public function __construct($tmp_dir='.')
    {
        $this->_tpl = new Template($tmp_dir);
    }

	/**
	 * Définie la valeur d'une variable
	 * @param string $name
	 * @param mixed $value
	 */
    public function assign(string $name, $value)
    {
        $this->_tpl->assign($name, $value);
    }

	/**
	 * Retourne le contenu HTML
	 * @param string $template
	 * @return string
	 */
    public function fetch(string $template = NULL) : string
    {
        if ($template === NULL)
        {
            $class = new ReflectionClass($this);
            $template = glob(dirname($class->getFileName()).'/*.tpl');
            if (count($template) === 0)
            {
                return '';
            }
            $template = $template[0];
        }
        return $this->_tpl->fetch($template);
    }
}
?>