<?php
/**
 * Regex est a classe contenant un ensemble de RegExp.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @copyright 2011-2015 Yoann Chaumin
 */
class Regex
{
	const CODEPOSTAL		= 	'/^(\d{5})$/';
	const DATE 				= 	'/^(\d{4}(-|\/)\d{2}(-|\/)\d{2})|(\d{2}(-|\/)\d{2}(-|\/)\d{4})|(\d{8})$/';
	const DIRNAME           =   '/^(.{1,2}|(\/|\w:\\)?([^(\\|\/|:|\*|\?|"|<|>|\|)]+(\/|\\)))+(\/|\\)$/';
	const ENGLISH_DATE		= 	'/^(\d{4}(-|\/)\d{2}(-|\/)\d{2})$/';
	const FILENAME          =   '/^(\/|\w:\\)?([^(\\|\/|:|\*|\?|"|<|>|\|)]+(\/|\\))*([^(\\|\/|:|\*|\?|"|<|>|\|)]+)$/';
	const FRENCH_DATE		= 	'/^(\d{2}(-|\/)\d{2}(-|\/)\d{4})$/';
	const FILE 				= 	'/^([-.\s\w]{3,30})$/';
	const MAIL 				= 	'/^([a-zA-Z]+([\w_\-\.]*)@([\w\-\.]*)[\w]\.[a-zA-Z]{2,3})$/';
	const URL 				=	'/^((https?:\/\/)?(www\.)?(([a-zA-Z0-9-]){2,}\.){1,4}([a-z]){2,6}(\/([\w-\.#:+?%=&;,]*)?)?\/?)$/';
	const MDP				= 	'/^([\w]{5,20})$/';
	const INTEGER			= 	'/^(\d{0,20})$/';
	const DECIMAL			=	'/^(\d{1,20}([\.|,]\d{0,20})?)$/';
	const PSEUDO			= 	'/^([èéêîïà\w\s]{2,30})$/';
	const PHONE    			= 	'/^(\d{10})$/';
	const SHORT_TEXT		= 	'/^([\.\-!\'?,°";\[\]\(\):\/=«»ÀÁÂÃÄÅàáâãäåÒÓÔÕÖòóôõöÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ&%\s\w]{0,30})$/';
	const LONG_TEXT 		= 	'/^([\.\-!\'?,°";\[\]\(\):\/=«»ÀÁÂÃÄÅàáâãäåÒÓÔÕÖòóôõöÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ&%\s\w]{0,1000})$/';
	const TITLE				= 	'/^([\.\-!\'?,°";\[\]\(\):\/=«»ÀÁÂÃÄÅàáâãäåÒÓÔÕÖòóôõöÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ&%\s\w]{0,50})$/';
	const TITLE_LONG		= 	'/^([\.\-!\'?,°";\[\]\(\):\/=«»ÀÁÂÃÄÅàáâãäåÒÓÔÕÖòóôõöÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ&%\s\w]{0,100})$/';
    const IP                =   '/^((((25[0-5])|(2[0-4]\d)|([0-1]\d{2})|(\d{2})|(\d))\.){3}((25[0-5])|(2[0-4]\d)|([0-1]\d{2})|(\d{2})|(\d)))$/';
	const COLOR_HEXA        =   '/^(#([a-fA-F\d]{1,2}){3})$/'; 
	
	
	/**
	 * Vérifie l'extactitude d'une date.
	 * @param string $chaine Date à vérifier.
	 * @return boolean
	 */
	public static function check_date($chaine)
	{
		if (!preg_match(self::ENGLISH_DATE,$chaine) && !preg_match(self::FRENCH_DATE,$chaine))
		{
			return FALSE;
		}
		$date = explode('-',$chaine);
		if (count($date) == 1)
		{
			$date = explode('/',$chaine);
		}
		if (strlen($date[0]) == 4) //Différenciation des deux types de date.
		{
			$year=$date[0];
			$month=$date[1];
			$day=$date[2];
		}
		else
		{
			$year=$date[2];
			$month=$date[1];
			$day=$date[0];
		}
		return (checkdate($month,$day,$year));
	}
}

?>