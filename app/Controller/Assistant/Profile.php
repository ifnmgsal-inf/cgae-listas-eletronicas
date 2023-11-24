<?php

namespace App\Controller\Assistant;

use App\Model\Entity\Assistente;
use App\Controller\Common\Alert;

/**
 * Controlador da página de perfil
 */
class Profile extends Page
{
    /**
     * Entrypoint GET da rota
     * @return string View renderizada
     */
    public static function getView()
    {
        // CONFIGURA A NAVBAR
        parent::setActiveModule("profile");
        
        // INICIALIZA A SESSÃO
        \App\Session\Login::init();

        // RENDERIZA A VIEW
        $content = parent::render("profile/index", [
            "nome" => $_SESSION['user']['usuario']['nome'],
            "email" => $_SESSION['user']['usuario']['email']
        ]);

        return parent::getPage("Perfil", $content);
    }

    /**
     * Entrypoint GET da rota de editar perfil
     * @param Request $request Objeto de requisição
     * @param string $message Mensagem de status
     * @param bool $success Tipo da mensagem de status
     * @return string View renderizada
     */
    public static function getViewEditProfile($request, $message = null, $success = false)
    {
        parent::setActiveModule("profile");
        $content = parent::render("profile/edit", self::getAttributes($message, $success));
        return parent::getPage("Editar", $content);
    }
    
    /**
     * Entrypoint POST da rota de editar perfil
     * @param Request $request Objeto de requisição
     * @return string View renderizada
     */
    public static function setViewEditProfile($request)
    {
        \App\Session\Login::init();

        $message = "";
        $success = false;
        $postVars = $request->getPostVars();

        $ob = Assistente::getAssistenteById($_SESSION['user']['usuario']['id']);

        $ob->nome = $postVars['nome'];
        $ob->email = $postVars['email'];
        $ob->senha = password_hash($postVars['senha'], PASSWORD_DEFAULT);

        $ob->atualizar();

        \App\Session\Login::logout();
        \App\Session\Login::login($ob);

        $message = "Atualizado com sucesso!";
        $success = true;

        $content = parent::render("profile/edit", self::getAttributes($message, $success));
        return parent::getPage("Editar", $content);
    }

    /**
     * Configura as variáveis da view de consultar perfil
     * @param string $message Mensagem de status
     * @param bool $success Tipo da mensagem de status
     * @return array Variáveis da view
     */
    private static function getAttributes($message = null, $success = false)
    {
        $attr = [];
        $ob = Assistente::getAssistenteById($_SESSION['user']['usuario']['id']);

        $attr['status'] = is_null($message) ? "" : ($success ? Alert::getSuccess($message) : Alert::getError($message));
        $attr['nome'] = $ob->nome;
        $attr['email'] = $ob->email;

        return $attr;
    }
}