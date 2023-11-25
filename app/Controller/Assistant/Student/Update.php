<?php

namespace App\Controller\Assistant\Student;

use App\Controller\Common\Alert;
use App\Controller\Assistant\Page;
use App\Model\Entity\Aluno;

/**
 * Controlador da página de atualizar aluno
 */
class Update extends Page
{
    /**
     * Entrypoint GET da rota
     * @param int $id ID do aluno a ser editado
     * @return string View renderizada
     */
    public static function getView($id, $message = null, $success = false)
    {
        parent::setActiveModule("students");

        $content = parent::render("student/update/index", self::getForm($id, $message, $success));

        return parent::getPage("Alunos", $content);
    }

    /**
     * Entrypoint POST da rota
     * @param Request $request Objeto de requisição
     * @param int $id ID doa aluno a ser atualizado
     * @return string View renderizada
     */
    public static function setView($request, $id)
    {
        $postVars = $request->getPostVars();

        $ob = Aluno::getAlunoById($id);

        $ob1 = Aluno::processData(Aluno::getAlunos("ativo = true AND id_refeitorio = ".$postVars['refeitorio']." AND id != ".$ob->id));
        $ob2 = Aluno::processData(Aluno::getAlunos("ativo = true AND email = '".$postVars['email']."' AND id != ".$ob->id));

        if (!is_null($ob1))
        {
            return self::getView($id, "O número do refeitório informado já está sendo utilizado por outro aluno!", false);
        }
        
        if (!is_null($ob2))
        {
            return self::getView($id, "O email informado já está sendo utilizado por outro aluno!", false);
        }

        if ($ob->ativo)
        {
            $senha = empty($postVars['senha']) ? $ob->senha : password_hash($postVars['senha'], PASSWORD_DEFAULT);
        }
        
        else 
        {
            $senha = NULL;
        }

        $ob->nome = $postVars['nome'];
        $ob->email = $postVars['email'];
        $ob->senha = $senha;
        $ob->nomeResponsavel = $postVars['nome_responsavel'];
        $ob->telefoneResponsavel = $postVars['telefone'];
        $ob->sexo = $postVars['sexo'];
        $ob->pernoite = isset($postVars['pernoite']);
        $ob->cidade = $postVars['cidade'];
        $ob->quarto = $postVars['quarto'];
        $ob->serie = $postVars['serie'];
        $ob->idRefeitorio = $postVars['refeitorio'];

        $ob->atualizar();

        return self::getView($id, "Atualizado com sucesso!", true);
    }

    /**
     * Configura as variáveis da view
     * @param int $id ID do aluno a ser atualizado
     * @return array Variáveis da view
     */
    private static function getForm($id, $message, $success)
    {
        $ob = Aluno::getAlunoById($id);

        $content = [
            "id" => $ob->id,
            "nome" => $ob->nome,
            "email" => $ob->email,
            "responsavel" => $ob->nomeResponsavel,
            "telefone" => $ob->telefoneResponsavel,
            "cidade" => $ob->cidade,
            "id_refeitorio" => $ob->idRefeitorio,
            "checked-00" => $ob->pernoite ? "checked" : ""
        ];

        $index = [
            "quarto" => 0,
            "serie" => 0
        ];

        $map = [
            "11" => 0,
            "12" => 1,
            "13" => 2,
            "14" => 3,
            "15" => 4,
            "21" => 5,
            "22" => 6,
            "23" => 7,
            "24" => 8,
            "31" => 9,
            "32" => 10,
            "33" => 11,
            "34" => 12
        ];
         
        $index['quarto'] = $map[(string)$ob->quarto];
        $index['serie'] = round((int)$ob->serie) - 1;

        for ($i = 0; $i < 13; $i++)
        {
            if ($i == $index['quarto'])
            {
                $content['selected-'.$i] = "selected";
                continue;
            }

            $content['selected-'.$i] = "";
        }
        
        for ($i = 0; $i < 3; $i++)
        {
            if ($i == $index['serie'])
            {
                $content['selected-0'.$i] = "selected";
                continue;
            }

            $content['selected-0'.$i] = "";
        }

        if ($ob->sexo == "masculino")
        {
            $content['checked-0'] = "checked";
            $content['checked-1'] = "";
        }

        else
        {
            $content['checked-0'] = "";
            $content['checked-1'] = "checked";
        }

        $content['status'] = self::getStatus($message, $success);

        return $content;
    }

    /**
     * Retorna mensagens de status
     * @param string $message Conteúdo da mensagem
     * @param bool $success Tipo da mensagem
     * @return string|null View renderizada
     */
    private static function getStatus($message, $success)
    {
        if (is_null($message))
        {
            return null;
        }

        if ($success)
        {
            return Alert::getSuccess($message);
        }

        else
        {
            return Alert::getError($message);
        }
    }
}

?>