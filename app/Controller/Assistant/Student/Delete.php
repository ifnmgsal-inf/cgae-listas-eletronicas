<?php

namespace App\Controller\Assistant\Student;

use App\Controller\Assistant\Page;
use App\Model\Entity\Aluno;

/**
 * Controlador da página de excluir aluno
 */
class Delete extends Page
{
    /**
     * Entrypoint POST da rota
     * @param Request $request Objeto de requisição
     * @param int $id ID do aluno a ser excluído
     */
    public static function setView($request, $id)
    {
        if ($request->getPostVars()['acao'] != "excluir") throw new \Exception("credentials missing", 500);

        $ob = Aluno::getAlunoById($id);
        $ob->excluir();

        $request->getRouter()->redirect("/ass/alunos");
    }
}

?>