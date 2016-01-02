<?php
/**
 * Mail gère l'envoie de mail.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @copyright 2011-2015 Yoann Chaumin
 * @uses Singleton
 */
class Mail extends Singleton
{
	Use Enable; 
	
	/**
	 * Instance de singleton.
	 * @var Mail
	 */
    protected static $_instance = NULL;
	
	/**
	 * Liste des adresses mail des destinataires.
	 * @var array<string>
	 */
	private $_receivers;
	
	/**
	 * Adresse mail de l'émetteur.
	 * @var string
	 */
	private $_sender_mail;
	
	/**
	 * Nom affiché de l'émetteur.
	 * @var string
	 */
	private $_sender_name;
	
	/**
	 * Sujet du mail
	 * @var string
	 */
	private $_subject;
	
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
	 * Constructeur de la classe.
	 */
	protected function __construct()
	{
		$this->reset();
	}
	
	/**
	 * Ajoute un destinataire au mail.
	 * @param string $mail Adresse mail du destinataire à ajouter.
	 * @return array<string>|boolean Retourne l'ensemble des destinataires du mail si l'ajout réussi sinon FALSE.
	 */
	public function add_receiver($mail)
	{
		if (is_string($mail))
		{
			$this->_receivers[] = $mail;
			return $this->_receivers;
		}
		else
		{
			return FALSE;
		}
			
	}

	/**
	 * Supprime un destinataire au mail.
	 * @param string $mail Adresse mail du destinataire à supprimer.
	 * @return array<string>|boolean Retourne l'ensemble des destinataires du mail si la suppression réussie sinon FALSE.
	 */
	public function drop_receiver($mail)
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
			return $this->_receivers;
		}
		else
		{
			return FALSE;
		}
			
	}
	
	/**
	 * Retourne le corps du message.
	 * @return string Contenu du corps du mail.
	 */
	public function get_body()
	{
		return $this->_body;
	}
	
	/**
	 * Retourne l'entête du message.
	 * @return string Contenu de l'entête du mail.
	 */
	public function get_head()
	{
		return $this->_head;
	}
	
	/**
	 * Retourne le pied du message.
	 * @return string Contenu du pied du mail.
	 */
	public function get_foot()
	{
		return $this->_foot;
	}
	
	/**
	 * Retourne l'expédidteur du mail.
	 * @return string Adresse mail de l'expéditeur du mail.
	 */
	public function get_sender()
	{
		return $this->_sender;
	}
	
	/**
	 * Retourne l'encodage du mail.
	 * @return string Encodage du mail.
	 */
	public function get_charset()
	{
		return $this->_charset;
	}
	
	/**
	 * Retourne le sujet du message.
	 * @return string Sujet du message.
	 */
	public function get_subject()
	{
		return $this->_subject;
	}
	
	/**
	 * Définie le corps du message.
	 * @param $text string ontenu du corps du message.
	 * @return Mail Instance du mail.
	 */
	public function set_body($text)
	{
		$this->_body = $text;
		return $this;
	}
	
	/**
	 * Définie l'entête du message.
	 * @param $text string Contenu de l'entête du message.
	 * @return Mail Instance du mail.
	 */
	public function set_head($text)
	{
		$this->_head = $text;
		return $this;
	}
	
	/**
	 * Définie le pied du message.
	 * @param $text string Contenu du pied du message.
	 * @return Mail Instance du mail.
	 */
	public function set_foot($text)
	{
		$this->_foot = $text;
		return $this;
	}
	
	/**
	 * Définie l'adresse de l'expéditeur du message.
	 * @param $name string Désignation de l'expéditeur du message.
	 * @param $mail string Adresse mail de l'expéditeur du message.
	 * @return Mail Instance du mail.
	 */
	public function set_sender($name,$mail)
	{
		$this->_sender_name = $name;
		$this->_sender_mail = $mail;
		return $this;
	}
	
	/**
	 * Définie l'adresse de copie cachée du message.
	 * @param $mail Adresse de copie caché du mail.
	 * @return Mail Instance du mail.
	 */
	public function set_hide_copy($mail)
	{
		$this->_bbc = $mail;
		return $this;
	}
	
	/**
	 * Définie l'encodage du mail.
	 * @param $charset string Encodage du message.
	 * @return Mail Instance du mail.
	 */
	public function set_charset($charset)
	{
		$this->_charset = $charset;
		return $this;
	}
	
	/**
	 * Définie le sujet du message.
	 * @param $text string Sujet du message.
	 * @return Mail Instance du mail.
	 */
	public function set_subject($text)
	{
		$this->_subject = $text;
		return $this;
	}
	
	/**
	 * Génère le contenu du message.
	 * @return string Contenu du message.
	 */
	public function generate()
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
	 * @return boolean TRUE si tous les mails ont bien été envoyés ou que la classe est inactive sinon FALSE.
	 */
	public function send()
	{
		if ($this->is_enabled() == FALSE)
		{
		    return TRUE;
		}
	    if (!is_array($this->_receivers) || count($this->_receivers) == 0)
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
       	if (count($this->_sender) == 1)
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
	 * Réinitialise les paramètres du mail.
	 */
	public function reset()
	{
		$this->_subject = '';
		$this->_head = '';
		$this->_body = '';
		$this->_foot = '';
		$this->_charset = 'UTF-8';
		$this->_receivers = array();
		$this->_sender_mail = '';
		$this->_sender_name = '';
	}
}
?>