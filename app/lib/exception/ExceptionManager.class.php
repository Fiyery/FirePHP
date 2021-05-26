<?php
namespace FirePHP\Exception;

use Error;
use Throwable;
use ReflectionClass;
use Exception as PHPException;
/**
 * ExceptionManager est la classe de gestion des erreurs et exceptions PHP personnalisée ou standard.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 */
class ExceptionManager 
{	
	/**
	 * Définie si les erreurs devront être affichées.
	 * @var bool
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
	 * @var bool
	 */
	private $_actived_save = FALSE;
	
	/**
	 * Informations supplémentaires à rajouter à l'erreur.
	 * @var string[]
	 */
	private $_data;
	
	/**
	 * Etat courant de l'activité de la classe.
	 * @var bool
	 */
	private $_enable = TRUE;

	/**
	 * Constructeur.
	 */
	public function __construct()
	{
		$this->_errors = [];
		$this->_exceptions = [];
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
	}
	
	/**
	 * Active la gestion des exceptions de la classe.
	 */
	public function active_exception()
	{
		set_exception_handler([$this, 'handle_exception']);
	}
	
	/**
	 * Définie le fichier de sauvegarde.
	 * @param string $filename Chemin du fichier de sauvegarde, s'il n'existe pas, il sera créé si possible.
	 * @param bool
	 */
	public function set_file(string $filename) : bool
	{
		$dir = dirname($filename);
		if (file_exists($dir) === FALSE)
		{
			if (mkdir($dir, 0755, TRUE) === FALSE)
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
	public function add_data(string $name, string $value)
	{
		$this->_data[$name] = $value;
	}

	/**
	 * Active ou désactive la sauvegarde des erreurs et exceptions dans un fichier.
	 * @param bool $bool Si TRUE, la sauvegarde sera activée.
	 */
	public function active_save(bool $bool = TRUE) 
	{
		$this->_actived_save = $bool;
	}
	
	/**
	 * Gère les erreurs.
	 * @param int $errno Numéro de l'erreur.
	 * @param string $errstr Message de l'erreur.
	 * @param string $errfile Fichier où a eu lieu l'erreur.
	 * @param int $errline Numéro de la ligne.
	 * @return bool
	 */
	public function handle_error(int $errno, string $errstr, string $errfile, int $errline) : bool
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
			$e = [
				'type'		=> $type,
				'string'	=> $errstr,
				'file'		=> $errfile,
				'line'		=> $errline,
				'time'		=> time()
			];
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
	 * Gère les objet héritant de l'interface throwable.
	 * @return bool
	 */
	public function handle_throwable(Throwable $t)
	{
		// On cherche si le Throwable est une erreur ou une exception.
		$r = new ReflectionClass($t);
		$name = $r->getName();
		while ($name !== 'Exception' && $name !== 'Error')
		{
			$r = new ReflectionClass($r->getParentClass()->name);
			$name = $r->getName();
		}
		if ($name === 'Exception')
		{
			$this->handle_exception($t);
		}
		if ($name === 'Error')
		{
			$this->handle_fatal_error($t);
		}
	}
	
	/**
	 * Gère les erreurs fatales.
	 * @return bool
	 */
	public function handle_fatal_error(Error $e) : bool
	{
		if ($this->is_enabled())
		{
			$e = [
				'type'		=> 'Fatal Error',
				'string'	=> $e->getMessage(),
				'file'		=> $e->getFile(),
				'line'		=> $e->getLine(),
				'time'		=> time()
			];
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
	 * Gère les exceptions.
	 * @param Exception $e Exception à traiter.
	 * @return bool
	 */
	public function handle_exception(PHPException $e) : bool
	{
		if ($this->is_enabled())
		{
			$e = [
				'type'		=> 'Exception',
				'string'	=> $e->getMessage(),
				'file'		=> $e->getFile(),
				'line'		=> $e->getLine(),
				'time'		=> time()
			];
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
	 * @param string[] $e Tableau d'information sur l'erreur ou l'exception.
	 * @param bool $detail Si TRUE, toutes les informations seront rajoutées.
	 * @return string Message définissant l'erreur.
	 */
	private function get_message(array $e, bool $detail = FALSE) : string
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
	public function get_all_errors() : array
	{
		return $this->_errors;
	}
	
	/**
	 * Retourne toutes les exceptions capturées.
	 * @return array Liste des informations sur les exceptions.
	 */
	public function get_all_exceptions() : array
	{
		return $this->_exceptions;
	}
	
	/**
	 * Sauvegarde les erreurs et exceptions.
	 * @param string[] $e Liste des informatiosn sur l'erreur ou l'exception.
	 * @return bool
	 */
	private function save(array $e) : bool 
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
	 * @return bool
	 */
	public function is_enabled() : bool
	{
	    return $this->_enable;
	}
}
?>