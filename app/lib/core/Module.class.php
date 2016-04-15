<?php
/**
 * Module est la classe mère de l'ensemble des actions de traitement de données de chaque page.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @copyright 2011-2015 Yoann Chaumin
 * @uses ServiceContainer
 */
class Module
{

	/**
	 * Nom par défaut du template charger.
	 * @var unknown
	 */
	protected $_tpl_name= NULL;
	
	/**
	 * Instance du containeur de services.
	 * @var ServiceContainer
	 */
	protected $_services;
	
	/**
	 * Constructeur.
	 * @param array<object> $config Tableau qui défini la valeur de chaque attribut de la classe.
	 */
	public function __construct($services)
	{
	    $this->_services = $services;
	}
	
	/**
	 * Récupère un service.
	 * @param string $name Nom du service
	 */
	public function __get($name)
	{
	    return $this->_services->get($name);
	}
}
?>