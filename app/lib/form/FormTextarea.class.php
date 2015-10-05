<?php
/**
 * FormTextarea génère les textarea pour des formulaires.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @copyright 2011-2015 Yoann Chaumin
 * @uses FormField
 */
class FormTextarea extends FormField
{
    /**
     * Constructeur
     */
    public function __construct()
    {
    	parent::__construct('textarea', FALSE);
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
    		$this->_content[0] = $value;
    		$this->_value = $value;
    	}
    	return $this;
    }
    
    /**
     * Définie le nombre de colonnes.
     * @param int $cols Nombre de colonnes.
     * @return FormTextarea
     */
    public function cols($cols)
    {
    	if (is_numeric($cols))
    	{
    		$this->cols = $cols;
    	}
    	return $this;
    }
    
    /**
     * Définie le nombre de lignes.
     * @param int $rows Nombre de lignes.
     * @return FormTextarea
     */
    public function rows($rows)
    {
    	if (is_numeric($rows))
    	{
    		$this->rows = $rows;
    	}
    	return $this;
    }
    
    /**
     * Définie le placeholder.
     * @param string $val Valeur du placeholder.
     * @return FormInput
     */
    public function placeholder($val)
    {
    	$this->placeholder = $val;
    	return $this;
    }
    
    /**
     * Définie si on active l'autofocus du champ ou non.
     * @param boolean $focus Si TRUE, on l'active.
     * @return FormInput
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
     * Définie le nombre de caractères maximal.
     * @param int $maxlength Nombre de caractères maximal.
     * @return FormInput
     */
    public function maxlength($maxlength)
    {
    	if (is_numeric($maxlength))
    	{
    		$this->maxlength = $maxlength;
    	}
    	return $this;
    }
    
    /**
     * Définie un masque.
     * @param string $val Regex.
     * @return FormInputText
     */
    public function pattern($val)
    {
    	$a = substr($val,0,1);
    	$b = substr($val,-1);
    	if ($a == $b && ($a == '/' || $a == '#'))
    	{
    		$val = substr($val,1,-1);
    	}
    	$val = str_replace(array('"','\'','&'),array('\u0022','\u0027','&amp;'),$val);
    	$this->pattern = $val;
    	return $this;
    }
}

?>