<?php

namespace App\Utils;

class Environment
{
    /**
     * Carrega as variáveis de ambiente do projeto
     * @param string $dir Diretório do arquivo .env
     */
    public static function load($dir)
    {
        // VERIFICA SE O ARQUIVO .env EXISTE
        if (!file_exists($dir."/.env"))
        {
            return;
        }

        // LÊ O CONTEÚDO DO ARQUIVO .env
        $lines = file($dir."/.env");

        foreach ($lines as $line)
        {
            // ADICIONA AS VARIÁVEIS DE AMBIENTE AO SERVIDOR
            putenv(trim($line));
        }
    }
}