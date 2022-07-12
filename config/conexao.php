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
            return new \PDO("mysql:dbname={$DB_NAME};host={$HOST};charset=utf8", $USER, $PASS);
        }
        catch (\Exception $e) {
            echo "Erro ao conectar com o banco de dados! " . $e;
            return false;
        }
    }
}
