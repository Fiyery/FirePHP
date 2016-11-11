<?php
/**
 * ExceptionManager est la classe de gestion des erreurs et exceptions PHP personnalisée ou standard.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses Singleton
 */
class ExceptionManager extends Singleton
{	
	/**
	 * Instance de singleton.
	 * @var Error
	 */
	protected static $_instance = NULL;
	
	/**
	 * Définie si les erreurs devront être affichées.
	 * @var boolean
	 */
	private $_show = FALSE;
	
	/**
	 * Liste des erreurs.
	 * @var array
	 */
	private $_errors;
	
	/**
	 * Liste des exceptions.
	 * @var array
	 */
	private $_exceptions;
	
	/**
	 * Chemin du fichier d'erreur.
	 * @var string
	 */
	private $_file = 'error.log';
	
	/**
	 * Définie si l'on sauvegarde les erreurs et exception ou non.
	 * @var boolean
	 */
	private $_actived_save = FALSE;
	
	/**
	 * Informations supplémentaires à rajouter à l'erreur.
	 * @var array<string>
	 */
	private $_data;
	
	/**
	 * Etat courant de l'activité de la classe.
	 * @var boolean
	 */
	private $_enable = TRUE;

	/**
	 * Constructeur.
	 */
	protected function __construct()
	{
		$this->_errors = array();
		$this->_exceptions = array();
	}
	
	/**
	 * Démarre la gestion des erreurs.
	 */
	public function start()
	{
	    ini_set('display_errors', 1);
	    ini_set('error_reporting', E_ALL);
	}

	/**
	 * Active la gestion des erreurs de la classe.
	 */
	public function active_error()
	{
		set_error_handler(array($this, 'handle_error'));
		register_shutdown_function(array($this, 'handle_fatal'));
	}
	
	/**
	 * Active la gestion des exceptions de la classe.
	 */
	public function active_exception()
	{
		set_exception_handler(array($this, 'handle_exception'));
	}
	
	/**
	 * Définie le fichier de sauvegarde.
	 * @param string $filename Chemin du fichier de sauvegarde, s'il n'existe pas, il sera créé si possible.
	 */
	public function set_file($filename)
	{
		$dir = dirname($filename);
		if (file_exists($dir) == FALSE)
		{
			if (mkdir($dir,0755,TRUE) == FALSE)
			{
				return FALSE;
			}
		}
		$this->_file = $filename;
		return TRUE;
	}
	
	/**
	 * Ajoute une information supplémentaire dans la sauvegarde.
	 * @param string $name Nom de la valeur.
	 * @param string $value Valeur du paramètre.
	 */
	public function add_data($name,$value)
	{
		$this->_data[$name] = $value;
	}

	/**
	 * Active ou désactive la sauvegarde des erreurs et exceptions dans un fichier.
	 * @param boolean $bool Si TRUE, la sauvegarde sera activée.
	 */
	public function active_save($bool=TRUE)
	{
		$this->_actived_save = $bool;
	}
	
	/**
	 * Gère les erreurs.
	 * @param int $errno Numéro de l'erreur.
	 * @param string $errstr Message de l'erreur.
	 * @param string $errfile Fichier où a eu lieu l'erreur.
	 * @param int $errline Numéro de la ligne.
	 * @return boolean
	 */
	public function handle_error($errno,$errstr,$errfile,$errline)
	{
		if ($this->is_enabled())
		{
			switch($errno)
			{
				case E_USER_ERROR : 	$type = 'Fatal'; break;
				case E_USER_WARNING : 	$type = 'Erreur'; break;
				case E_USER_NOTICE : 	$type = 'Warning'; break;
				case E_ERROR : 			$type = 'Fatal'; break;
				case E_WARNING : 		$type = 'Erreur'; break;
				case E_NOTICE : 		$type = 'Warning'; break;
				default : 				$type = 'Inconnue'; break;
			}
			$e = array('type'=>$type,'string'=>$errstr,'file'=>$errfile,'line'=>$errline,'time'=>time());
			$this->_errors[] = $e;
			if ($this->_actived_save)
			{
				$this->save($e);
			}
			if ($this->_show)
			{
				echo '<div>'.$this->get_message($e).'</div>';
			}
		}
		return TRUE;
	}
	
