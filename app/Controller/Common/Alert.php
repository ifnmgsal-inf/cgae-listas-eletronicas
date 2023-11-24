<?php

namespace App\Controller\Common;

use \App\Utils\View;

/**
 * Controlador da view de alerta
 */
class Alert
{
    /**
     * Retorna uma mensagem de sucesso
     * @param string $message Texto da mensagem
     * @return string View renderizada
     */
    public static function getSuccess($message)
    {
        return View::render("common/alert/status", [
            'tipo' => "success",
            "mensagem" => $message
        ]);
    }

    /**
     * Retorna uma mensagem de erro
     * @param string $message Texto da mensagem
     * @return string View renderizada
     */
    public static function getError($message)
    {
        return View::render("common/alert/status", [
            'tipo' => "danger",
            "mensagem" => $message
        ]);
    }
}

?>