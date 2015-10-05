<?php
/**
 * Form génère des formulaires HTML.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @copyright 2011-2015 Yoann Chaumin
 * @uses HTMLTag
 */
class Form extends HTMLTag
{
    /**
     * Liste des constantes disponibles pour l'ajout de champ.
     * @var string
     */
    const BUTTON = 'InputButton';
    const CHECKBOX = 'InputCheckbox';
    const COLOR = 'InputColor';
    const DATALIST = 'Datalist';
    const DATE = 'InputDate';
    const DATETIME = 'InputDateTime';
    const EMAIL = 'InputEmail';
    const FIELDSET = 'Fieldset';
    const FILE = 'InputFile';
    const HIDDEN = 'InputHidden';
    const IMAGE = 'InputImage';
    const MONTH = 'InputMonth';
    const NUMBER = 'InputNumber';
    const PASSWORD = 'InputPassword';
    const RADIO = 'InputRadio';
    const RANGE = 'InputRange';
    const RESET = 'InputReset';
    const SELECT = 'Select';
    const SUBMIT = 'InputSubmit';
    const TEL = 'InputTel';
    const TEXT = 'InputText';
    const TEXTAREA = 'Textarea';
    const TIME = 'InputTime';
    const URL = 'InputUrl';
    const WEEK = 'InputWeek';
    
    /**
     * Classe par défaut des formulaires.
     * @var string
     */
    private static $_default_class = NULL;
    
    /**
     * Liste des erreurs.
     * @var array<string>
     */
	private $_errors;
	
	/**
	 * Numéro de la case du fieldset. Si supérieur à 0, alors les nouveaux champs seront dans le fieldset
	 * @var int
	 */
	private $_fieldset = -1;
	
	/** 
	 * Liste des attributs 'name' des champs
	 * @var array<string>
	 */
	private $_names = array();
	
	/**
	 * Si TRUE, tous les champs du formulaire seront requis.
	 * @var boolean
	 */
	private $_required = FALSE;
	
	/**
	 * Champ qui n'a pas passé la validation.
	 * @var FormField
	 */
	private $_error_field = NULL;
	
	/**
	 * Constructeur.
	 * @param string $action Adresse de traitement du formulaire.
	 * @param string $id Identifiant du formulaire.
	 * @param string $method Méthode d'envoie.
	 */
	public function __construct($action, $id, $method=NULL)
	{
		parent::__construct('form');
		$this->_attrs['action'] = (is_string($action)) ? ($action) : (NULL);
		$this->_attrs['id'] = (is_string($id)) ? ($id) : (NULL);
		$this->_attrs['method'] = (is_string($method) && strtoupper($method) == 'GET') ? ('GET') : ('POST');
		$this->_attrs['class'] = self::$_default_class;
		$this->_attrs['enctype'] = NULL;
		$this->_errors = array(
			'FIELD_NOT_FOUND' => 'Form field " $var " not found',
			'NAME_INVALID' => 'Invalide name for Form field $var "',
			'NAME_EXISTS' => 'Field name " $var " already exists'
		);
	}

	/**
	 * Définie ou retourne la méthode d'envoie des informations.
	 * @param string $method Méthode parmi GET ou POST.
	 * @return Form|string Retourne l'objet en cas d'affection ou l'attribut method de l'objet si aucun paramètre n'est passé.
	 */
	public function method($method=NULL)
	{
		$method = strtoupper($method);
		if (is_string($method) && ($method == 'POST' || $method == 'GET'))
		{
			$this->_method = $method;
			return $this;
		}
		return $this->_method;
	}
	
	/**
	 * Définie ou retourne l'adresse de traitement.
	 * @param string $action Adresse de traitement du formulaire.
	 * @return Form|string Retourne l'objet en cas d'affection ou l'attribut action de l'objet si aucun paramètre n'est passé.
	 */
	public function action($action=NULL)
	{
		if (is_string($action))
		{
			$this->_attrs['action'] = $action;
			return $this;
		}
		return $this->_attrs['action'];
	}
	
