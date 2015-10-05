<?php
interface DataSource
{
    public function query(String $query);
    
    public function insert(Array $list);
    
    public function delete(Array $key);
    
    public function load(Array $key);
    
    public function search(Array $fields);
}

?>