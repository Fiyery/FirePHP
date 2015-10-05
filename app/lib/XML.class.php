<?php
/**
 * XML est une interface simplifiée de gestion de contenu XML et notamment RSS.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @copyright 2011-2015 Yoann Chaumin
 */
class XML 
{
    /**
     * Information du document.
     * @var DOMDocument
     */
	private $document = NULL;
	
	/**
	 * Encodage du document.
	 * @var string
	 */
	private $charset = 'UTF-8';
	
	/**
	 * Constructeur.
	 */
	public function __construct()
	{

	}

	/**
	 * Charge un fichier xml existant.
	 * @param string $filename Chemin du fichier.
	 * @return XML Le document XML ou NULL en cas d'échec.
	 */
	public function load($filename)
	{
		if (file_exists($filename))
		{
			$this->document = new DOMDocument();
			if ($this->document->load(realpath($filename)))
			{
				return $this;
			}
		}
		return NULL;
	}

	/**
	 * Crée un nouveau document XML.
	 */
	public function create()
	{
		$this->document = new DOMDocument('1.0',$this->charset);
	}
	
	/**
	 * Crée un fichier RSS.
	 * @return XML
	 */
	public function create_rss()
	{
		$this->create();
		$this->set_root('rss',array('version'=>'2.0','xmlns:atom'=>'http://www.w3.org/2005/Atom'));
		return $this;
	}
	
	/**
	 * Retourne la racine du document XML.
	 * @return DOMNode
	 */
	public function get_root()
	{
		return ($this->document->firstChild !== NULL) ? ($this->document->firstChild) : ($this->set_root());
	}
	
	/**
	 * Définie l'encodage du document XML.
	 * @param string $charset Encodage du document.
	 * @return XML
	 */
	public function set_charset($charset)
	{
		if (in_array(strtolower($charset), array('iso-8859-1','iso-8859-15','utf-8','windows-1252')))
		{
			$this->charset = $charset;
		}
		return $this;
	}
	
	/**
	 * Définie la racine du fichier XML. 
	 * @param string $name Nom de la racine.
	 * @param array<string> $attributes Liste d'atrributs de la racine en couple clé-valeur. 
	 * @return DOMNode Racine du document.
	 */
	public function set_root($name='root', $attributes=NULL)
	{
		$name = (is_string($name)) ? ($name) : ('root');
		$root = $this->document->createElement($name); 
		if (is_array($attributes))
		{
			foreach ($attributes as $name => $value)
			{
				$root->setAttribute($name,$value);
			}
		}
		if ($this->document->firstChild !== NULL)
		{
			$this->document->removeChild($this->document->firstChild);
		}
		$root = $this->document->appendChild($root); 
		return $root;
	}
	
	/**
	 * Ajoute de l'information dans le document XML. 
	 * @param object|array $child Noeud à ajouter soit sous forme de liste ou d'un objet.
	 * @param object $root Parent du noeud.
	 * @return XML
	 */
	public function add_child($child=NULL, $root=NULL)
	{
		if ($child == NULL)
		{
			return $this;
		}
		if ($this->document == NULL)
		{
			$this->create();
		}
		if ($root == NULL || !is_object($root))
		{
			$root = $this->get_root();
		}
		if (is_object($child) && is_array($child) == FALSE)
		{
			$occ = $this->document->createElement(get_class($child));
			$occ = $root->appendChild($occ);
			foreach ($child as $n => $v)
			{
				$param = $this->document->createElement($n);
				$param = $occ->appendChild($param);
				$value = $this->document->createTextNode($this->convert($v));
				$param->appendChild($value);
			}
		}
		elseif (is_array($child))
		{
			if ($root->hasChildNodes())
			{
				$occ = $this->document->createElement('Array');
				$occ = $root->appendChild($occ);	
			}
			else
			{
				$parent = $root->parentNode;
				$this->document->removeChild($root);
				$occ = $parent->createElement('Array'); 
				$occ = $parent->appendChild($occ); 
			}
			foreach ($child as $n => $v)
			{
				if (is_array($v) || is_object($v))
				{
					$this->add_child($v,$occ);
				}
				else 
				{
					$param = $this->document->createElement($n);
					$param = $occ->appendChild($param);
					$value = $this->document->createTextNode($this->convert($v));
					$param->appendChild($value);
				}
			}
		}
		return $this;
	}
	
	/**
	 * Ajoute un channel au document RSS. 
	 * @param string $title Titre de la chaîne.
	 * @param string $description Description de la chaîne.
	 * @param string $link Lien cible de la chaîne.
	 * @param string $url_rss Lien du document RSS de la chaîne.
	 * @return XML
	 */
	public function add_channel($title, $description, $link, $url_rss=NULL)
	{
		if ($this->document !== NULL)
		{
			$root = $this->get_root();
			
			// Création du noeud channel.
			$element_channel = $this->document->createElement('channel');
			$element_channel = $root->appendChild($element_channel);
			
			// Création du noeud title et ajout du texte à l élément.
			$element_title = $this->document->createElement('title');
			$element_title = $element_channel->appendChild($element_title);
			$content_title = $this->document->createTextNode($this->convert($title));
			$content_title = $element_title->appendChild($content_title);
			
			// Création du noeud atom.
			if ($url_rss == NULL)
			{
				$url_rss = (substr($link,-1) != '/') ? ($link.'/rss.xml') : ($link.'rss.xml');
			}
			$element_atom = $this->document->createElement('atom:link');
			$element_atom->setAttribute('href',$url_rss);
			$element_atom->setAttribute('rel','self');
			$element_atom->setAttribute('type','application/rss+xml');
			$element_atom = $element_channel->appendChild($element_atom);
		 
			// Création du noeud description et du texte pour le noeud description.
			$element_description = $this->document->createElement('description');
			$element_description = $element_channel->appendChild($element_description);
			$content_description = $this->document->createTextNode($this->convert($description)); 
			$content_description = $element_description->appendChild($content_description); 
		 
			// Création du noeud link et ajout du texte à l élément.
			if (substr($link,0,7) != 'http://')
			{
				$link = 'http://'.$link;
			}
			$element_link = $this->document->createElement('link');
			$element_link = $element_channel->appendChild($element_link);
			$content_link = $this->document->createTextNode($this->convert($link));
			$content_link = $element_link->appendChild($content_link);
			
			return $this;
		}
		return NULL;
	}
	
