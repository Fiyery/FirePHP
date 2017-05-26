<?php
/**
 * Gestionnaire de l'API RESTful.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses RestException
 * @uses Request
 * @uses Database
 * @uses Site
 */
class RestManager
{
    /**
     * Classe de connexion à la base de données.
     * @var Database
     */
    private $_base = NULL;

    /**
     * Classe de gestion des informations de la requête.
     * @var Request
     */
    private $_req;

    /**
     * Classe pour les codes retour HTTP.
     * @var Site
     */
    private $_site;

    /**
     * Constructeur.
     * @param Database $base Classe de connexion à la base de données.
     * @param Request $req Classe de gestion des informations de la requête.
     * @param Site $site Classe pour les codes retour HTTP.
     */
    public function __construct(Database $base, Request $req, Site $site)
    {
        $this->_base = $base;
        $this->_req = $req;
        $this->_site = $site;
    }

    /**
     * Gère la requête HTTP REST et retourne le résultat.
     * Liste des paramètres :
     *      filter[name] string : Condition de récupération des lignes.
     *      order[name] string : Trie selon le champ name avec la valeur 'ASC' ou 'DESC'.
     *      id string : Identifiant de la ressource.
     *      __ressource string : Nom de la table.
     *      __method string : Méthode HTTP à traiter : GET, POST, PUT, DELETE, PATCH.
     *      __foreign_table bool : Si 0, les informations des tables liées ne sont pas chargées. La valeur par défaut est 1.
     */
    public function handle()
    {
        if ($this->_req->__ressource === NULL)
        {
            throw new RestException(RestException::MISSING_NAME);
        }
        $return = NULL;
        // Si le paramètre __method est renseigné, il prime sur la véritable valeur de la méthode HTTP.
        $method = ($this->_req->__method !== NULL) ? ($this->_req->__method) : ($this->_req->method());
        switch (strtolower($method)) {
            case 'get':
                $return = $this->_handle_get();
                break;
            case 'post':
                $return = $this->_handle_post();
                break;
            case 'put':
                $return = $this->_handle_post();
                break;
            case 'delete':
                $return = $this->_handle_delete();
                break;
            default:
                throw new RestException(RestException::INVALID_METHOD);
        }
        return ($return !== NULL) ? (json_encode($return)) : (NULL);
    }

    /**
     * Handle GET Request.
     * @return mixed Request result.
     */
    private function _handle_get()
    {
        // Récupération des informations sur les clés étrangères.
        $foreign_keys = $this->_base->foreign_keys($this->_req->__ressource);
        $tmp = [];
        foreach ($foreign_keys as $k)
        {
            $tmp[$k['COLUMN_NAME']] = [
                'table' => $k['REFERENCED_TABLE_NAME'],
                'field' => $k['REFERENCED_COLUMN_NAME']
            ];
        }
        $foreign_keys = $tmp;

        // Récupération des champs.
        $fields = $this->_base->fields($this->_req->__ressource);
        if (is_array($fields) === FALSE)
        {
            $this->_site->status_code(400);
            throw new RestException(RestException::MISSING_NAME);
        }

        // Alias.
        $table_alias = md5($this->_req->__ressource);
        $has_alias = FALSE;

        $sql = 'SELECT * FROM `'.$this->_req->__ressource.'`';

        // Load foreign key.
        if ($this->_req->foreign_table === NULL || $this->_req->foreign_table === "1")
        {
            foreach ($fields as $f)
            {
                if (isset($foreign_keys[$f['Field']]))
                {
                    if ($has_alias === FALSE)
                    {
                        $has_alias = TRUE;
                        $sql .= ' '.$table_alias;
                    }
                    $foreign_table = $foreign_keys[$f['Field']]['table'];
                    $foreign_table_alias = $foreign_table.mt_rand();
                    $foreign_fields = $this->_base->fields($foreign_table);
                    if (is_array($foreign_fields) === FALSE)
                    {
                        throw new RestException(RestException::INVALID_FOREIGN_KEY);
                    }
                    $sql .= ' LEFT JOIN (SELECT ';
                    $select = [];
                    foreach ($foreign_fields as $ff)
                    {
                        $select[]= $ff['Field']." ".$f['Field'].'_'.$ff['Field'];
                    }
                    $sql .= implode(',', $select).' FROM `'.$foreign_table.'`) '.$foreign_table_alias.' ON '.$foreign_table_alias.'.'.$f['Field'].'_id = '.$table_alias.'.'.$f['Field'];
                }
            }
        }
        
        $sql = "
            SELECT * FROM
            (
                ".$sql."
            ) t
        ";

        $values_sql = [];
        $req_filter = (is_array($this->_req->filter)) ? ($this->_req->filter) : ([]);
        if ($this->_req->id !== NULL)
        {
            $req_filter['id'] = $this->_req->id;
        }
        if (count($req_filter) > 0)
        {
            $sql .= "
                WHERE
            ";
            $filter = [];
            foreach ($req_filter as $f => $v)
            {
                $filter[] = '`'.$f.'` = ?';
                $values_sql[] = $v;
            }
            $sql .= implode($filter, ' AND ');
        }

        // Order.
        if (is_array($this->_req->order))
        {
            $sql .= "
                ORDER BY
            ";
            $order = [];
            foreach ($this->_req->order as $f => $v)
            {
                switch (strtolower($v))
                {
                    case 'desc' :
                        $order[] = '`'.$f.'` DESC';
                        break;
                    default :
                        $order[] = '`'.$f.'` ASC';
                        break;
                }
            }
            $sql .= implode($order, ', ');
        }

        $result = $this->_base->query($sql, $values_sql);
        if ($result === TRUE)
        {
            $this->_site->status_code(200);
            return [];
        }

        if (is_array($result))
        {
            // Pour éviter le problème des noms de clé étrangère incluse dans d'autres noms.
            $foreign_keys = array_keys($foreign_keys);
            usort($foreign_keys, function($a, $b) {
                return strlen($a) <= strlen($b);
            });

            // Groupement des informations des clés étrangères.
            foreach ($foreign_keys as $k)
            {
                $len = strlen($k);
                foreach ($result as &$r)
                {
                    foreach ($r as $n => $v)
                    {
                        if (substr($n, 0, $len) === $k && $n !== $k && is_array($v) === FALSE)
                        {
                            if (is_array($r[$k]) === FALSE)
                            {
                                $r[$k] = [];
                            }
                            $r[$k][substr($n, $len+1)] = $v;
                            unset($r[$n]);
                        }
                    }
                }
            }

            $this->_site->status_code(200);
            return $result;
        }
        $this->_site->status_code(400);
        $error = $this->_base->error();
        throw new RestException(RestException::ERROR_SQL, '['.$error[0].'] '.$error[2]);
    }

