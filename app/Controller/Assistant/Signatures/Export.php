<?php

namespace App\Controller\Assistant\Signatures;

use App\Controller\Assistant\Page;
use App\Controller\Common\Alert;
use App\Model\Entity\Listas;
use App\Model\Entity\Aluno;

/**
 * Controlador da página de exportar dados
 */
class Export extends Page
{
    /**
     * Entrypoint GET da rota
     * @param Request $request Objeto de requisição
     * @return string View renderizada
     */
    public static function getView($request)
    {
        parent::setActiveModule("signatures");

        $alert = "";

        if (isset($request->getQueryParams()['status']))
        {
            $code = $request->getQueryParams()['status'];

            switch ($code)
            {
                case "200":
                    $alert = Alert::getSuccess("Os dados foram exportados com sucesso!");
                    break;

                case "404":
                    $alert = Alert::getError("Nenhum dado foi encontrado!");
                    break;

                case "503":
                    $alert = Alert::getError("Permita a abertura de pop-ups nas configurações do seu navegador!");
                    break;
                    
                case "500":
                    $alert = Alert::getError("Um erro inesperado ocorreu durante a exportação dos dados!");
                    break;
            }
        }

        $content = parent::render("signatures/export/index", [
            "alert" => $alert
        ]);

        return parent::getPage("Listas", $content);
    }

    /**
     * Entrypoint POST da rota
     * @param Request $request Objeto de requisição
     * @return string View renderizada
     */
    public static function setView($request)
    {
        $postVars = $request->getPostVars();

        date_default_timezone_set("America/Sao_Paulo");
        $sexo = $postVars['sexo'] ?? "masculino";
        $from = isset($postVars['from']) ? $postVars['from'] : "";
        $to = isset($postVars['to']) ? $postVars['to'] : "";

        $from = empty($from) ? date("Y-m-d", time()) : $from;
        $to = empty($to) ? date("Y-m-d", time()) : $to;

        if (self::compareDates($from, $to) == 1) 
        {
            $from = $postVars['to'];
            $to = $postVars['from'];
        }

        $res = "";

        switch ($postVars['lista'])
        {
            case "vai_volta":
                $data = Listas\VaiVolta::processData(Listas\VaiVolta::getSignatures("pai IS NULL", "id"));
                $aux = [];

                foreach ($data as $obj)
                {
                    if (self::compareDates($obj->data, $from) > -1 && self::compareDates($obj->data, $to) < 1)
                    {
                        $aluno = Aluno::getAlunoById($obj->aluno);

                        if ($aluno->sexo == $sexo) $aux[] = $obj;
                    }
                }

                $res = self::createVaiVoltaArray($aux);
                break;
                
            case "saida":
                $data = Listas\Saida::processData(Listas\Saida::getSignatures("pai IS NULL", "id"));

                $aux = [];

                foreach ($data as $obj)
                {
                    if ((self::compareDates($obj->dataSaida, $from) > -1 && self::compareDates($obj->dataSaida, $to) < 1) ||
                        (self::compareDates($obj->dataChegada, $from) > -1 && self::compareDates($obj->dataChegada, $to) < 1) ||
                        (self::compareDates($obj->dataSaida, $from) < 1 && self::compareDates($obj->dataChegada, $to) > -1))
                    {
                        $aluno = Aluno::getAlunoById($obj->aluno);
                        if ($aluno->sexo == $sexo) $aux[] = $obj;
                    }
                }

                $res = self::createSaidaArray($aux);
                break;
                
            case "pernoite":
                $data = Listas\Pernoite::processData(Listas\Pernoite::getSignatures("pai IS NULL", "id"));

                $aux = [];

                foreach ($data as $obj)
                {
                    if ((self::compareDates($obj->dataSaida, $from) > -1 && self::compareDates($obj->dataSaida, $to) < 1) ||
                        (self::compareDates($obj->dataChegada, $from) > -1 && self::compareDates($obj->dataChegada, $to) < 1) ||
                        (self::compareDates($obj->dataSaida, $from) < 1 && self::compareDates($obj->dataChegada, $to) > -1))
                    {
                        $aluno = Aluno::getAlunoById($obj->aluno);
                        if ($aluno->sexo == $sexo) $aux[] = $obj;
                    }
                }

                $res = self::createPernoiteArray($aux);
                break;

            default:
                $request->getRouter()->redirect("/ass/listas/exportar?status=500");
                break;
        }

        if (strlen($res) <= 2) $request->getRouter()->redirect("/ass/listas/exportar?status=404");

        $content = parent::render("signatures/export/create", [
            "lista" => $postVars['lista'],
            "dados" => $res
        ]);

        return $content;
    }

    /**
     * Inicializa objetos JS da lista "vai e volta"
     * @param VaiVolta $data Instância da assinatura
     * @return string Objeto JS
     */
    private static function createVaiVoltaArray($data)
    {
        $res = "[";
        
        foreach ($data as $obj)
        {
            $res .= "{";
                
            foreach ((array)$obj as $key => $value)
            {
                switch ($key)
                {
                    case "id": continue 2;
                    case "pai": continue 2;
                    case "ativa": continue 2;

                    case "aluno":
                        $aluno = Aluno::getAlunoById($value);

                        $res .= "nome: '".$aluno->nome."', ";
                        $res .= "quarto: '".str_split($aluno->quarto, 1)[0]."-".str_split($aluno->quarto, 1)[1]."', ";
                        $res .= "idRefeitorio: '".$aluno->idRefeitorio."', ";
                        break;
                    
                    case "destino":
                        $res .= "destino: '".$value."', ";
                        break;

                    case "data":
                        $data = $value;
                        $data = explode("-", $data);
                        $data = join("/", array_reverse($data));
                        $res .= "data: '".$data."', ";
                        break;

                    case "horaSaida":
                        $hora = explode(":", $value);
                        $hora = array($hora[0], $hora[1]);
                        $hora = join(":", $hora);
                        $res .= "horaSaida: '".$hora."', ";
                        break;

                    case "horaChegada":
                        $hora = explode(":", $value);
                        $hora = array($hora[0], $hora[1]);
                        $hora = join(":", $hora);
                        $res .= "horaChegada: '".$hora."', ";
                        break;
                }
            }

            $res = substr($res, 0, strlen($res) - 2)."}, ";
        }

        $res = strlen($res) > 1 ? substr($res, 0, strlen($res) - 2)."]" : "[]";
        return $res;
    }

