<?php

namespace App\Controller\Assistant\Students;

use App\Controller\Assistant\Page;
use App\Model\Entity\Aluno;

/**
 * Controlador da página de atualizar alunos
 */
class Update extends Page
{
    /**
     * Entrypoint GET da rota
     * @return string View renderizada
     */
    public static function getView()
    {
        parent::setActiveModule("students");
        
        $content = parent::render("students/update/index");

        return parent::getPage("Alunos", $content);
    }

    /**
     * Entrypoint POST da rota
     * @param Request $request Objeto de requisição
     */
    public static function setView($request)
    {
        $ob = Aluno::processData(Aluno::getAlunos("ativo = true"));

        foreach ($ob as $item)
        {
            if ($item->serie == 3)
            {
                $item->ativo = false;
                $item->senha = null;
            }

            else
            {
                $item->serie ++;
            }

            $item->atualizar();
        }

        $request->getRouter()->redirect("/ass/alunos?status=success");
    }
}