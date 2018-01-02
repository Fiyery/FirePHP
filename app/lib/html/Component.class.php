<?php
/**
 * Component permet la segmentation de code HTML et leur réusilisation.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses Template
 */
class Component
{
    private $_tpl = NULL;

    public function __construct($tmp_dir='.')
    {
        $this->_tpl = new Template($tmp_dir);
    }

    public function assign(string $name, $value)
    {
        $this->_tpl->assign($name, $value);
    }

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