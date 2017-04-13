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
            echo json_encode([
                'error' => ($e->detail() !== NULL) ? ($e->getMessage().' : '.$e->detail()) : ($e->getMessage())
            ]);
        }
        exit();
    }
}
?>