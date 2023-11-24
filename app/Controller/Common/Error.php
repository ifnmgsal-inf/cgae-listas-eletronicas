<?php

namespace App\Controller\Common;

use App\Utils\View;

/**
 * Controlador da página de erro
 */
class Error
{
    /**
     * Entrypoint GET da rota
     * @param \Exception $error Objeto da exceção
     * @return string View renderizada
     */
    public static function getView($error)
    {
        $content = View::render("error/error_".$error->getCode());

        $content = empty($content) ? View::render("error/error_500") : $content;

        return View::render("error/base/page", [
            "title" => "Error",
            "header" => self::getHeader(),
            "content" => $content,
            "footer" => self::getFooter(),
            "code" => $error->getCode(),
            "message" => $error->getMessage()
        ]);
    }

    /**
     * Retorna o header padrão das página de erro
     * @return string
     */
    private static function getHeader()
    {
        return View::render("error/base/header");
    }

    /**
     * Retorna o footer padrão das páginas de erro
     * @return string
     */
    private static function getFooter()
    {
        return View::render("error/base/footer");
    }
}