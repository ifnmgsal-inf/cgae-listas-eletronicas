<?php

namespace App\Controller\Assistant\Student;

use App\Controller\Assistant\Page;
use App\Controller\Common\Alert;
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
    public static function getView($id, $message = null, $success = false)
    {
        parent::setActiveModule("students");

        $content = self::getContent($id, $message, $success);

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

        $message = null;

        if (isset($postVars['acao']))
        {
            if ($postVars['acao'] == "ativar")
            {
                $senha = password_hash($postVars['senha'], PASSWORD_DEFAULT);
                $message = self::ativarUsuario($ob, $senha, $success);
            }

            else if ($postVars['acao'] == "desativar")
            {
                $ob->ativo = false;
                $ob->senha = NULL;
                $message = "Aluno desativado com sucesso!";
                $success = true;
                $ob->atualizar();
            }
        }

        return self::getView($id, $message, $success);
    }

    /**
     * Formata a view com os dados do aluno 
     * @param int $id ID do aluno a ser consultado
     * @param string $message Mensagem de alerta
     * @param bool $success Tipo da mensagem de alerta
     * @return string View renderizada
     */
    private static function getContent($id, $message, $success)
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
                "status" => is_null($message) ? "" : "<hr>".($success ? Alert::getSuccess($message) : Alert::getError($message))."<hr>",
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

    /**
     * Verifica se o aluno pode ser ativado e realiza sua ativação
     * @param Aluno $ob Instância do aluno
     * @param string $senha Nova senha de acesso do aluno
     * @param bool &$success Resultado da ativação
     */
    private static function ativarUsuario($ob, $senha, &$success)
    {
        if (!is_null(Aluno::getAlunoByEmail($ob->email)))
        {
            if (Aluno::getAlunoByEmail($ob->email)->ativo) 
            {
                $success = false;
                return "Ocorreu um erro ao ativar o aluno!<br>O email informado já está sendo utilizado por outro aluno!";
            }
        }

        if (!is_null(Aluno::getAlunoByIdRefeitorio($ob->idRefeitorio)))
        {
            if (Aluno::getAlunoByIdRefeitorio($ob->idRefeitorio)->ativo)
            {
                $success = false;
                return "Ocorreu um erro ao ativar o aluno!<br>O número do refeitório informado já está sendo utilizado por outro aluno!";
            }
        }
        
        if (!is_null(Aluno::processData(Aluno::getAlunos("quarto = '".$ob->quarto."' AND cama = '".$ob->cama."' AND sexo = '".$ob->sexo."'"))))
        {
            if (Aluno::processData(Aluno::getAlunos("quarto = '".$ob->quarto."' AND cama = '".$ob->cama."' AND sexo = '".$ob->sexo."'"))[0]->ativo)
            {
                $success = false;
                return "Ocorreu um erro ao ativar o aluno!<br>A cama informada já está sendo utilizada por outro aluno!";
            }
        }

        $ob->senha = $senha;
        $ob->ativo = true;
        $ob->atualizar();

        $success = true;
        return "Aluno ativado com sucesso!";
    }
}