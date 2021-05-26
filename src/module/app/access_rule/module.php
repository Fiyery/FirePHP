<?php
use FirePHP\Controller\Module; 
class AccessRuleModule extends Module
{
    /**
     * Met à jour les règles de gestions des droits.
     * @return bool
     */
    public function run()
    {
        // Récupération des règles d'accès à partir de la base de données.
        
        // $rules = Access_Rule::search();
        // foreach ($rules as $r)
        // {
        //     $this->access->add_rule($r->id_group, $r->module, $r->action);
        // }
    }
}
?>
