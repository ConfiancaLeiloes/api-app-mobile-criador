<?php
use Psr\Http\Message\ServerRequestInterface;
class PlantelModel
{
     /**
     * Método index
     * @author Iago Oliveira <iagooliveira09@outlook.com>
     * @return 
     */
    public function index()
    {   
        return json_encode(["mensagem" => "Hello confianca"]);
    }
}
