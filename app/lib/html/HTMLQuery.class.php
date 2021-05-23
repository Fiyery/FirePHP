<?php
namespace FirePHP\Html;

use DOMXPath;
use DOMDocument;

/**
 * HTMLQuery permet de récupérer une portion d'un code HTML en fonction d'un XPath ou d'un sélection CSS. 
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 */
class HTMLQuery 
{
	/**
	 * Contient le code HTML sous forme de DOMDocument. 
	 * @var DOMDocument
	 */
	private $_html;
	
	/**
	 * Constructeur
	 * @param String $html Code HTML à parcourir.
	 */
	public function __construct($html)
	{
		$this->_html = new DOMDocument();
		libxml_use_internal_errors(true);
		$this->_html->loadHTML($html);
		libxml_use_internal_errors(false);
	}
	
	/**
	 * Retourne les portions de code correspondant au XPath.
	 * @param String $path XPath de sélection.
	 * @return Liste de code HTML.
	 */
	public function get_elements_by_xpath($path)
	{
		if (is_string($path) == FALSE)
		{
			return NULL;
		}
		$xpath = new DOMXPath($this->_html);
		$list = $xpath->query(trim($path));
		$html = array();
		for($i=0; $i < $list->length; $i++)
		{
			$el = $list->item($i);
			$html[] = $el->ownerDocument->saveHTML($el);
		}
		return $html;
	}
	
	/**
	 * Retourne les portions de code correspondant au sélecteur CSS.
	 * @param String $path CSS de sélection.
	 * @return Liste de code HTML.
	 */
	public function get_elements_by_css_path($path)
	{
		if (is_string($path) == FALSE)
		{
			return NULL;
		}
		$xpath = '//'.preg_replace('#(\s+)#i', '/descendant::', trim($path));
		$xpath = str_replace(array('/>/', '/>', '>/', '>'), '/', $xpath);
		$xpath = str_replace(array('/+/', '/+', '+/', '+'), '/following-sibling::', $xpath);
		$xpath = str_replace(',', '|', $xpath);
		$xpath = preg_replace('#\#([^\/]+)#', '*[@id="$1"]', $xpath);
		$xpath = preg_replace('#([\/|:])\.([^\/]+)#', '$1*[contains(@class,"$2")]', $xpath);
		$xpath = preg_replace('#\.([^\/]+)#', '[contains(@class,"$1")]', $xpath);
		$xpath = str_replace(':first-child', '[1]', $xpath);
		$xpath = str_replace(':last-child', '[last()]', $xpath);
		$xpath = preg_replace('#:nth-child\((\w+)\)#', '[$1]', $xpath);
		return $this->get_elements_by_xpath($xpath);		
	}

	/**
	 * Retourne le code HTML analysé.
	 * @return string
	 */
	public function get()
	{
		return $this->_html->saveHTML();
	}
}
?>