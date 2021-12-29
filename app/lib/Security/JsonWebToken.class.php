<?php
namespace FirePHP\Security;

/**
 * Gère la génération et la validation du token JWT.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 */
class JsonWebToken 
{
	/**
	 * Génération du token JWT.
	 * @param string $secret Clé secrète.
	 * @param array $payload Information détenues dans le token.
	 * @return string
	 */
	public static function get(string $secret, array $payload = []) : string
	{
		// Header
		$header = self::_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));

		// Payload
		$payload = self::_encode(json_encode(array_merge($payload, [
			"iat"	=> time(),
			"iss"	=> (isset($_SERVER["SERVER_NAME"])) ? ($_SERVER["SERVER_NAME"]) : (""),
			"exp" 	=> time() + 3600
		])));
		
		// Signature
		$signature = self::_encode(hash_hmac('sha256', $header . "." . $payload, $secret, TRUE));

		return $header . "." . $payload . "." . $signature;
	}

	/**
	 * Valide la conformité d'un token.
	 * @param string $secret Clé secrète.
	 * @param string $token Token à valider.
	 * @return bool
	 */
	public static function check(string $secret, string $token) : bool
	{
		$parts = explode("." , $token);
		if (count($parts) !== 3)
		{
			return FALSE;
		}
		$build_token = $parts[0] . "." . $parts[1] . "." . self::_encode(hash_hmac('sha256', $parts[0] . "." . $parts[1], $secret, TRUE));
		return ($build_token === $token);
	}

	/**
	 * Fonction de raccourci pour la convertion en base64.
	 * @param string $value Chaîne à convertir.
	 * @return string
	 */
	private static function _encode(string $value) : string
	{
		return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($value));
	}
}
?>