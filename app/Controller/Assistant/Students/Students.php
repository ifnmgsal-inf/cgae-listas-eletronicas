<?php

namespace App\Controller\Assistant\Students;

use App\Controller\Assistant\Page;
use App\Model\Entity\Aluno;

/**
 * Controlador da página de consulta de alunos 
 */
class Students extends Page
{
    /**
     * Entrypoint GET da rota
     * @return string View renderizada
     */
    public static function getView()
    {
        parent::setActiveModule("students");

        $content = parent::render("students/index", [
            "not_found" => self::getNotFound(),
            "no_itens" => self::getNoItens(),
            "itens" => self::getItens(),
            "page_limit" => getenv("PAGE_LIMIT")
        ]);

        return parent::getPage("Alunos", $content);
    }

    /**
     * Retorna a view "não encontrado"
     * @return string View renderizada
     */
    private static function getNotFound()
    {
        return parent::render("students/not_found");
    }
    
    /**
     * Retorna a view "sem dados"
     * @return string View renderizada
     */
    private static function getNoItens()
    {
        return parent::render("students/no_itens");
    }

    /**
     * Converte instâncias de Aluno em objetos JS
     * @return string Array de objetos JS
     */
    private static function getItens()
    {
        // INICIALIZA A SESSÃO
        \App\Session\Login::init();

        // RECUPERA OS DADOS DO BANCO
        $students = Aluno::processData(Aluno::getAlunos()) ?? [];
        $result = "";

        foreach ($students as $item)
        {
            // CONVERTE OS OBJETOS EM ARRAYS
            $arr = (array) $item;

            // INICIALIZA A ESCRITA DE UM NOVO OBJETO JS 
            $aux = "{";
            
            $aux .= "id: ".$arr['id'].", ";
            $aux .= "id_refeitorio: '".$arr["idRefeitorio"]."', ";
            $aux .= "nome: '".$arr["nome"]."', ";
            $aux .= "sexo: '".$arr["sexo"]."', ";
            $aux .= "quarto: '".join("-", str_split($arr["quarto"], 1))."', ";
            $aux .= "serie: '".$arr['serie']."'";

            // FINALIZA A DECLARAÇÃO DO OBJETO E O ADICIONA AO ARRAY DE OBJETOS JS
            $aux .= "}";
            $result .= $aux.", ";
        }
        
        // RETORNA O OBJETO JS DE DADOS
        return substr($result, 0, -2);
    }
}