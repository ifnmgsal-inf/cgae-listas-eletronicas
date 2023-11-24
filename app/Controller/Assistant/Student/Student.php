<?php

namespace App\Controller\Assistant\Student;

use App\Controller\Assistant\Page;
use App\Model\Entity\Aluno;

/**
 * Controlador da página de consulta de aluno
 */
class Student extends Page
{
    /**
     * Entrypoint GET da rota
     * @param int $id ID do aluno a ser consultado
     * @return string View renderizada
     */
    public static function getView($id)
    {
        parent::setActiveModule("students");

        $content = self::getContent($id);

        return parent::getPage("Aluno", $content);
    }

    /**
     * Entrypoint POST da rota
     * @param int $id ID do aluno a ser consultado
     * @return string View renderizada
     */
    public static function setView($request, $id)
    {
        $postVars = $request->getPostVars();
        $ob = Aluno::getAlunoById($id);

        if (!$ob instanceof Aluno)
        {
            return self::getView($id);
        }

        if (isset($postVars['acao']))
        {
            if ($postVars['acao'] == "ativar")
            {
                $senha = password_hash($postVars['senha'], PASSWORD_DEFAULT);
                $ob->senha = $senha;
                $ob->ativo = true;
            }

            else if ($postVars['acao'] == "desativar")
            {
                $ob->ativo = false;
                $ob->senha = NULL;
            }

            $ob->atualizar();
        }

        return self::getView($id);
    }

    /**
     * Formata a view com os dados do aluno 
     * @param int $id ID do aluno a ser consultado
     * @return string View renderizada
     */
    private static function getContent($id)
    {
        $content = null;
        $ob = Aluno::getAlunoById($id);

        if (is_null($ob))
        {
            throw new \Exception("student not found", 404);
        }

        else
        {
            $content = parent::render("student/index", [
                "nome" => $ob->nome,
                "email" => $ob->email,
                "refeitorio" => $ob->idRefeitorio,
                "quarto" => str_split($ob->quarto, 1)[0]."-".str_split($ob->quarto, 1)[1],
                "cama" => $ob->cama,
                "serie" => $ob->serie."°",
                "sexo" => ucfirst($ob->sexo),
                "pernoite" => $ob->pernoite ? "Sim" : "Não",
                "cidade" => $ob->cidade,
                "nome_responsavel" => $ob->nomeResponsavel,
                "telefone" => $ob->telefoneResponsavel,
                "actions" => self::getActions($id)
            ]);
        }

        return $content;
    }

    /**
     * Formata a view das ações disponíveis
     * @param int $id ID do Aluno a ser consultado
     * @return string View renderizada
     */
    private static function getActions($id)
    {
        $content = "";
        $ob = Aluno::getAlunoById($id);

        if (!is_null($ob))
        {
            if ($ob->ativo)
            {
                $content .= parent::render("student/actions/desativar");
            }

            else
            {
                $content .= parent::render("student/actions/ativar");
            }
            
            $content .= parent::render("student/actions/excluir", [
                "id" => $ob->id
            ]);

            $content .= parent::render("student/actions/atualizar", [
                "id" => $ob->id
            ]);
        }

        return $content;
    }
}