<?php
interface DataSource
{
    public function query(string $query);
    
    public function insert(array $list);
    
    public function delete(array $key);
    
    public function load(array $key);
    
    public function search(array $fields);
}

?>