    /**
     * Handle POST Request.
     * @return mixed Request result.
     */
    private function _handle_post()
    {
        $fields = $this->_base->fields($this->_req->__ressource);
        if ($fields === NULL)
        {
            $this->_site->status_code(400);
            throw new RestException(RestException::NOT_FOUND_TABLE);
        }

        // On vérifie s'il n'existe pas déjà le même idée pour le Code retour HTTP.$_COOKIE
        $this->_site->status_code(201);
        if ($this->_req->id)
        {
            $result = $this->_base->query("SELECT id FROM `".$this->_req->__ressource."` WHERE id = ?", [$this->_req->id]);
            if (is_array($result) && count($result) === 1)
            {
                $this->_site->status_code(204);
            }
        }

        $sql = "
            INSERT INTO `".$this->_req->__ressource.'`
            VALUES ('.implode(',', array_fill(0, count($fields), '?')).')
            ON DUPLICATE KEY UPDATE
            ';
        $updates = [];
        $values_sql = [];
        foreach ($fields as $f)
        {
            if ($f['Key'] !== 'PRI')
            {
                $updates[] = '`'.$f['Field'].'` = VALUES(`'.$f['Field'].'`)';
            }
            $default = $f['Default'];
            $f = $f['Field'];
            $values_sql[] = (isset($this->_req->$f)) ? ($this->_req->$f) : ($default);
        }
        $sql .= implode(',', $updates);

        $result = $this->_base->query($sql, $values_sql);
        if ($result === TRUE || is_array($result))
        {
            if ($this->_site->status_code() === 201)
            {
                header('Location: '.$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['PATH_INFO'].$this->_base->last_id().'/'); 
            }
            return NULL;
        }
        $this->_site->status_code(400);
        $error = $this->_base->error();
        throw new RestException(RestException::ERROR_SQL.' : ['.$error[0].'] '.$error[2]);
    }

    /**
     * Handle DELETE Request.
     * @return mixed Request result.
     */
    private function _handle_delete()
    {
        if ($this->_req->id === NULL)
        {
            $this->_site->status_code(400);
            throw new RestException(RestException::MISSING_INSTANCE_ID);
        }

        // Pour savoir si l'entité existe ou non pour le code HTTP de retour
        $result = $this->_base->query("SELECT id FROM `".$this->_req->__ressource."` WHERE id = ?", [$this->_req->id]);
        if (is_array($result) && count($result) === 1)
        {
            $this->_site->status_code(200);
        }
        else
        {
            $this->_site->status_code(404);
            return NULL;
        }

        $sql = "
            DELETE FROM `".$this->_req->__ressource."`
            WHERE id = ?;
        ";
        $result = $this->_base->query($sql, [$this->_req->id]);
        if ($result === TRUE || is_array($result))
        {
            $this->_site->status_code(200);
            return NULL;
        }
        $this->_site->status_code(400);
        $error = $result->errorInfo();
        throw new RestException(RestException::ERROR_SQL, '['.$error[0].'] '.$error[2]);
    }
}
?>