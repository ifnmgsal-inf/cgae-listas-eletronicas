<?php

namespace App\Controller\Assistant\Signatures;

use App\Controller\Assistant\Page;
use App\Controller\Common\Alert;
use App\Model\Entity\Listas\Saida;
use App\Model\Entity\Listas\Pernoite;
use App\Model\Entity\Listas\VaiVolta;

/**
 * Controlador da página de editar assinatura
 */
class Edit extends Page
{
    /**
     * Entrypoint GET da rota
     * @param string $type Lista da assinatura
     * @param int $id ID da assinatura
     * @param string $message Mensagem de status
     * @param bool $success Tipo da mensagem de status
     * @return string View renderizada
     */
    public static function getView($type, $id, $message = null)
    {
        parent::setActiveModule("signatures");

        $data = self::getData($type, $id);
        $form = "";

        switch ($type)
        {
            case "vai_volta":
                $form = self::getVaiVoltaForm($data);
                break;

            case "saida":
                $form = self::getSaidaForm($data);
                break;

            case "pernoite":
                $form = self::getPernoiteForm($data);
                break;
        }

        $content = parent::render("signature/edit/index", [
            "status" => is_null($message) ? "" : Alert::getError($message),
            "form" => $form
        ]);

        return parent::getPage("Editar assinatura", $content);
    }

    /**
     * Entrypoint POST da rota
     * @param Request $request Objeto de requisição
     * @param string $list Lista da assinatura
     * @param int $id ID da assinatura
     * @return string View renderizada
     */
    public static function setView($request, $list, $id)
    {
        $postVars = $request->getPostVars();

        try
        {
            if ($postVars['acao'] != "editar") throw new \Exception();

            $var = [];

            foreach ($postVars as $key => $value)
            {
                if ($key == "acao") continue;

                $var[$key] = $value;
            }

            switch ($list)
            {
                case "vai_volta":
                    $res = self::verifyVaiVoltaData($postVars);

                    if (!is_null($res))
                    {
                        return self::getView($list, $id, $res);
                    }

                    $ob = new VaiVolta(-1, $_SESSION['user']['usuario']['id'], null, true, $postVars['destino'], $postVars['data'], $postVars['hora_saida'], $postVars['hora_chegada']);
                    $ob->cadastrar();
                    VaiVolta::atualizarAssinaturas("id = ".$id, ["ativa" => false, "pai" => $ob->id]);
                    break;

                case "saida":
                    $res = self::verifySaidaData($postVars);

                    if (!is_null($res))
                    {
                        return self::getView($list, $id, $res);
                    }

                    $ob = new Saida(-1, $_SESSION['user']['usuario']['id'], null, true, $postVars['destino'], $postVars['data_saida'], $postVars['data_chegada'], $postVars['hora_saida'], $postVars['hora_chegada']);
                    $ob->cadastrar();
                    Saida::atualizarAssinaturas("id = ".$id, ["ativa" => false, "pai" => $ob->id]);
                    break;

                case "pernoite":
                    $res = self::verifyPernoiteData($postVars);

                    if (!is_null($res))
                    {
                        return self::getView($list, $id, $res);
                    }

                    $ob = new Pernoite(-1, $_SESSION['user']['usuario']['id'], null, true, $postVars['endereco'], $postVars['nome_responsavel'], $postVars['telefone'], $postVars['data_saida'], $postVars['data_chegada'], $postVars['hora_saida'], $postVars['hora_chegada']);
                    $ob->cadastrar();
                    Pernoite::atualizarAssinaturas("id = ".$id, ["ativa" => false, "pai" => $ob->id]);
                    break;

                default:
                    throw new \Exception();
                    break;
            }

            $request->getRouter()->redirect("/ass/listas/".$list."/".$ob->id."?status=success");
        }
        
        catch (\Exception $e)
        {
            return self::getView($list, $id);
        }
    }

    /**
     * Configura a view da lista "vai e volta"
     * @param VaiVolta $data Instância da assinatura
     * @return string View renderizada
     */
    private static function getVaiVoltaForm($data)
    {
        $content = parent::render("signature/edit/vai_volta", [
            "lista" => "vai_volta",
            "id" => $data->id,
            "destino" => $data->destino,
            "data" => $data->data,
            "hora_saida" => $data->horaSaida,
            "hora_chegada" => $data->horaChegada
        ]);

        return $content;
    }

    /**
     * Configura a view da lista "saída"
     * @param Saida $data Instância da assinatura
     * @return string View renderizada
     */
    private static function getSaidaForm($data)
    {
        $content = parent::render("signature/edit/saida", [
            "lista" => "saida",
            "id" => $data->id,
            "destino" => $data->destino,
            "data_saida" => $data->dataSaida,
            "data_chegada" => $data->dataChegada,
            "hora_saida" => $data->horaSaida,
            "hora_chegada" => $data->horaChegada,
        ]);
        
        return $content;
    }

    /**
     * Configura a view da lista "vai e volta"
     * @param Pernoite $data Instância da assinatura
     * @return string View renderizada
     */
    private static function getPernoiteForm($data)
    {
        $content = parent::render("signature/edit/pernoite", [
            "lista" => "pernoite",
            "id" => $data->id,
            "endereco" => $data->endereco,
            "nome_responsavel" => $data->nomeResponsavel,
            "telefone" => $data->telefone,
            "data_saida" => $data->dataSaida,
            "data_chegada" => $data->dataChegada,
            "hora_saida" => $data->horaSaida,
            "hora_chegada" => $data->horaChegada
        ]);
        
        return $content;
    }

