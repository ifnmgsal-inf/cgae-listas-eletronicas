<?php

namespace App\Controller\Student;

use \App\Utils\View;

/**
 * Controlador da página base (aluno)
 */
class Page
{
    /**
     * Define os módulos disponíveis e define se estão ativos ou não
     * @var array
     */
    private static $modules = [
        "home" => false,
        "listas" => false,
        "assinaturas" => false,
        "perfil" => false
    ];

    /**
     * Retorna o conteúdo da página base
     * @param string $title Título da página
     * @param string $content Conteúdo da página
     * @param bool $renderNavbar Define se a navbar deve ser renderizada
     * @return string View renderizada
     */
    public static function getPage($title, $content, $renderNavbar = true)
    {
        return self::render("base/page", [
            "title"   => $title,
            "header"  => $renderNavbar ? self::getHeader() : "",
            "content" => $content,
            "footer"  => self::getFooter()
        ]);
    }

    /**
     * Renderiza o topo da página
     * @return string View renderizada
     */
    private static function getHeader()
    {
        $variables = [
            "user" => htmlspecialchars(explode(" ", $_SESSION['user']['usuario']['nome'])[0]),
        ];

        foreach (self::$modules as $module => $value)
        {
            $variables["active-".$module] = $value ? "active" : "";
        }
        
        return self::render("base/header", $variables);;
    }

    /**
     * Renderiza o rodapé da página
     * @return string View renderizada
     */
    private static function getFooter()
    {
        return self::render("base/footer");
    }

    /**
     * Renderiza views de aluno
     * @param string $view A view a ser renderizada
     * @param array $params Parâmetros de renderização da view
     * @return string View renderizada
     */
    protected static function render($view, $params = [], $isModule = true)
    {
        return View::render(($isModule ? "student/" : "").$view, $params);
    }

    /**
     * Configura um novo módulo ativo
     * @param string $activeModule Novo do módulo a ser ativado
     */
    protected static function setActiveModule($activeModule)
    {
        foreach (self::$modules as $module => $value)
        {
            self::$modules[$module] = $module == $activeModule;
        }
    }
}