<?php

/**
 * Minifier gère la minification des fichiers CSS et JS.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @copyright 2011-2015 Yoann Chaumin
 */
class Minifier
{
	/**
	 * Minifie du contenu en fonction du content-type passé en paramètre.
	 * @param string $content_type Content-Type du contenu.
	 * @param string $content Contenu à minifier.
	 * @return string Contenu minifié.
	 */
	public static function minify($content_type,$content)
	{
		$minified_content = NULL;
		switch ($content_type)
		{
			case 'text/css' : $minified_content = self::minify_css($content); break;
			case 'text/javascript' : $minified_content = self::minify_js($content); break;
		}
		return $minified_content;
	}
	
	/**
	 * Minifie du contenu CSS.
	 * @param string $content Contenu CSS.
	 * @return string Contenu CSS minifié.
	 */
	public static function minify_css($content)
	{
		// Suppression des espaces doubles.
		$content = preg_replace('#\s+#', ' ', $content);
		// Suppression commentaires.
		$content = preg_replace('#/\*.*?\*/#s', '', $content);
		// Suppression des caractères inutiles.
		$content = str_replace('; ', ';', $content);
		$content = str_replace(': ', ':', $content);
		$content = str_replace(array('{ ',' {'), '{', $content);
		$content = str_replace(', ', ',', $content);
		$content = str_replace(array(';}','} '), '}', $content);
		return $content;
	}
	
	/**
	 * Minifie du contenu JS.
	 * @param string $content Contenu JS.
	 * @return string Contenu JS minifié.
	 */
	public static function minify_js($content)
	{
		// Suppression commentaires.
		$content = preg_replace('#\/\*[^*]*\*\/#', '', $content);
		$content = preg_replace('#\/\*\*(.*)\*\/#sU', '', $content);
		$content = preg_replace('#\/\/\s[^\n]*#', '', $content);
		// Suppression des caractères inutiles.
		$content = str_replace('return',' return',$content);
		$content = str_replace(array("\t","\n\r","\r","\n"),'',$content);
		$content = str_replace(array('{ ',' {'),'{',$content);
		$content = str_replace(array('} ',' }'),'}',$content);
		$content = str_replace(array(': ',' :'),':',$content);
		$content = str_replace(array(', ',' ,'),',',$content);
		$content = str_replace(array('= ',' ='),'=',$content);
		$content = str_replace(array('| ',' |'),'|',$content);
		$content = str_replace(array('& ',' &'),'&',$content);
		$content = str_replace(array('( ',' ('),'(',$content);
		$content = str_replace(array(') ',' )'),')',$content);
		$content = str_replace(array('< ',' <'),'<',$content);
		$content = str_replace(array('> ',' >'),'>',$content);
		return $content;
	}
}
?>