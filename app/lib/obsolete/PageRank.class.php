<?php
/**
 * PageRank fournie le page rank d'un site.  
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 */
class PageRank
{
    /**
     * Site à analyser.
     * @var string
     */
	private $_website;
	
	/**
	 * Valeur du page rank.
	 * @var string
	 */
	private $_page_rank;
	
	/**
	 * Constructeur.
	 */
	public function __construct()
	{
		$this->_page_rank = NULL;
	}
	
	/**
	 * Récupère le page rank d'un site.
	 * @param string $url Site à analyser.
	 * @return int
	 */
	public function get_pagerank($url) 
	{
	    $this->_website = (substr($url,0,7) != 'http://') ? ('http://'.$url) : ($url);
		// 1ère méthode.
		$key = 'Q8ZQAf4herV4PvS'; // Trouvée sur http://honorcoders.com/hc-google-pagerank-api/
		$this->_page_rank = file_get_contents('http://honorcoders.com/prapi/?key='.$key.'&url='.$this->_website);
		if ($this->_page_rank === NULL || $this->_page_rank < 0) // 2ème méthode.
		{
			$query = "http://toolbarqueries.google.com/tbr?client=navclient-auto&ch=".$this->_check_hash($this->_hash_url($this->_website)). "&features=Rank&q=info:".$this->_website."&num=100&filter=0";
			$data = file_get_contents($query);
			$pos = strpos($data,"Rank_");
			if($pos !== FALSE)
			{
				$this->_page_rank = substr($data, $pos+9);
			}
		}
		return $this->_page_rank;
	}

	/**
	 * Transforme une chaine spécifique en numéros qui peuvent être traités.
	 * @param string $str Chaîne à traiter.
	 * @param int $check Hexadécimal.
	 * @param int $magic Hexadécimal.
	 * @return int
	 */
	private function _str_to_num($str, $check, $magic)
	{
		$int_32_unit = 4294967296; // 2^32

		$length = strlen($str);
		for ($i = 0; $i < $length; $i++) 
		{
			$check *= $magic;

			if ($check >= $int_32_unit) 
			{
				$check = ($check - $int_32_unit * (int) ($check / $int_32_unit));
				$check = ($check < -2147483648) ? ($check + $int_32_unit) : $check;
			}
			$check += ord($str{$i});
		}
		return $check;
	}

	/**
	 * Convertie l'url sous sa forme codée (hashée).
	 * @param string $string
	 * @return boolean
	 */
	private function _hash_url($string)
	{
		$check1 = $this->_str_to_num($string, 0x1505, 0x21);
		$check2 = $this->_str_to_num($string, 0, 0x1003F);
		$check1 >>= 2;
		$check1 = (($check1 >> 4) & 0x3FFFFC0 ) | ($check1 & 0x3F);
		$check1 = (($check1 >> 4) & 0x3FFC00 ) | ($check1 & 0x3FF);
		$check1 = (($check1 >> 4) & 0x3C000 ) | ($check1 & 0x3FFF);
		$t1 = (((($check1 & 0x3C0) << 4) | ($check1 & 0x3C)) <<2 ) | ($check2 & 0xF0F );
		$t2 = (((($check1 & 0xFFFFC000) << 4) | ($check1 & 0x3C00)) << 0xA) | ($check2 & 0xF0F0000 );
		return ($t1 | $t2);
	}

	/**
	 * Vérifie le hash de l'url
	 * @param int $hashnum Chaîne formatée en chiffre.
	 * @return string
	 */
	private function _check_hash($hashnum)
	{
		$check_byte = 0;
		$Flag = 0;

		$hash_str = sprintf('%u', $hashnum) ;
		$length = strlen($hash_str);

		for ($i = $length - 1; $i >= 0; $i --) 
		{
			$re = $hash_str{$i};
			if (1 === ($Flag % 2)) 
			{
				$re += $re;
				$re = (int)($re / 10) + ($re % 10);
			}
			$check_byte += $re;
			$Flag ++;
		}

		$check_byte %= 10;
		if (0 !== $check_byte) 
		{
			$check_byte = 10 - $check_byte;
			if (1 === ($Flag % 2) ) 
			{
				if (1 === ($check_byte % 2)) 
				{
					$check_byte += 9;
				}
				$check_byte >>= 1;
			}
		}

		return '7'.$check_byte.$hash_str;
	}
}
?>