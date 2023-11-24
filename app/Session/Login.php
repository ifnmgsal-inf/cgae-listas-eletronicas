<?php

namespace App\Session;

use App\Model\Entity;

class Login 
{
    /**
     * Iniciliza a sessão
     */
    public static function init()
    {
        if (session_status() != PHP_SESSION_ACTIVE)
        {
            session_start();
        }
    }

    /**
     * Cria o login de usuário
     * @param mixed $ob Objeto do usuário a ser logado
     * @return bool
     */
    public static function login($ob)
    {
        // INICIALIZA A SESSÃO
        self::init();

        // VERIFICA O TIPO DE USUÁRIO QUE ESTÁ TENTANDO LOGAR
        if ($ob instanceof Entity\Aluno)
        {
            $_SESSION['user'] = [
                'usuario' => [
                    "id" => $ob->id,
                    "nome" => $ob->nome,
                    "email" => $ob->email,
                    "type" => "student"
                ]
            ];
        }
        
        else if ($ob instanceof Entity\Assistente)
        {
            $_SESSION['user'] = [
                'usuario' => [
                    "id" => $ob->id,
                    "nome" => $ob->nome,
                    "email" => $ob->email,
                    "type" => "assistant"
                ]
            ];
        }

        else
        {
            throw new \Exception("user not valid", 500);
        }

        return true;
    }

    /**
     * Desconecta o usuário
     */
    public static function logout()
    {
        // INICIALIZA A SESSÃO
        self::init();

        // DESTRÓI A SESSÃO
        unset($_SESSION['user']['usuario']);
        
        // DESTRÓI OS COOKIES DE LOGIN
        setcookie('user', null, time() - 100);
        setcookie('id', null, time() - 100);
        setcookie('nome', null, time() - 100);
        setcookie('email', null, time() - 100);

        return true;
    }
}

?>