	/**
	 * Ajoute une news dans le document RSS.
	 * @param int $channel_index Index de la chaîne.
	 * @param string $guid Identidiant unique de la news.
	 * @param string $title Titre de la news.
	 * @param string $description Contenu de la news.
	 * @param int $timestamp Date en timestamp de création de la news.
	 * @param string $author Nom de l'auteur de la news.
	 * @param string $link Lien cible de la news.
	 * @return XML
	 */
	public function add_news($channel_index, $guid, $title, $description, $timestamp, $author=NULL, $link=NULL)
	{
 		if ($this->document !== NULL)
 		{
			// On récupère le channel.
			$list_elements_channel = $this->document->getElementsByTagName('channel');
			if ($list_elements_channel->length <= $channel_index)
			{
				return FALSE;
			}
			$element_channel = $list_elements_channel->item($channel_index);
			
			// Création du noeud item.
			$element_item = $this->document->createElement('item');
			$element_item = $element_channel->appendChild($element_item);
			
			// Création du guid (identifiant) de l'item.
			$element_guid = $this->document->createElement('guid');
			$element_guid = $element_item->appendChild($element_guid);
			$element_guid->setAttribute('isPermaLink','false');
			$content_guid = $this->document->createTextNode($this->convert($guid));
			$content_guid = $element_guid->appendChild($content_guid);
		 
			// Création du noeud title et ajout du texte à l élément.
			$element_title = $this->document->createElement('title');
			$element_title = $element_item->appendChild($element_title);
			$content_title = $this->document->createTextNode($this->convert($title));
			$content_title = $element_title->appendChild($content_title);
			
			// Création du noeud description et ajout du texte à l élément.
			$element_description = $this->document->createElement('description');
			$element_description = $element_item->appendChild($element_description);
			$content_description = $this->document->createTextNode($this->convert($description));
			$content_description = $element_description->appendChild($content_description);
		 
			// Création du noeud link et ajout du texte à l élément.
			if ($link != NULL)
			{
				if (substr($link,0,6) != 'http://')
				{
					$link = 'http://'.$link;
				}
				$element_link = $this->document->createElement('link');
				$element_link = $element_item->appendChild($element_link);
				$content_link = $this->document->createTextNode($this->convert($link));
				$content_link = $element_link->appendChild($content_link);	
			}
		 
			// Création du noeud pubDate et ajout du texte à l élément.
			$element_date = $this->document->createElement('pubDate');
			$element_date = $element_item->appendChild($element_date);
			$date_config = date_default_timezone_get();
			date_default_timezone_set('GMT');
			$content_date = $this->document->createTextNode(date('D, j M Y H:i:s',$timestamp).' GMT');
			date_default_timezone_set($date_config);
			$content_date = $element_date->appendChild($content_date);
		 
			
			// Création du noeud author et ajout du texte à l élément.
			if ($author != NULL)
			{
				$element_author = $this->document->createElement('author');
				$element_author = $element_item->appendChild($element_author);
				$content_author = $this->document->createTextNode($this->convert($author));
				$content_author = $element_author->appendChild($content_author);
			}			
			return $this;
 		}
 		return NULL;
	}
	
	/**
	 * Retourne la ligne de code pour lier le fichier RSS à la page web.
	 * @param string $filename Lien du fichier RSS.
	 * @return string
	 */
	public function get_rss_link($filename)
	{
		return "<link rel='alternate' type='application/rss+xml' title='Flux RSS' href='".$filename."' />";
	}
	
	/**
	 * Sauvegarde un fichier rss.
	 * @param string $filename Nom du nouveau fichier.
	 * @return boolean
	 */
	public function save($filename)
	{
		if ($this->document !== NULL && file_exists(dirname($filename)))
		{
			if (substr($filename,-4) != '.xml')
			{
				$filename .= '.xml';
			}
			return ($this->document->save($filename));
		}
		else
		{
			return FALSE;
		}
	} 

	/**
	 * Retourne le contenu du document XML.
	 * @return string
	 */
	public function get()
	{
		return $this->document->saveXML();
	}

	/**
	 * Convertie une chaine de caractère en UTF-8 si elle ne l'est pas. UTF-8 est l'encodage utilisé par la classe DomDocument.
	 * @param string $string Chaîne à convertir.
	 * @return string Chaîne encodée en UTF-8.
	 */
	public function convert($string)
	{
		return (strtolower(mb_detect_encoding($string)) == 'utf-8') ? ($string) : (utf8_encode($string));
	}
}
?>