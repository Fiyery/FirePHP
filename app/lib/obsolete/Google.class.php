<?php
class Google extends Singleton
{
    /**
     * Instance de Singleton.
     * @var Google
     */
    public static $_instance = NULL;
    
    private $_website = NULL;
    
    public function __construct($website)
    {
        $this->website = $website;
    }
    
    public function get_nb_indexed_pages()
    {
        $content = file_get_contents($this->_get_search_url(array('site:club-manga.fr')));
        $content = substr($content, strpos($content, '<body'));
        foreach (array('a', 'li', 'script', 'table') as $tag)
        {
            $content = preg_replace('#<'.$tag.'(.*)</'.$tag.'>#U', '', $content);
        }
        preg_match('#<div.*id="resultStats".*>([^\d]*)\s(.*)([^\d]*)<\/div>#iU', $content, $m);
        return (isset($m[2])) ? ($m[2]) : (-1);
    }
    
    private function _get_search_url($words, $page = 0)
    {
        $words = implode('+',$words);
        $start = $page * 10;
        $params_url = array();
        $params_url[] = 'q='.$words;					// Mots de la recherche.
        $params_url[] = 'oq='.$words;					// Mots de la recherche en mode OR.
        $params_url[] = 'start='.$start;				// Premier résultat à afficher.
        $params_url[] = 'ie=utf-8';						// Encodage entré.
        $params_url[] = 'oe=utf-8';	 					// Encodage sortie.
        $params_url[] = 'hl=fr';	 					// Langage du navigateur.
        $params_url[] = 'lr=lang_fr';					// Langage de la recherche.
        $params_url[] = 'client=firefox-beta';			// Client de la recherche.
        $params_url[] = 'rls=org.mozilla:fr:official';	// Client de la recherche.
        $params_url[] = 'biw=1600';						// Pixel largeur de l'écrant du client.
        $params_url[] = 'bih=753';						// Pixel hauteur de l'écrant du client.
        return 'http://www.google.fr/search?'.implode('&',$params_url);
    }
    
    /**
     * Retourne une instance de la classe avec les arguments correctement ordonnés selon le constructeur de la classe.
     * @param array $args Tableau d'arguments du constructeur.
     * @return Google
     */
    protected static function __create($args)
    {
    	return new self($args[0]);
    }
}
?>