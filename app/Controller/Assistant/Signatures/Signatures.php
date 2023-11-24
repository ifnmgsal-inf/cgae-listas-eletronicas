<?php

namespace App\Controller\Assistant\Signatures;

use App\Controller\Assistant\Page;
use App\Model\Entity\Listas;
use App\Model\Entity\Aluno;

/**
 * Controlador da página de consulta de assinaturas
 */
class Signatures extends Page
{
    /**
     * Entrypoint GET da rota
     * @return string View renderizada
     */
    public static function getView()
    {
        parent::setActiveModule("signatures");

        $content = parent::render("signatures/index",
        [
            "no_itens" => self::getNoItens(),
            "not_found" => self::getNotFound(),
            "itens" => self::getItens(),
            "page_limit" => getenv("PAGE_LIMIT")
        ]);

        return parent::getPage("Listas", $content);
    }

    /**
     * Retorna a view "sem dados"
     * @return string View renderizada
     */
    private static function getNoItens()
    {
        return parent::render("signatures/no_itens");
    }

    /**
     * Retorna a view "não encontrado"
     * @return string View renderizada
     */
    private static function getNotFound()
    {
        return parent::render("signatures/not_found");
    }

    /**
     * Configura a view segundo os dados das assinaturas
     * @return string View renderizada
     */
    private static function getItens()
    {
        // INICIALIZA A SESSÃO
        \App\Session\Login::init();

        // RECUPERA OS DADOS DO BANCO
        $lists['vai_volta'] = Listas\VaiVolta::processData(Listas\VaiVolta::getSignatures("pai IS NULL", "id DESC"));
        $lists['saida'] = Listas\Saida::processData(Listas\Saida::getSignatures("pai IS NULL", "id DESC"));
        $lists['pernoite'] = Listas\Pernoite::processData(Listas\Pernoite::getSignatures("pai IS NULL", "id DESC"));    
        
        $result = "";

        foreach (array_merge($lists['vai_volta'], $lists['saida'], $lists['pernoite']) as $item)
        {
            // CONVERTE OS OBJETOS EM ARRAYS
            $arr = (array) $item;
            $keys = array_keys($arr);
            $values = array_values($arr);

            // INICIALIZA A ESCRITA DE UM NOVO OBJETO JS 
            $aux = "{";
            
            for ($i = 0; $i < count($keys); $i++)
            {
                $valueData = $values[$i];

                // FORMATA A DATA PARA O PADRÃO dd/mm/yyyy E ADICIONA O ATRIBUTO AO OBJETO
                if (str_contains($keys[$i], "data"))
                {
                    if (!isset($arr["data"]))
                    {
                        if ($keys[$i] == "dataChegada") continue;
                    }

                    $valueData = explode("-", $valueData, 4);
                    $valueData = $valueData[2]."/".$valueData[1]."/".$valueData[0];
                    $aux .= "data: '".$valueData."', ";
                    continue;
                }

                // VERIFICA SE O ATRIBUTO É VÁLIDO E O ADICIONA AO OBJETO JS
                switch ($keys[$i])
                {
                    case "aluno":
                        $aux .= "aluno: '".Aluno::getAlunoById($valueData)->nome."', ";
                        break;

                    case "ativa":
                        $aux .= "ativa: ".($valueData ? "true" : "false").", ";
                        break;

                    case "id":
                        $aux .= "id: ".$valueData.", ";
                }
            }

            // ADICIONA O ATRIBUTO IDENTIFICADOR DE TIPO
            if (isset($arr["data"]))
            {
                $aux .= "lista: 'vai_volta'";
            }

            else if (isset($arr['endereco']))
            {
                $aux .= "lista: 'pernoite'";
            }

            else
            {
                $aux .= "lista: 'saida'";
            }

            // FINALIZA A DECLARAÇÃO DO OBJETO E O ADICIONA AO ARRAY DE OBJETOS JS
            $aux .= "}";
            $result .= $aux.", ";
        }
        
        // RETORNA O OBJETO JS DE DADOS
        return substr($result, 0, -2);
    }
}

?>