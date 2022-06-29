<?php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ClienteController 
{
    private $cliente;
    public function __construct($cliente)
    {
       $this->cliente = new ClienteModel();
    }

    public function teste1() {
        exit("Rodando ::teste1()");
    }

    public function teste2() {
        exit("Rodando ::teste2()");
    }


    public function teste3() {
        exit("Rodando ::teste2()");
    }



}
