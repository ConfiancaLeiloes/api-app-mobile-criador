<?php

class ConexaoModel 
{
   static public function conectar()
   {
        $HOST = 'confiancacriador.digital';

        # DADOS DO BANCO DE PRODUÇÃO
        $DB_NAME = 'gc_confianca_criador';
        $USER = 'gc_criador';
        $PASS = 'YDs-p(9Nr$%3';        

        # DADOS DO BANCO DE TESTES
        if ( LOCALHOST ) {   
            $DB_NAME = 'gc_confianca_criador_dev';
            $USER = 'gc_user_dev';
            $PASS = 'Xou93FTtDlUZ';
        }

        try {
            return new \PDO("mysql:dbname={$DB_NAME};host={$HOST};charset=utf8", $USER, $PASS);
        }
        catch (\Exception $e) {
            echo "Erro ao conectar com o banco de dados! " . $e;
            return false;
        }
    }
}
