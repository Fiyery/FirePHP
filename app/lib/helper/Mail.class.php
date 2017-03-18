<?php
/**
 * Mail gère l'envoie de mail.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 */
class Mail 
{
	/**
	 * Liste des adresses mail des destinataires.
	 * @var string[]
	 */
	private $_receivers = [];
	
	/**
	 * Adresse mail de l'émetteur.
	 * @var string
	 */
	private $_sender_mail = NULL;
	
	/**
	 * Nom affiché de l'émetteur.
	 * @var string
	 */
	private $_sender_name = NULL;
	
	/**
	 * Sujet du mail
	 * @var string
	 */
	private $_subject = NULL;
	
	/**
	 * Entête du mail.
	 * @var string
	 */
	private $_head = NULL;
	
	/**
	 * Corps du mail.
	 * @var string
	 */
	private $_body = NULL;
	
	/**
	 * Pied du mail.
	 * @var string
	 */
	private $_foot = NULL;
	
	/**
	 * Copie caché du mail.
	 * @var string
	 */
	private $_bcc = NULL;
	
	/**
	 * Encodage du mail. Par défaut, sa valeur est UTF-8.
	 * @var string
	 */
	private $_charset = NULL;

	/**
	 * Etat courant de l'activité de la classe.
	 * @var boolean 
	 */
	private $_enable = TRUE;
	
	/**
	 * Constructeur de la classe.
	 */
	protected function __construct()
	{
		$this->reset();
	}
	
	/**
	 * Ajoute un destinataire au mail.
	 * @param string $mail Adresse mail du destinataire à ajouter.
	 * @return string[] Retourne l'ensemble des destinataires du mail.
	 */
	public function add_receiver(string $mail) : array
	{
		if (is_string($mail))
		{
			$this->_receivers[] = $mail;
			return $this->_receivers;
		}
		return $this->_receivers;	
	}

	/**
	 * Supprime un destinataire au mail.
	 * @param string $mail Adresse mail du destinataire à supprimer.
	 * @return string[] Retourne l'ensemble des destinataires du mail.
	 */
	public function drop_receiver(string $mail) : array
	{
		if (is_string($mail))
		{
			$tmp = array();
			$max = count($this->_receivers);
			for($i=0;$i<$max;$i++)
			{
				if ($this->_receivers[$i] != $mail)
				{
					$tmp[] = $this->_receivers[$i];
				}
					
			}
			$this->_receivers = $tmp;
		}
		return $this->_receivers;	
	}
	
	/**
	 * Définie et retourne le corps du message.
	 * @param $value string ontenu du corps du message.
	 * @return string
	 */
	public function body(string $value = NULL)
	{
		if ($value !== NULL)
		{
			$this->_body = $value;
		}
		return $this->_body;
	}
	
	/**
	 * Définie et retourne l'entête du message.
	 * @param $value string Contenu de l'entête du message.
	 * @return string
	 */
	public function head(string $value = NULL)
	{
		if ($value !== NULL)
		{
			$this->_head = $value;
		}
		return $this->_head;
	}
	
	/**
	 * Définie et retourne le pied du message.
	 * @param $value string Contenu du pied du message.
	 * @return string
	 */
	public function foot(string $value = NULL)
	{
		if ($value !== NULL)
		{
			$this->_foot = $value;
		}
		return $this->_foot;
	}
	
	/**
	 * Définie et retourne l'adresse de l'expéditeur du message.
	 * @param $mail string Adresse mail de l'expéditeur du message.
	 * @param $name string Désignation de l'expéditeur du message.
	 * @return string
	 */
	public function sender(string $mail = NULL, string $name = NULL)
	{
		if ($mail !== NULL)
		{
			$this->_sender_name = $name;
			$this->_sender_mail = $mail;
		}
		return $this->_sender_mail;
	}
	
	/**
	 * Définie et retourne l'adresse de copie cachée du message.
	 * @param $value Adresse de copie caché du mail.
	 * @return string
	 */
	public function bcc(string $value = NULL)
	{
		if ($value !== NULL)
		{
			$this->_bcc = $value;
		}
		return $this->_bcc;
	}
	
	/**
	 * Définie et retourne l'encodage du mail.
	 * @param $value string Encodage du message.
	 * @return string
	 */
	public function charset(string $value = NULL)
	{
		if ($value !== NULL)
		{
			$this->_charset = $value;
		}
		return $this->_charset;
	}
	
	/**
	 * Définie et retourne le sujet du message.
	 * @param $value string Sujet du message.
	 * @return string
	 */
	public function subject(string $value = NULL)
	{
		if ($value !== NULL)
		{
			$this->_suject = $value;
		}
		return $this->_suject;
	}
	
	/**
	 * Génère le contenu du message.
	 * @return string Contenu du message.
	 */
	public function generate() : string
	{
		$content = '';
		if (empty($this->_head) == FALSE);
		{
			$content .= $this->_head;
		}
		if (empty($this->_body) == FALSE);
		{
			$content .= $this->_body;
		}
		if (empty($this->_foot) == FALSE);
		{
			$content .= $this->_foot; 
		}
		return $content;
	}
	
	/**
	 * Envoie le mail.
	 * @return bool TRUE si tous les mails ont bien été envoyés ou que la classe est inactive sinon FALSE.
	 */
	public function send() : bool
	{
		if ($this->is_enabled() === FALSE)
		{
		    return TRUE;
		}
	    if (count($this->_receivers) === 0)
        {
        	return FALSE;
        }
		$entete  = 'MIME-Version: 1.0' . "\n"; // Version MIME.
        $entete .= 'Content-type: text/html; charset='.$this->_charset.'\''."\n";
        $entete .= 'Reply-To: '.$this->_sender."\n"; // Mail de reponse.
        $entete .= 'From: "'.$this->_sender_name.'" <'.$this->_sender_mail.'>'."\n"; // Expediteur.        
        
        $entete .= 'Bcc: '.$this->_bcc."\n"; // Copie cachée Bcc.
        $message = $this->generate();
        $subject = $this->_subject;
       	if (count($this->_receivers) === 1)
        {
        	$entete .= 'Delivered-to: '.$this->_receivers[0]."\n\n"; // Destinataire.
        	return mail($this->_receivers[0], $subject, $message, $entete);
        }
        else 
        {	
        	$return = TRUE;      
        	foreach ($this->_receivers as $receiver)
        	{
        		$delived = 'Delivered-to: '. $receiver."\n\n"; // Destinataire.
        		$return = $return && mail($receiver, $subject, $message, $entete);
        	}
        	return $return;
        }	
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
	public function is_enabled() : bool
	{
		return $this->_enable;
	}
	
	/**
	 * Réinitialise les paramètres du mail.
	 */
	public function reset()
	{
		$this->_subject = '';
		$this->_head = '';
		$this->_body = '';
		$this->_foot = '';
		$this->_charset = 'UTF-8';
		$this->_receivers = [];
	}
}
?>