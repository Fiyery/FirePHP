<?php
class Erreur extends Module
{
    public function action_index()
    {
        if (isset($this->req->error_msg))
        {
            $this->tpl->assign('error_msg', $this->req->error_msg);
        }

        $u = new User();
    }
}
?>