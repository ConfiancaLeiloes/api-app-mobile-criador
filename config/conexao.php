<?php

class ConexaoModel 
{
   
   static public function conectar()
    {
        $DB_NAME = 'gc_confianca_criador_dev';
        $HOST    = 'confiancacriador.digital';
        $USER    = 'gc_user_dev';
        $PASS    = 'Xou93FTtDlUZ';
        try {
            return new \PDO("mysql:dbname={$DB_NAME};host={$HOST};charset=utf8", $USER, $PASS);
        }
        catch (\Exception $e) {
            echo "Erro ao conectar com o banco de dados! " . $e;
            return false;
        }
    }
}
