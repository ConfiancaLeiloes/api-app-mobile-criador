<?php

class ConexaoModel 
{
   
   static public function conectar()
    {
        $DB_NAME = "gc_confianca_criador";
        $HOST    = "confiancacriador.digital";
        $USER    = "gc_criador";
        $PASS    = "YDs-p(9Nr$%3";
        try {
            $pdo = new \PDO("mysql:dbname=gc_confianca_criador;host=confiancacriador.digital;charset=utf8", "gc_criador", "YDs-p(9Nr$%3");
            return $pdo;
        }
        catch (\Exception $e) {
            echo "Erro ao conectar com o banco de dados! " . $e;
            return false;
        }
    }
}