	/**
	 * Définie ou retourne l'encodage.
	 * @param boolean $enctype L'encodage du formulaire a multipart/form-data si TRUE.
	 * @return Form|string Retourne l'objet en cas d'affection ou l'attribut enctype de l'objet si aucun paramètre n'est passé.
	 */
	public function enctype($enctype=NULL)
	{
		if ($enctype !== NULL)
		{
			$this->_attrs['enctype'] = ($enctype != FALSE) ? ('multipart/form-data') : (NULL);
			return $this;
		}
		return $this->_attrs['enctype'];
	}
	
	/**
	 * Définie si on accepte l'autocomplete ou non.
	 * @param boolean $complete Si TRUE, on accepte.
	 * @return Form
	 */
	public function autocomplete($complete=NULL)
	{
		if (is_bool($complete))
		{
			$this->_attrs['autocomplete'] = ($complete) ? ('on') : ('off');
		}
		return $this;
	}
	
	/**
	 * Ajoute un nouveau champs au formulaire
	 * @param string $type Type de champ parmi les constantes de la classe.
	 * @param string $name Valeur de l'attribut name du champ.
	 * @return FormField
	 */
	public function add($type, $name=NULL)
	{
		$class_name = 'Form'.$type;
		if (class_exists($class_name) == FALSE)
		{
			$this->error('FIELD_NOT_FOUND',$type);
			return NULL;
		}
		if (is_scalar($name) == FALSE && is_null($name) == FALSE)
		{
			$this->error('NAME_INVALID',$type);
			return NULL;
		}
		if (is_null($name) == FALSE && isset($this->_names[$name]) && $type != self::RADIO)
		{
			$this->error('NAME_EXISTS',$name);
			return NULL;
		}
		if ($type == self::FILE)
		{
		    $this->_attrs['enctype'] = 'multipart/form-data';
		}
		$last = new $class_name();
		if ($this->_fieldset >= 0)
		{
		    $this->_content[$this->_fieldset]->add_content($last);
		}
		else
		{
		    $this->_content[] = $last;
		}
        if (empty($name) == FALSE)
        {
            $last->name = $name;
        }
        if ($this->_required)
        {
            $last->required();
        }
        return $last;
	}
	
	/**
	 * Ajoute un fieldset.
	 * @param string $legend Nom facultatif du fieldset
	 * @return FormField
	 */
	public function add_fieldset($legend=NULL)
	{
	    $class_name = 'Form'.self::FIELDSET;
	    $last = new $class_name();
	    $this->_content[] = $last;
	    if (is_scalar($legend))
	    {
	        $last->legend($legend);
	    }
	    $this->_fieldset = count($this->_content) - 1;
	    return $last;
	}
	
	/**
	 * Termine la redirection des nouveau champ vers le fieldset.
	 * @return Form
	 */
	public function end_fieldset()
	{
		$this->_fieldset = -1;
		return $this;
	}
	
	/**
	 * Ajoute un submit au formulaire.
	 * @param string $value Valeur du bouton submit.
	 * @return FormInputSubmit
	 */
	public function add_submit($value)
	{
	    $class_name = 'Form'.self::SUBMIT;
	    $last = new $class_name();
	    $this->_content[] = $last;
	    if (is_scalar($value))
	    {
	       $last->value($value);
	    }
	    return $last;
	}
	
	/**
	 * Définie tous les champs du formulaire comme requis.
	 * @return Form
	 */
	public function set_all_required()
	{
	    $this->_required = TRUE;
	    return $this;
	}

	/**
	 * Envoie une erreur.
	 * @param string $name Nom de l'erreur.
	 * @param string $var Nom de la variable.
	 */
	private function error($name, $var=NULL)
	{
		$info = str_replace('$var',$var,$this->_errors[$name]);
		trigger_error($info);
	}
	
	/**
	 * Charge des valeurs de la requête dans les champs.
	 */
	public function assign()
	{
	    $this->assign_list($this->_content);
	}
	
