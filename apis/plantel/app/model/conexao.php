<?php

class conexao 
{
   
   static public function conectar()
    {
        $DB_NAME = "";
        $HOST    = "";
        $USER    = "";
        $PASS    = "";
        try {
            $pdo = new \PDO("mysql:dbname=$DB_NAME;host=$HOST;charset=utf8", "$USER", "$PASS");
            return $pdo;
        }
        catch (\Exception $e) {
            echo "Erro ao conectar com o banco de dados! " . $e;
            return false;
        }
    }
}
