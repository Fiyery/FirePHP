<?php
/**
 * FormSelect génère un select pour des formulaires.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses FormField
 */
class FormSelect extends FormField
{
    /**
     * Constructeur
     */
    public function __construct()
    {
    	parent::__construct('select', FALSE);
    }
    
    /**
     * Définie la valeur.
     * @param string $value Valeur du champ.
     * @return FormTextarea
     */
    public function value($value)
    {
    	if (is_scalar($value))
    	{
    		foreach ($this->_content as $op)
    		{
    		    if (is_object($op))
    		    {
    		        if ($op->value == $value)
    		        {
    		            $op->selected = 'selected';
    		            $this->_value = $value;
    		        }
    		        else 
    		        {
    		            unset($op->selected);
    		        }
    		    }
    		}
    	}
    	return $this;
    }
    
    /**
     * Définie si on active l'autofocus du champ ou non.
     * @param boolean $focus Si TRUE, on l'active.
     * @return FormSelect
     */
    public function autofocus($focus=TRUE)
    {
    	if (is_bool($focus) && $focus)
    	{
    		$this->autofocus = NULL;
    	}
    	return $this;
    }
    
    /**
     * Définie si on active les valeurs multiples du champ ou non.
     * @param boolean $multiple Si TRUE, on l'active.
     * @return FormSelect
     */
    public function multiple($multiple=TRUE)
    {
    	if (is_bool($multiple) && $multiple)
    	{
    		$this->multiple = NULL;
    	}
    	return $this;
    }
    
    /**
     * Ajoute une nouvelle option au select.
     * @param string $value Valeur de l'option.
     * @param string $label Valeur affichée dans le select.
     * @param boolean $selected Si TRUE, la valeur sera selectionnée.
     * @return FormSelect
     */
    public function add($value, $label=NULL, $selected=FALSE)
    {
        if (is_scalar($value))
        {
            if (is_null($label))
            {
                $label = $value;
            }
            $op = new HTMLTag('option');
            $op->content($label);
            $op->value = $value;
            if ($selected)
            {
                $op->selected = 'selected';
                $this->_value = $value;
            }
            $this->_content[] = $op;
        }
        elseif (is_array($value))
        {
        	foreach ($value as $v)
        	{
        		$op = new HTMLTag('option');
        		$op->content($v);
        		$op->value = $v;
        		$this->_content[] = $op;
        	}
        }
        return $this;
    }
}
?>