	/**
	 * Gère les erreurs fatales.
	 * @return boolean
	 */
	public function handle_fatal()
	{
		if ($this->is_enabled())
		{
			$error = error_get_last();
			
			if($error !== NULL) 
			{
				$errno   = $error["type"];
				$errfile = $error["file"];
				$errline = $error["line"];
				$errstr  = $error["message"];
				switch($errno)
				{
					case E_USER_ERROR : 	$type = 'Fatal'; break;
					case E_USER_WARNING : 	$type = 'Erreur'; break;
					case E_USER_NOTICE : 	$type = 'Warning'; break;
					case E_ERROR : 			$type = 'Fatal'; break;
					case E_WARNING : 		$type = 'Erreur'; break;
					case E_NOTICE : 		$type = 'Warning'; break;
					default : 				$type = 'Inconnue'; break;
				}
				$e = array('type'=>$type,'string'=>$errstr,'file'=>$errfile,'line'=>$errline,'time'=>time());
				$this->_errors[] = $e;
				if ($this->_actived_save)
				{
					$this->save($e);
				}
				if ($this->_show)
				{
					echo '<div>'.$this->get_message($e).'</div>';
				}
			}
		}
		return TRUE;
	}

	/**
	 * Gère les exceptions.
	 * @param Exception $exc Exception à traiter.
	 * @return boolean
	 */
	public function handle_exception(Exception $exc)
	{
		if ($this->is_enabled())
		{
			$e = array('type'=>'Exception','string'=>$exc->getMessage(),'file'=>$exc->getFile(),'line'=>$exc->getLine(),'time'=>time());
			$this->_exceptions[] = $e;
			if ($this->_actived_save)
			{
				$this->save($e);
			}
			if ($this->_show)
			{
				echo '<div>'.$this->get_message($e).'</div>';
			}
		}	    
		return TRUE;
	}
	
	/**
	 * Retourne l'erreur ou l'exception sous forme d'une chaîne.
	 * @param array<string> $e Tableau d'information sur l'erreur ou l'exception.
	 * @param boolean $detail Si TRUE, toutes les informations seront rajoutées.
	 * @return string Message définissant l'erreur.
	 */
	private function get_message($e,$detail=FALSE)
	{
		$message = '';
		if ($detail)
		{
			$message .= '['.date('Y-m-d H:i:s',$e['time']).'] ['.$e['type'].']';
			foreach ($this->_data as $n => $v)
			{
				$message .= ' ['.$n.'="'.$v.'"]';
			}
			$message .=  ' : ';
		}
		$message .= $e['file'].':'.$e['line'].' '.$e['string'];
		return $message;
	}
	
	/**
	 * Retourne toutes les erreurs capturées.
	 * @return array Liste des informations sur les erreurs.
	 */
	public function get_all_errors()
	{
		return $this->_errors;
	}
	
	/**
	 * Retourne toutes les exceptions capturées.
	 * @return array Liste des informations sur les exceptions.
	 */
	public function get_all_exceptions()
	{
		return $this->_exceptions;
	}
	
	/**
	 * Sauvegarde les erreurs et exceptions.
	 * @param array<string> $e Liste des informatiosn sur l'erreur ou l'exception.
	 * @return boolean
	 */
	private function save($e)
	{
		if (($fp = fopen($this->_file,'a+')) == FALSE)
		{
			return FALSE;
		}
		$message = $this->get_message($e,TRUE);
		fputs($fp,$message."\r\n");
		fclose($fp);
		return TRUE;
	}
	
	/**
	 * Active l'affichage des erreurs.
	 */
	public function show()
	{
	    ini_set('display_errors', 1);
		$this->_show = TRUE;
	}
	
	/**
	 * Désactive l'affichage des erreurs.
	 */
	public function hide()
	{
	    ini_set('display_errors', 0);
		$this->_show = FALSE;
	}
	
	/**
	 * Active les fonctionnalités de la classe.
	 */
	public function enable()
	{
	    $this->_enable = TRUE;
	}
	
	/**
	 * Désactive les fonctionnalités de la classe.
	 */
	public function disable()
	{
	    $this->_enable = FALSE;
	}
	
	/**
	 * Vérifie si la classe est active.
	 * @return boolean
	 */
	public function is_enabled()
	{
	    return $this->_enable;
	}
}
?>