	/**
	 * Charge des valeurs de la requête dans une liste de champs.
	 * @param array<HTMLTag> $content Liste de champs.
	 */
	private function assign_list($content)
	{
	    foreach ($content as $field)
	    {
	    	if (empty($field->name) == FALSE && isset($_REQUEST[$field->name]))
	    	{
	    	    if ($field->_name == 'input' && ($field->type == 'radio' || $field->type == 'checkbox'))
	    	    {
	    	        if ($field->value == $_REQUEST[$field->name] || $_REQUEST[$field->name] == 'on')
	    	        {
	    	            $field->checked();
	    	        }
	    	    }
	    	    else 
	    	    {
	    	        $field->value($_REQUEST[$field->name]);
	    	    }
	    	}
	    	elseif ($field->_name == 'fieldset')
	    	{
	    		$this->assign_list($field->_content);
	    	}
	    }
	}
	
	/**
	 * Vérifie les information entrée dans le formulaire.
	 * @return boolean
	 */
	public function check()
	{
	    $result = $this->check_list($this->_content);
	    if (is_object($result))
	    {
	        $this->_error_field = $result;
	        return FALSE;
	    }
	    else 
	    {
	        $this->_error_field = NULL;
	        return TRUE;
	    }
	}
	
	/**
	 * Retourne le premier champ qui n'a pas pu être validé lors de la vérification.
	 * @return FormField Renvoie le champ ou NULL.
	 */
	public function get_error_field()
	{
	    return $this->_error_field;
	}
	
	/**
	 * Vérifie les information entrée dans une liste des champs.
	 * @return FormField Renvoie le premier champ qui n'a pas passé la vérification ou NULL.
	 */
	public function check_list($content)
	{
	    reset($content);
	    $field_invalid = NULL;
	    while (($field = current($content)) && $field_invalid == NULL)
	    {
	        if (empty($field->name) == FALSE && $field->check() == FALSE)
	        {
	        	$field_invalid = $field;
	        }
	        elseif ($field->_name == 'fieldset')
	    	{
	    		$field_invalid = $this->assign_list($field->_content);
	    	}
	    	next($content);
	    }
	    return $field_invalid;
	}
	
	/**
	 * Retourne le champ avec le name passé en paramètre.
	 * @param string $name Valeur de l'attribut name du champ recherché.
	 * @return HTMLTag 
	 */
	public function get($name)
	{
	    $name = strtolower($name);
	    return $this->get_list($name, $this->_content);
	}
	
	/**
	 * Retourne le champ avec le name passé en paramètre dans une liste de champ.
	 * @param string $name Valeur de l'attribut name du champ recherché.
	 * @param array<HTMLTag> Liste de champs.
	 * @return HTMLTag
	 */
	private function get_list($name, $content)
	{
	    reset($content);
	    $field_searched = NULL;
	    while (($field = current($content)) && $field_searched == NULL)
	    {
	    	if (strtolower($field->name) == $name)
	    	{
	    	    $field_searched = $field;
	    	}
	    	elseif ($field->_name == 'fieldset')
	    	{
	    		$field_searched = $this->get_list($name, $field->_content);
	    	}
	    	next($content);
	    }
	    return $field_searched;
	}

    /**
	 * Sauvegarde de formulaire en session.
	 * @return boolean
	 */
	public function save()
	{
		if (isset($_SESSION))
		{
			$_SESSION['__form'] = $this;
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * Charge un formulaire sauvegardé.
	 * @param string $id Identifiant du formulaire.
	 * @return Form Instance du formulaire ou NULL.
	 */
	public static function load($id, $assign=TRUE)
	{
	    if (isset($_SESSION))
	    {
	        $form = (isset($_SESSION['__form'])) ? ($_SESSION['__form']) : (NULL);
	        if (is_object($form) && $form->id == $id)
	        {
                if ($assign)
                {
                    $form->assign();
                }
	        	return $form;
	        }
	    }
        return NULL;
	}
	
	/**
	 * Supprime le formulaire sauvegardé.
	 */
	public static function delete()
	{
		if (isset($_SESSION) && isset($_SESSION['__form']))
		{
			unset($_SESSION['__form']);
		}
	}
	
	/**
	 * Définie la classe CSS par défaut des formulaires.
	 * @param string $class Nom de la classe.
	 */
	public static function set_default_class($class)
	{
	    if (is_scalar($class))
	    {
	        self::$_default_class = $class;
	    }
	}
}
?>