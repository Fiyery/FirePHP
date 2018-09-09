<?php
/**
 * Classe de gestion pour les messages informatifs de retour de la réponse.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 */
class ResponseAlert
{
    /**
	 * Constant qui définie le format d'un message.
	 * @var string
	 */
	const ALERT_INFO = 'alert_info';
	
	/**
	 * Constant qui définie le format d'un message.
	 * @var string
	 */
	const ALERT_SUCCESS = 'alert_success';
	
	/**
	 * Constant qui définie le format d'un message.
	 * @var string
	 */
	const ALERT_WARNING = 'alert_warning';
	
	/**
	 * Constant qui définie le format d'un message.
	 * @var string
	 */
	const ALERT_ERROR = 'alert_error';

    /**
     * Liste des messages de la réponse.
     * @var array
     */
    protected $_list = [];

    /**
     * Constructeur.
     * @param array $list Liste des messages.
     */
    public function __construct(array $list = [])
    {
        $this->_list = $list;
    }

    /**
     * Définie l'entête.
     * @param string $type
     * @param string $message
     */
    public function add(string $type, string $message)
    {
        $this->_list[] = [
            "type"      => $type,
            "message"   => $message
        ];
    }

    /**
     * Définie un message informatif de type erreur.
     * @param string $message
     */
    public function add_error(string $message)
    {
        $this->add(self::ALERT_ERROR, $message);
    }

    /**
     * Définie un message informatif de type attention.
     * @param string $message
     */
    public function add_warning(string $message)
    {
        $this->add(self::ALERT_WARNING, $message);
    }

    /**
     * Définie un message informatif de type succès.
     * @param string $message
     */
    public function add_success(string $message)
    {
        $this->add(self::ALERT_SUCCESS, $message);
    }

    /**
     * Définie un message informatif de type information.
     * @param string $message
     */
    public function add_info(string $message)
    {
        $this->add(self::ALERT_INFO, $message);
    }

    /**
     * Retourne la valeur de l'entête.q
     * @param int $index
     */
    public function get(int $index) : array
    {
        return (isset($this->_list[$index])) ? ($this->_list[$index]) : (NULL);
    }

    /**
     * Supprime tous les messages.
     */
    public function clean()
    {
        $this->_list = [];
    }

    /**
     * Retourne toutes les valeurs de l'entête.
     * @return string
     */
    public function lists() : array
    {
        return $this->_list;
    }
}
?>