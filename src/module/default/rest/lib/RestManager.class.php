<?php
/**
 * Gestionnaire de l'API RESTful.
 * @author Yoann Chaumin <yoann.chaumin@gmail.com>
 * @uses RestException
 */
class RestManager
{
    /**
     * Classe de connexion à la base de données.
     * @var Base
     */
    private $_base = NULL;

    /**
     * Classe de gestion des informations de la requête.
     * @var Request
     */
    private $_req;

    /**
     * Constructeur.
     * @param Base $base Classe de connexion à la base de données.
     * @param Request $req Classe de gestion des informations de la requête.
     */
    public function __construct(Base $base, Request $req)
    {
        $this->_base = $base;
        $this->_req = $req;
    }


    /**
     * Gère la requête HTTP REST et retourne le résultat.
     * Liste des paramètres :
     *      filter[name] string : Condition de récupération des lignes.
     *      order[name] string : Trie selon le champ name avec la valeur 'ASC' ou 'DESC'.
     *      __method string : Méthode HTTP à traiter : GET, POST, PUT, DELETE, PATCH.
     *      __ressource string : Nom de la table.
     *      __id string : Identifiant de la ressource.
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
        $method = ($this->_req->method() !== NULL) ? ($this->_req->__method) : ($this->_req->method());
        switch (strtolower($this->_req->method())) {
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
            case 'patch':
                $return = $this->_handle_patch();
                break;
            default:
                throw new RestException(RestException::INVALID_METHOD);
        }
        return json_encode($return);
    }

    /**
     * Handle GET Request.
     * @return mixed Request result.
     */
    private function _handle_get()
    {
        $fields = $this->_base->fields($this->_req->__ressource);

        if (is_array($fields) === FALSE)
        {
            throw new RestException(RestException::MISSING_NAME);
        }

        // Alias.
        $table_alias = md5($this->_req->__ressource);
        $has_alias = FALSE;

        $sql = 'SELECT * FROM `'.$this->_req->__ressource.'`';

        // Load foreign key.
        $foreign_keys = [];
        if ($this->_req->foreign_table === NULL || $this->_req->foreign_table === "1")
        {
            foreach ($fields as $f)
            {
                if ($f['Key'] === 'MUL' && substr($f['Field'], 0, 3) === 'id_')
                {
                    $foreign_keys[] = $f['Field'];
                    if ($has_alias === FALSE)
                    {
                        $has_alias = TRUE;
                        $sql .= ' '.$table_alias;
                    }
                    $foreign_table = substr($f['Field'], 3);
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
                    $sql .= implode(',', $select).' FROM `'.$foreign_table.'`) '.$foreign_table.' ON '.$foreign_table.'.'.$f['Field'].'_id = '.$table_alias.'.'.$f['Field'];
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
            return [];
        }
        if (is_array($result))
        {
            // Pour éviter le problème des noms de clé étrangère incluse dans d'autres noms.
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
                        if (substr($n, 0, $len) === $k && $n !== $k)
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
            return $result;
        }
        $error = $this->_base->error();
        throw new RestException(RestException::ERROR_SQL, '['.$error[0].'] '.$error[2]);
    }

    /**
     * Handle POST Request.
     * @return mixed Request result.
     */
    private function _handle_post()
    {
        $fields = $this->_get_fields($this->_request['name']);
        if ($fields === NULL)
        {
            throw new RestException(RestException::NOT_FOUND_TABLE);
        }
        $sql = "
            INSERT INTO `".$this->_request['name'].'`
            VALUES ('.implode(',', array_fill(0, count($fields), '?')).')
            ON DUPLICATE KEY UPDATE
            ';
        $values = [];
        $prepare = [];
        foreach ($fields as $f)
        {
            if ($f['Key'] !== 'PRI')
            {
                $values[] = '`'.$f['Field'].'` = VALUES(`'.$f['Field'].'`)';
            }
            $f = $f['Field'];
            $prepare[] = (isset($this->_request['instance'][$f])) ? ($this->_request['instance'][$f]) : (NULL);
        }
        $sql .= implode(',', $values);

        $result = $this->_pdo->prepare($sql);
        if ($result->execute($prepare))
        {
            $this->_request['instance']['id'] = $this->_pdo->lastInsertId();
            return $this->_request['instance'];
        }
        $error = $result->errorInfo();
        throw new RestException(RestException::ERROR_SQL, '['.$error[0].'] '.$error[2]);
    }

    /**
     * Handle DELETE Request.
     * @return mixed Request result.
     */
    private function _handle_delete()
    {
        if (isset($this->_request['instance']['id']) === FALSE)
        {
            throw new RestException(RestException::MISSING_INSTANCE_ID);
        }
        $sql = "
            DELETE FROM `".$this->_request['name'].'`
            WHERE id = ?;
            ';
        $result = $this->_pdo->prepare($sql);
        if ($result->execute([$this->_request['instance']['id']]))
        {
            return TRUE;
        }
        $error = $result->errorInfo();
        throw new RestException(RestException::ERROR_SQL, '['.$error[0].'] '.$error[2]);
    }
}
?>