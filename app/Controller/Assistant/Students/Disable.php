<?php

namespace App\Controller\Assistant\Students;

use App\Controller\Common\Alert;
use App\Controller\Assistant\Page;
use App\Model\Entity\Aluno;

/**
 * Controlador da página de desativar alunos
 */
class Disable extends Page
{
    /**
     * Entrypoint GET da rota
     * @param string $message Mensagem de status
     * @param bool $success tipo da mensagem de status
     * @return string View renderizada
     */
    public static function getView($message = null, $success = false)
    {
        parent::setActiveModule("students");

        $content = parent::render("students/disable/index", [
            "status" => is_null($message) ? null : "<br>".($success ? Alert::getSuccess($message) : Alert::getError($message))
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
        $where = "ativo = true";

        foreach ($postVars as $key => $value)
        {
            if ($value != "null" && $key != "confirm")
            {
                $where .= " AND ".$key." = '".$value."'";
            }
        }

        if (isset($postVars['confirm']))
        {
            self::disableStudents($where);
            return self::getView("Alunos desativados com sucesso!", true);
        }

        $obStudents = Aluno::processData(Aluno::getAlunos($where));

        if (!empty($obStudents))
        {
            return self::getViewConfirm($obStudents, $request);
        }

        return self::getView("Nenhum aluno foi encontrado!");
    }

    /**
     * Entrypoint GET da rota de confirmação
     * @param array $obStudents Array de instâncias de Aluno
     * @param Request $request Objeto de requisição
     * @return string View renderizada
     */
    private static function getViewConfirm($obStudents, $request)
    {
        parent::setActiveModule("students");
        
        $content = parent::render("students/disable/confirm", self::getContent($obStudents, $request));

        return parent::getPage("Alunos", $content);
    }

    /**
     * Configura a view de confirmação segundo os dados disponíveis
     * @param array $obStudents Array de instâncias de Aluno
     * @param Request $request Objeto de requisição
     * @return array View renderizada
     */
    private static function getContent($obStudents, $request)
    {
        $postVars = $request->getPostVars();

        $content = [
            "num-alunos" => count($obStudents),
            "quarto" => $postVars['quarto'],
            "serie" => $postVars['serie'],
            "sexo" => $postVars['sexo']
        ];

        $lines = "";

        foreach ($obStudents as $item)
        {
            $lines .= parent::render("students/disable/item", [
                "id_refeitorio" => htmlspecialchars($item->idRefeitorio),
                "nome" => htmlspecialchars($item->nome),
                "sexo" => htmlspecialchars(ucfirst($item->sexo)),
                "quarto" => htmlspecialchars(str_split($item->quarto)[0].".".str_split($item->quarto)[1]),
                "serie" => htmlspecialchars($item->serie."°")
            ]);
        }

        $content['lines'] = $lines;

        return $content;
    }

    /**
     * Desabilita os alunos
     * @param string $where Condição da query SQL de DELETE
     */
    private static function disableStudents($where)
    {
        $list = Aluno::processData(Aluno::getAlunos($where));

        foreach ($list as $item)
        {
            $item->senha = null;
            $item->ativo = false;
            $item->atualizar();
        }
    }
}