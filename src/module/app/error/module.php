<?php
class ErrorModule extends Module
{
    public function action_404()
    {

    }

    public function action_500()
    {
        if (isset($this->req->error_msg))
        {
            $this->tpl->assign('error_msg', $this->req->error_msg);
        }
    }
}
?>