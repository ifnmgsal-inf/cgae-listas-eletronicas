<?php

namespace App\Controller\Student;

/**
 * Controlador da página de perfil (aluno)
 */
class Profile extends Page
{
    /**
     * Retorna a view da página de perfil
     * @return string View renderizada
     */
    public static function getProfile()
    {
        // OBTÉM OS DADOS DO ALUNO
        $ob = \App\Model\Entity\Aluno::getAlunoById($_SESSION['user']['usuario']['id']);

        // TRANSFORMA OS DADOS
        $quarto = str_split($ob->quarto, 1);
        $quarto = $quarto[0].".".$quarto[1];

        // CONFIGURA A NAVBAR
        parent::setActiveModule("perfil");

        // RENDERIZA A VIEW DA PÁGINA
        $content = parent::render("profile/index", [
            "nome" => $ob->nome,
            "email" => $ob->email,
            "quarto" => $quarto,
            "cama" => $ob->cama,
            "serie" => $ob->serie."° ano",
            "numero" => $ob->idRefeitorio,
            "pernoite" => $ob->pernoite ? "Sim" : "Não"
        ]);

        return parent::getPage("Perfil", $content);
    }
}

?>