<?php
abstract class Logger implements Observer
{
    /**
     * Constructeur.
     */
    public function __contruct()
    {

    }

    /**
     * Récupère une information.
     * @param string $text Contenu de l'information.
     * @param string $name Titre de l'information.
     * @param string $type Catégorie de l'information permettant le trie.
     */
    public abstract function log($text, $name = NULL, $type = NULL);

    /**
     * Sauvegarde les informations récupérées.
     */
    public abstract function save();
}
?>