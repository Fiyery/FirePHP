<?php
/**
 * FormInput est la classe mère de tous les champs input d'un formulaire.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses FormField
 */
abstract class FormInput extends FormField
{
    /**
     * Constructeur.
     */
    public function __construct()
    {
    	parent::__construct('input',TRUE);
    }
    
    /**
     * Définie la valeur.
     * @param string $value Valeur du champ.
     * @return FormInput
     */
    public function value($value)
    {
    	if (is_scalar($value))
    	{
    		$this->value = $value;
    		$this->_value = $value;
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
     * Définie le titre.
     * @param string $val Valeur de l'attribut title.
     * @return FormInput
     */
    public function title($val)
    {
    	$this->title = $val;
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
     * Définie une liste pour le champ.
     * @param string $id Identifiant de la datalist.
     * @return FormInput
     */
    public function datalist($id)
    {
    	if (is_scalar($id))
    	{
    	    $id = (substr($id,0,1) == '#') ? (substr($id,1)) : ($id);
    		$this->list = $id;
    	}
    	return $this;
    }
    
    /**
     * Définie la taille du champ en nombre de caractères.
     * @param int $size Taille en nombre de caractères.
     * @return FormInput
     */
    public function size($size)
    {
    	if (is_numeric($size))
    	{
    		$this->size = $size;
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
}
?>