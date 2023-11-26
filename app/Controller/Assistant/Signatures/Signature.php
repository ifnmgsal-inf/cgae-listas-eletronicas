<?php

namespace App\Controller\Assistant\Signatures;

use App\Controller\Assistant\Page;
use App\Controller\Common\Alert;
use App\Model\Entity\Listas;
use App\Model\Entity\Aluno;

/**
 * Controlador da página de consultar assinatura
 */
class Signature extends Page
{
    /**
     * Entrypoint GET da rota
     * @param Request $request Objeto de requisição
     * @param string $list Lista da assinatura
     * @param int $id ID da assinatura a ser consultada
     * @return string View renderizada
     */
    public static function getView($request, $list, $id)
    {
        parent::setActiveModule("signatures");

        $content = parent::render("signature/index", [
            "status" => isset($request->getQueryParams()['status']) ? ($request->getQueryParams()['status'] == "success" ? Alert::getSuccess("Atualizada com sucesso!") : "") : "",
            "list" => $list,
            "id" => $id,
            "dados" => self::getDados($list, $id),
            "edit" => self::getEdit($list, $id)
        ]);

        return parent::getPage("Assinatura", $content);
    }

    /**
     * Configura a view segundo os dados da assinatura
     * @param string $list Lista da assinatura
     * @param int $id ID da assinatura
     * @return string View renderizada
     */
    private static function getDados($list, $id)
    {
        $ob = null;

        switch ($list)
        {
            case "vai_volta":
                $ob = Listas\VaiVolta::getSignatureById($id) ?? throw new \Exception("not found", 404);
                $type = "Vai e Volta";
                break;
            
            case "saida":
                $ob = Listas\Saida::getSignatureById($id) ?? throw new \Exception("not found", 404);
                $type = "Saída";
                break;

            case "pernoite":
                $ob = Listas\Pernoite::getSignatureById($id) ?? throw new \Exception("not found", 404);
                $type = "Pernoite";
                break;

            default:
                throw new \Exception("not found", 404);
        }

        $content = self::createJsObj((array) $ob, $type);

        return $content;
    }

    /**
     * Inicializa um objeto JS a partir uma instância de assinatura
     * @param array $arr Instância da assinatura convertida em Array
     * @param string $type Lista da assinatura
     * @return string Objeto JS
     */
    private static function createJsObj($arr, $type)
    {
        $keys = array_keys($arr);
        $values = array_values($arr);
        
        $values = array_map(function ($item)
        {
            return htmlspecialchars($item);
        }, $values);
        
        $res = "{Lista: '".$type."', ";
        $res .= "renderEdit: ".(is_null($arr['pai']) ? "true" : "false").", ";

        for ($i = 0; $i < count($keys); $i++)
        {
            switch ($keys[$i])
            {
                case "id":
                    continue 2;

                case "pai":
                    continue 2;

                case "aluno":
                    $values[$i] = htmlspecialchars(Aluno::getAlunoById($values[$i])->nome);
                    break;

                case "nomeResponsavel":
                    $keys[$i] = "nomeDoResponsável";
                    break;

                case "endereco":
                    $keys[$i] = "endereço";
                    break;

                case "dataSaida":
                    $keys[$i] = "dataDeSaída";
                    break;

                case "dataChegada":
                    $keys[$i] = "dataDeChegada";
                    break;
            }

            if (str_contains($keys[$i], "data"))
            {
                $values[$i] = explode("-", $values[$i]);
                $values[$i] = join("/", array_reverse($values[$i]));
            }
            
            $keys[$i] = strtolower(preg_replace(["/([A-Z]+)/", "/_([A-Z]+)([A-Z][a-z])/"], ["_$1", "_$1_$2"], lcfirst($keys[$i])));
            $keys[$i] = str_replace("hora", "horário_de", $keys[$i]);

            $res .= ($keys[$i] == "ativa" ? $keys[$i] : ucfirst($keys[$i])).": ".($keys[$i] == "ativa" ? ($values[$i] ? "true" : "false") : "'".$values[$i]."'").", ";
        }

        return substr($res, 0, -2)."}";
    }

    /**
     * Inicializa um objeto JS a partir uma instância de assinatura
     * @param array $arr Instância da assinatura convertida em Array
     * @param string $list Lista da assinatura
     * @return string Objeto JS
     */
    private static function createEditJsObj($arr, $list)
    {
        $keys = array_keys($arr);
        $values = array_values($arr);

        $values = array_map(function ($item)
        {
            return htmlspecialchars($item);
        }, $values);

        $res = "{type: '".$list."', ";

        for ($i = 0; $i < count($keys); $i++)
        {
            switch ($keys[$i])
            {
                case "id":
                    break;

                case "endereco":
                    break;

                case "destino":
                    break;

                case "data":
                    break;

                case "dataSaida":
                    break;

                case "dataChegada":
                    break;

                case "horaSaida":
                    break;

                case "horaChegada":
                    break;

                default: 
                    continue 2;
            }

            if (str_contains($keys[$i], "data"))
            {
                $values[$i] = explode("-", $values[$i]);
                $values[$i] = join("/", array_reverse($values[$i]));
            }
            
            if ($keys[$i] == "data")
            {
                $res .= "Data_saida: "."'".$values[$i]."', ";
                $res .= "Data_chegada: "."'".$values[$i]."', ";
            }

            else 
            {
                $keys[$i] = strtolower(preg_replace(["/([A-Z]+)/", "/_([A-Z]+)([A-Z][a-z])/"], ["_$1", "_$1_$2"], lcfirst($keys[$i])));
                $res .= ($keys[$i] == "id" ? "id" : ucfirst($keys[$i])).": "."'".$values[$i]."', ";
            }
        }
        
        return substr($res, 0, -2)."}";
    }

    /**
     * Configura a view de edições realizadas
     * @param string $list Lista da assinatura
     * @param int $id ID da assinatura
     * @return string View renderizada
     */
    private static function getEdit($list, $id)
    {
        $arr = [];
        $fatherId = $id;

        switch ($list)
        {
            case "vai_volta":
                while (True)
                {
                    $aux = Listas\VaiVolta::getSignatureByFather($fatherId);

                    if (is_null($aux)) break;

                    $fatherId = $aux->id;
                    $arr[] = $aux;
                }
                
                break;

            case "saida":
                while (True)
                {
                    $aux = Listas\Saida::getSignatureByFather($fatherId);

                    if (is_null($aux)) break;

                    $fatherId = $aux->id;
                    $arr[] = $aux;
                }

                break;

            case "pernoite":
                while (True)
                {
                    $aux = Listas\Pernoite::getSignatureByFather($fatherId);

                    if (is_null($aux)) break;

                    $fatherId = $aux->id;
                    $arr[] = $aux;
                }

                break;
        }

        if (!count($arr)) return "[]";

        $res = "[";

        for ($i = 0; $i < count($arr); $i++)
        {
            $res .= self::createEditJsObj((array) $arr[$i], $list).", ";
        }

        return substr($res, 0, -2)."]";
    }
}