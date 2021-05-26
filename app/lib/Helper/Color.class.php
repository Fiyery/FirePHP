<?php 
namespace FirePHP\Helper;

/**
 * Helper pour l'utilisation des couleurs principalement orienté sur la conversion.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 */
class Color 
{
	/**
	 * Convertie une couleur depuis un code HEXA en RVG.
	 * @param string $color
	 * @return array
	 */
	public static function hex_to_rgb(string $color) : array 
	{
		if (preg_match("/^\#([0-9A-F]{2})([0-9A-F]{2})([0-9A-F]{2})$/", $color, $match) === FALSE)
		{
			return [];
		}
		var_dump($match);
		return [
			hexdec($match[1]),
			hexdec($match[2]),
			hexdec($match[3])
		];
	}

	/**
	 * Convertie une couleur RVG en code HEXA.
	 * @param array $colors
	 * @return string
	 */
	public static function rgb_to_hex(array $colors) : string
	{
		$c = array_values($colors);
		if (count($c) !== 3 && $c[0] < 0 && $c[1] < 0 && $c[2] < 0 && $c[0] > 255 && $c[1] > 255 && $c[2] > 255)
		{
			return "";
		}
		return strtoupper("#" . dechex($c[0]) . dechex($c[1]) . dechex($c[2]));
	}
}
?>