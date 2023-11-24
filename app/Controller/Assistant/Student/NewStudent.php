<?php

namespace App\Controller\Assistant\Student;

use App\Controller\Common\Alert;
use App\Controller\Assistant\Page;
use App\Model\Entity\Aluno;

/**
 * Controlador da página de cadastro de aluno
 */
class NewStudent extends Page
{
    /**
     * Entrypoint GET da rota
     * @return string View renderizada
     */
    public static function getView($message = null, $success = false)
    {
        parent::setActiveModule("students");

        $content = parent::render("students/new/index", [
            "status" => self::getStatus($message, $success)
        ]);

        return parent::getPage("Alunos", $content);
    }

    /**
     * Entrypoint POST da rota
     * @param Request $request Objeto de requisição
     * @return string View renderizada
     */
    public static function setView($request)
    {
        $postVars = $request->getPostVars();
        
        $ob = new Aluno(-1, $postVars['nome'], $postVars['sexo'], $postVars['email'], $postVars['quarto'], $postVars['cama'], $postVars['serie'], $postVars["refeitorio"], null, (isset($postVars['pernoite']) ? true : false), $postVars['nome_responsavel'], $postVars['cidade'], $postVars['telefone']);
        
        $message = "";
        $res = self::verifyAluno($ob);

        if (is_null($res))
        {
            $ob->cadastrar();
            $message = "Aluno cadastrado com sucesso!";
        }
        
        else
        {
            $message = $res;
        }
        
        return self::getView($message, is_null($res));
    }

    /**
     * Verifica se os dados informados são válidos
     * @param Aluno $ob Instância do aluno
     * @return string|null Mensagem de erro caso algum dado seja inválido
     */
    private static function verifyAluno($ob)
    {
        if (!is_null(Aluno::getAlunoByEmail($ob->email)))
        {
            if (Aluno::getAlunoByEmail($ob->email)->ativo) return "O email informado já está sendo utilizado por outro aluno!";
        }

        if (!is_null(Aluno::getAlunoByIdRefeitorio($ob->idRefeitorio)))
        {
            if (Aluno::getAlunoByIdRefeitorio($ob->idRefeitorio)->ativo) return "O número do refeitório informado já está sendo utilizado por outro aluno!";
        }
        
        if (!is_null(Aluno::processData(Aluno::getAlunos("quarto = '".$ob->quarto."' AND cama = '".$ob->cama."' AND sexo = '".$ob->sexo."'"))))
        {
            if (Aluno::processData(Aluno::getAlunos("quarto = '".$ob->quarto."' AND cama = '".$ob->cama."' AND sexo = '".$ob->sexo."'"))[0]->ativo) return "A cama informada já está sendo utilizada por outro aluno!";
        }

        return null;
    }

    /**
     * Retorna mensagens de status
     * @param string $message Conteúdo da mensagem
     * @param bool $success Tipo da mensagem
     * @return string|null View renderizada
     */
    private static function getStatus($message, $success)
    {
        $content = null;

        if (!is_null($message))
        {
            if ($success)
            {
                $content = Alert::getSuccess($message);
            }

            else
            {
                $content = Alert::getError($message);
            }
        }

        return $content;
    }
}