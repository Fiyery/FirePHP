<?php
/**
 * SecureBFA (Brut-Force Attack) protège contre les attaques massives.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @copyright 2011-2015 Yoann Chaumin
 * @uses SingletonSession
 */
class SecureBFA extends SingletonSession
{
	/**
	 * A chaque tentative, on rajoute le nombre de secondes initales.
	 * Avec 60 secondes intiales : 5 + 53 tentatives par jour.
	 */
	const ALGORITHM_ADD = 0;
	
	/**
	 * A chaque tentative, le nombre de secondes initale est multiplié par 2 à la puissance nombre de tentative moins 1.
	 * Avec 60 secondes intiales : 5 + 10 tentatives par jour.
	 */
	const ALGORITHM_POW = 1;
	
	/**
	 * Instance de singleton.
	 * @var SecureBFA
	 */
	protected static $_instance = NULL;
	
	/**
	 * Temps de la dernière génération en timestamp.
	 * @var int
	 */
	private $_time;
	
	/**
	 * Compteur d'accès.
	 * @var int
	 */
	private $_count;
	
	/**
	 * Définie s'il attente ou non.
	 * @var boolean
	 */
	private $_wait = FALSE;
	
	/**
	 * Limite d'accès.
	 * @var int
	 */
	private $_limit_count = 5;
	
	/**
	 * Nombre de secondes initial à attendre.
	 * @var int
	 */
	private $_waiting_time = 60;
	
	/**
	 * Définie la façon dont l'attente augmentera.
	 * @var int
	 */
	private $_algorithm = self::ALGORITHM_ADD;
	
	/**
	 * Constructeur.
	 */
	protected function __construct()
	{
		$this->_time = time();
		$this->_count = 1;
	}
	
	/**
	 * Définie le nombre de tentatives maximal autorisées.
	 * @param int $nb Nombre de tentatives.
	 */
	public function set_tentative($nb)
	{
		if (is_numeric($nb))
		{
			$this->_limit_count = $nb;
		}
	}
	
	/**
	 * Définie le nombre de secondes initial à attendre.
	 * @param int $seconds Nombre de secondes.
	 */
	public function set_time($seconds=60)
	{
		if (is_numeric($seconds))
		{
			$this->_waited_time = $seconds;
		}
	}
	
	/**
	 * Définie l'algorithme de croissance.
	 * @param int $algorithm Algorithme parmi les constants.
	 */
	public function set_algorithm($algorithm=self::ALGORITHM_ADD)
	{
		if (in_array($algorithm, array(self::ALGORITHM_ADD, self::ALGORITHM_POW)))
		{
			$this->_algorithm = $algorithm;
		}
	}
	
	/**
	 * Vérifie s'il faut attentre pour exécuter la requête.
	 * @return int Nombre de secondes à attendre.
	 */
	public function wait()
	{
		if ($this->_count > $this->_limit_count)
		{
			$seconds = $this->calcul();
		    if ($this->_wait == FALSE)
		    {
		        $this->_wait = TRUE;
		        return $this->_time + $seconds - time();
		    } 
		    else 
		    {
		        if ($this->_time + $seconds > time())
		        {
		            return $this->_time + $seconds - time();
		        }
		        else
		        {
		            $this->_wait = FALSE;
		        }
		    }
		}
		$this->_time = time();
		$this->_count++;
		return 0;
	}	
	
	/**
	 * Calcul le nombre de seconde à attendre.
	 * @return int Nombre de secondes.
	 */
	public function calcul()
	{
		switch ($this->_algorithm)
		{
			case self::ALGORITHM_ADD :
			{
				$seconds = ($this->_count - $this->_limit_count) * 60;
				break;
			}
			case self::ALGORITHM_POW :
			{
				$seconds = pow(2, ($this->_count - $this->_limit_count)-1) * 60;
				break;
			}
		}
		return $seconds;
	}
	
	/**
	 * Réinitialise le bloqueur de force brute.
	 */
	public function reset()
	{
		$this->_count = 1;
		$this->_time = time();
	}
	
}