    /**
     * Recupera os dados da assinatura do banco de dados
     * @param string $type Tipo da assinatura
     * @param int $id ID da assinatura
     * @return mixed
     */
    private static function getData($type, $id)
    {
        $ob = null;

        switch ($type)
        {
            case "vai_volta":
                $ob = VaiVolta::getSignatureById($id);
                break;
                
            case "saida":
                $ob = Saida::getSignatureById($id);
                break;
                
            case "pernoite":
                $ob = Pernoite::getSignatureById($id);
                break;
        }

        return $ob;
    }

    /**
     * 
     * @param array $vars
     * @return string|null
     */
    private static function verifyVaiVoltaData($vars)
    {
        $data = $vars['data'];
        $horaSaida = strlen($vars['hora_saida']) == 5 ? $vars['hora_saida'].":00" : $vars['hora_saida'];
        $horaChegada = strlen($vars['hora_chegada']) == 5 ? $vars['hora_chegada'].":00" : $vars['hora_chegada'];

        // OBTÉM A DATA E HORA ATUAIS
        date_default_timezone_set("America/Sao_Paulo");
        $dataAtual = date("Y-m-d", time());
        $horaAtual = date("H:i:s", time() + 60);

        // VERIFICA SE OS DADOS DA ASSINATURA SÃO VÁLIDOS

        if ($dataAtual > $data)
        {
            return "A data de saída não pode ser anterior a data atual!";
        }

        if (!("05:00:00" <= $horaSaida && $horaSaida <= "23:00:00"))
        {
            return "O horário de saída deve estar compreendido entre as 05:00 e as 23:00 horas!";
        }

        if (!("05:00:00" <= $horaChegada && $horaChegada <= "23:00:00"))
        {
            return "O horário de chegada deve estar compreendido entre as 05:00 e as 23:00 horas!";
        }

        if ($dataAtual == $data)
        {
            if ($horaAtual > $horaSaida)
            {
                return "O horário de saída deve ser posteior ao horário atual!";
            }
        }

        if ($horaSaida >= $horaChegada)
        {
            return "O horário de chegada deve ser posteior ao horário de saída!";
        }

        return null;
    }

    /**
     * 
     * @param array $vars
     * @return string|null
     */
    private static function verifySaidaData($vars)
    {
        $dataSaida = $vars['data_saida'];
        $dataChegada = $vars['data_chegada'];
        $horaSaida = $vars['hora_saida'].":00";
        $horaChegada = $vars['hora_chegada'].":00";

        // OBTÉM A DATA E HORA ATUAIS
        date_default_timezone_set("America/Sao_Paulo");
        $dataAtual = date("Y-m-d", time());
        $horaAtual = date("H:i:s", time() + 60);

        // RECUPERA O HORÁRIO LIMITE PARA ASSINATURAS
        $hourInitial = getenv("HOUR_INITIAL");
        $hourFinal = getenv("HOUR_FINAL");

        // VERIFICA SE OS DADOS DA ASSINATURA SÃO VÁLIDOS
        if (!($hourInitial < $horaAtual && $horaAtual < $hourFinal))
        {
            return "O horário para cadastro de assinaturas na lista de saída já se encerrou!<br>Contate um assistente para realizar sua assinatura.";
        }

        if (!("05:00:00" <= $horaSaida && $horaSaida <= "23:00:00"))
        {
            return "O horário de saída deve estar compreendido entre as 05:00 e as 23:00 horas!";
        }

        if (!("05:00:00" <= $horaChegada && $horaChegada <= "23:00:00"))
        {
            return "O horário de chegada deve estar compreendido entre as 05:00 e as 23:00 horas!";
        }

        if ($dataAtual > $dataSaida)
        {
            return "A data de saída não pode ser anterior a data atual!";
        }

        if ($dataAtual == $dataSaida)
        {
            if ($horaAtual > $horaSaida)
            {
                return "O horário de saída deve ser posterior ao horário atual!";
            }
        }

        if ($horaSaida >= $horaChegada && $dataSaida == $dataChegada)
        {
            return "O horário de chegada deve ser posterior ao horário de saída!";
        }

        if ($dataSaida > $dataChegada)
        {
            return "A data de chegada não pode ser anterior a data de saída!";
        }
    }

    /**
     * 
     * @param array $vars
     * @return string|null
     */
    private static function verifyPernoiteData($vars)
    {
        $dataSaida = $vars['data_saida'];
        $dataChegada = $vars['data_chegada'];
        $horaSaida = $vars['hora_saida'].":00";
        $horaChegada = $vars['hora_chegada'].":00";

        // OBTÉM A DATA E HORA ATUAL
        date_default_timezone_set("America/Sao_Paulo");
        $dataAtual = date("Y-m-d", time());
        $horaAtual = date("H:i:s", time() + 60);

        // VERIFICA SE OS DADOS DA ASSINATURA SÃO VÁLIDOS
        if (!("05:00:00" <= $horaSaida && $horaSaida <= "23:00:00"))
        {
            return "O horário de saída deve estar compreendido entre 05:00 e 23:00 horas!";
        }

        if (!("05:00:00" <= $horaChegada && $horaChegada <= "23:00:00"))
        {
            return "O horário de chegada deve estar compreendido entre 05:00 e 23:00 horas!";
        }

        if ($dataAtual > $dataSaida)
        {
            return "A data de saída não pode ser anterior a data atual!";
        }

        if ($dataAtual == $dataSaida)
        {
            if ($horaAtual > $horaSaida)
            {
                return "O horário de saída deve ser posterior ao horário atual!";
            }
        }

        if ($dataSaida >= $dataChegada)
        {
            return "A data de chegada deve ser posterior a data de saída!";
        }

        if ($dataSaida == $dataChegada)
        {
            if ($horaSaida >= $horaChegada)
            {
                return "O horário de saída deve ser anterior ao horário de chegada!";
            }
        }
    }
}