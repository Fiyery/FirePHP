<?php
namespace FirePHP\Helper;

/**
 * Str regroupe des fonctions utiles sur les chaînes de caractères.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 */
class Str
{
    /**
     * Liste des méthodes utilisées pour la comparaison.
     * @var unknown
     */
    const CMP_LEVENSHTEIN = 1;
    const CMP_SOUNDER = 2;
    const CMP_METAPHONE = 4;
    const CMP_LENGTH = 8;
    const CMP_SIMILAR = 16;
    
    /**
     * Poids de la méthode dans l'algorithme de comparaison (pertinence).
     * @var int
     */
    const CMP_STRENGTH_LEVENSHTEIN = 2;
    const CMP_STRENGTH_SOUNDER = 1;
    const CMP_STRENGTH_METAPHONE = 1;
    const CMP_STRENGTH_LENGTH = 1;
    const CMP_STRENGTH_SIMILAR = 2;
    
    /**
     * Formate une chaine pour la mettre dans l'url.
     * @param string $string Chaîne de caractères à traiter.
     * @return string Chaîne formatée.
     */
    public static function format_url($string)
    {
    	return self::format($string,'_');
    }
    
    /**
     * Formate une chaine en enlevant tous les caractères spéciaux.
     * @param string $string Chaîne de caractères à traiter.
     * @param char $space Caractère de remplacement des espaces.
     * @return string Chaîne formatée.
     */
    public static function format($string, $space='')
    {
        $special_char = ['À','Á','Â','Ã','Ä','Å','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï','Ò','Ó','Ô','Õ','Ö','Ù','Ú','Û','Ü','Ý','à','á','â','ã','ä','å','ç','è','é','ê','ë','ì','í','î','ï','ð','ò','ó','ô','õ','ö','ù','ú','û','ü','ý','ÿ'];
        $normal_char = ['A','A','A','A','A','A','C','E','E','E','E','I','I','I','I','O','O','O','O','O','U','U','U','U','Y','a','a','a','a','a','a','c','e','e','e','e','i','i','i','i','o','o','o','o','o','o','u','u','u','u','y','y'];
    	$string = str_replace($special_char, $normal_char, $string);
    	$string = preg_replace('/[^\w\s]/',' ',$string);
		$string = trim($string);
		$string = str_replace('  ',' ',$string, $count);
		while ($count > 0)
		{
			$string = str_replace('  ',' ',$string, $count);
		}
    	$string = str_replace(' ',$space,$string);
    	$string = strtolower($string);
    	return $string;
    }
    
    /**
     * Compare la ressemblance entre deux chaînes de caractères.
     * @param string $string1 Première chaîne à comparer.
     * @param string $string2 Deuxième chaîne à comparer.
     * @param int $options Ensembles de Méthodes de comparaison à utiliser parmis les constantes CMP_*.
     * @param boolean $reduce Si TRUE, les chaînes seront formatées en enlevant tous les caractères spéciaux et remplacant les majuscules.
     * @return float Nombre compris entre 1 (chaîne égale) et 0 (chaîne totalement différente).
     */
    public static function similar($string1, $string2, $options=31, $reduce=FALSE)
    {
        if (is_string($string1) == FALSE || is_string($string2) == FALSE)
        {
            return 0;
        }
        if ($reduce)
        {
            $string1 = self::format($string1);
            $string2 = self::format($string2);  
        }
        if (strcmp($string1, $string2) == 0)
        {
            return 1;
        }
        $length1 = strlen($string1);
        $length2 = strlen($string2);
        $max_length = max($length1, $length2);
        $min_length = min($length1, $length2);
        $rates = array();
        if ($options - self::CMP_SIMILAR >= 0)
        {
            $value = similar_text($string1, $string2)/$max_length;
            $rates = array_pad($rates, self::CMP_STRENGTH_SIMILAR, $value);
            $options -= self::CMP_SIMILAR;
        }
        if ($options - self::CMP_LENGTH >= 0)
        {
            $value = $min_length / $max_length;
            $rates = array_pad($rates, count($rates) + self::CMP_STRENGTH_LENGTH, $value);
            $options -= self::CMP_LENGTH;
        }
        if ($options - self::CMP_METAPHONE >= 0)
        {
            $value = metaphone($string1) == metaphone($string2);
            $rates = array_pad($rates, count($rates) + self::CMP_STRENGTH_METAPHONE, $value);
        	$options -= self::CMP_METAPHONE;
        }
        if ($options - self::CMP_SOUNDER >= 0)
        {
            $value = soundex($string1) == soundex($string2);
            $rates = array_pad($rates, count($rates) + self::CMP_STRENGTH_SOUNDER, $value);
        	$options -= self::CMP_SOUNDER;
        }
        if ($options - self::CMP_LEVENSHTEIN >= 0)
        {
            $value = 1 - (levenshtein($string1, $string2)/$max_length);
            $rates = array_pad($rates, count($rates) + self::CMP_STRENGTH_LEVENSHTEIN, $value);
        	$options -= self::CMP_LEVENSHTEIN;
        }
        $count = count($rates);
        return ($count > 0) ? (array_sum($rates) / $count) : (0);
	}
	
	/**
	 * Transforme un tableau d'entier en tableau interval si les entiers se suivent.
	 * @param array $numbers Tableau d'entier
	 * @return array
	 */
	public static function numbers_to_interval(array $numbers) : array
	{
		$reduced = [];
		$count = count($numbers);
		sort($numbers);
		for($i = 0; $i < $count - 1; $i++) 
		{
			if ($numbers[$i] + 1 !== $numbers[$i + 1])
			{
				$reduced[] = $numbers[$i];
			}
			else 
			{
				$j = $i + 1;
				while ($numbers[$j] + 1 == $numbers[$j + 1] && $j < $count)
				{
					$j++;
				}
				$reduced[] = $numbers[$i] . "-" . $numbers[$j];
				$i = $j;
			}
		}
		return $reduced;
	}
}
?>