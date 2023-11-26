<?php

namespace App\Controller\Assistant;

use \App\Utils\View;

/**
 * Controlador da página base
 */
class Page
{
    /**
     * Módulos da navbar
     * @var array
     */
    private static $modules = [
        "home" => false,
        "signatures" => false,
        "students" => false,
        "profile" => false
    ];

    /**
     * Retorna o conteúdo da página base
     * @param string $title Título da página
     * @param string $content Conteúdo da página
     * @param string $renderNavbar Indica se a navbar deve ser renderizada na página
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
        // INICIALIZA A SESSÃO
        \App\Session\Login::init();

        // CONFIGURA OS PARÂMETROS DE RENDERIZAÇÃO
        $itens = [
            "user" => htmlspecialchars($_SESSION['user']['usuario']['nome'])
        ];

        foreach (self::$modules as $name => $value)
        {
            $itens['active-'.$name] = $value ? "active" : "";
        }
        
        // RETORNA A VIEW
        return self::render("base/header", $itens);
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
     * Renderiza views de assistant
     * @param string $view A view a ser renderizada
     * @param array $params Parâmetros de renderização da view
     * @param bool $isModule Indica o tipo de view a ser renderizada
     * @return string View renderizada
     */
    protected static function render($view, $params = [], $isModule = true)
    {
        return View::render(($isModule ? "assistant/" : "").$view, $params);
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