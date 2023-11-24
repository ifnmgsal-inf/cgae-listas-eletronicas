<?php

namespace App\Controller\Assistant;

/**
 * Controlador da página principal
 */
class Home extends Page
{
    /**
     * Entrypoint GET da rota 
     * @return string View renderizada
     */
    public static function getView()
    {
        // CONFIGURA A NAVBAR
        parent::setActiveModule("home");

        // RENDERIZA A PÁGINA
        $content = parent::render("home/index");

        return parent::getPage("Painel", $content, true);
    }
}

?>