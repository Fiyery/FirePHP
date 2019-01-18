<?php
class RestModule extends Module
{
    public function action_index()
    {
        try
        {
            $rest = new RestManager($this->database, $this->request, $this->response);
            echo $rest->handle();
        }
        catch (RestException $e)
        {
            echo json_encode([
                'error' => ($e->detail() !== NULL) ? ($e->getMessage().' : '.$e->detail()) : ($e->getMessage())
            ]);
        }
        catch (Exception $e)
        {
            echo json_encode([
                'error' => $e->getMessage()
            ]);
        }
        exit();
    }
}
?>