    /**
     * Inicializa objetos JS da lista "saída"
     * @param Saida $data Instância da assinatura
     * @return string Objeto JS
     */
    private static function createSaidaArray($data)
    {
        $res = "[";
        
        foreach ($data as $obj)
        {
            $res .= "{";
                
            foreach ((array)$obj as $key => $value)
            {
                switch ($key)
                {
                    case "id": continue 2;
                    case "pai": continue 2;
                    case "ativa": continue 2;

                    case "aluno":
                        $aluno = Aluno::getAlunoById($value);

                        $res .= "nome: '".$aluno->nome."', ";
                        $res .= "quarto: '".str_split($aluno->quarto, 1)[0]."-".str_split($aluno->quarto, 1)[1]."', ";
                        $res .= "idRefeitorio: '".$aluno->idRefeitorio."', ";
                        break;
                    
                    case "destino":
                        $res .= "destino: '".$value."', ";
                        break;

                    case "dataSaida":
                        $data = $value;
                        $data = explode("-", $data);
                        $data = join("/", array_reverse($data));
                        $res .= "dataSaida: '".$data."', ";
                        break;

                    case "dataChegada":
                        $data = $value;
                        $data = explode("-", $data);
                        $data = join("/", array_reverse($data));
                        $res .= "dataChegada: '".$data."', ";
                        break;

                    case "horaSaida":
                        $hora = explode(":", $value);
                        $hora = array($hora[0], $hora[1]);
                        $hora = join(":", $hora);
                        $res .= "horaSaida: '".$hora."', ";
                        break;

                    case "horaChegada":
                        $hora = explode(":", $value);
                        $hora = array($hora[0], $hora[1]);
                        $hora = join(":", $hora);
                        $res .= "horaChegada: '".$hora."', ";
                        break;
                }
            }

            $res = substr($res, 0, strlen($res) - 2)."}, ";
        }

        $res = strlen($res) > 1 ? substr($res, 0, strlen($res) - 2)."]" : "[]";
        return $res;
    }

    /**
     * Inicializa objetos JS da lista "pernoite"
     * @param Pernoite $data Instância da assinatura
     * @return string Objeto JS
     */
    private static function createPernoiteArray($data)
    {
        $res = "[";
        
        foreach ($data as $obj)
        {
            $res .= "{";
                
            foreach ((array)$obj as $key => $value)
            {
                switch ($key)
                {
                    case "id": continue 2;
                    case "pai": continue 2;
                    case "ativa": continue 2;

                    case "aluno":
                        $aluno = Aluno::getAlunoById($value);

                        $res .= "nome: '".$aluno->nome."', ";
                        $res .= "quarto: '".str_split($aluno->quarto, 1)[0]."-".str_split($aluno->quarto, 1)[1]."', ";
                        $res .= "idRefeitorio: '".$aluno->idRefeitorio."', ";
                        break;
                    
                    case "endereco":
                        $res .= "destino: '".$value."', ";
                        break;
                    
                    case "nomeResponsavel":
                        $res .= "responsavel: '".$value."', ";
                        break;
                
                    case "telefone":
                        $res .= "telefone: '".$value."', ";
                        break;

                    case "dataSaida":
                        $data = $value;
                        $data = explode("-", $data);
                        $data = join("/", array_reverse($data));
                        $res .= "dataSaida: '".$data."', ";
                        break;

                    case "dataChegada":
                        $data = $value;
                        $data = explode("-", $data);
                        $data = join("/", array_reverse($data));
                        $res .= "dataChegada: '".$data."', ";
                        break;

                    case "horaSaida":
                        $hora = explode(":", $value);
                        $hora = array($hora[0], $hora[1]);
                        $hora = join(":", $hora);
                        $res .= "horaSaida: '".$hora."', ";
                        break;

                    case "horaChegada":
                        $hora = explode(":", $value);
                        $hora = array($hora[0], $hora[1]);
                        $hora = join(":", $hora);
                        $res .= "horaChegada: '".$hora."', ";
                        break;
                }
            }

            $res = substr($res, 0, strlen($res) - 2)."}, ";
        }

        $res = strlen($res) > 1 ? substr($res, 0, strlen($res) - 2)."]" : "[]";
        return $res;
    }

    /**
     * Compara duas datas
     * @param string $date1 1° data (dd-mm-YYYY)
     * @param string $date2 2° data (dd-mm-YYYY)
     * @return int [1] - date1 > date2 | [0] - date1 = date2 | [-1] - date1 < date2
     */
    private static function compareDates($date1, $date2)
    {
        $aux1 = explode("-", $date1);
        $aux2 = explode("-", $date2);

        for ($i = 0; $i < 3; $i++)
        {
            if ($aux1[$i] > $aux2[$i]) return 1;

            if ($aux1[$i] < $aux2[$i]) return -1;
        }

        return 0;
    }
}