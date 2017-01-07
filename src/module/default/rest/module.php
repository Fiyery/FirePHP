<?php
class RestModule extends Module
{
    public function action_index()
    {
        try
        {
            $rest = new RestManager($this->base, $this->req, $this->site);
            echo $rest->handle();
        }
        catch (Exception $e)
        {
            Debug::show($e);
        }
        exit();
    }
}
?>