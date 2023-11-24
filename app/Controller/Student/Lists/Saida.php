<?php

namespace App\Controller\Student\Lists;

use App\Controller\Student\Page;
use App\Controller\Common\Alert;
use App\Model\Entity\Listas\Saida as EntitySaida;

/**
 * Controlador da página da lista de saída (aluno)
 */
class Saida extends Page
{
    /**
     * Retorna a view da lista de saída
     * @param string $message Texto da mensagem
     * @param bool $success Indica se a mensagem corresponde a um processo bem ou mal sucedido
     * @return string View renderizada
     */
    public static function getSaida($message = null, $success = false)
    {
        // CONFIGURA A NAVBAR
        parent::setActiveModule("listas");

        // RENDERIZA A VIEW
        $content = parent::render("lists/saida", [
            "status" => !is_null($message) ? (!$success ? Alert::getError($message) : Alert::getSuccess($message)) : ""
        ]);
        
        return parent::getPage("Listas | Saída", $content);
    }

    /**
     * Cadastra a assinatura
     * @param Request $request Objeto de requisição
     * @return string View renderizada
     */
    public static function setSaida($request)
    {
        // RECUPERA AS VÁRIAVEIS DE POST
        $postVars = $request->getPostVars();

        $destino = $postVars['destino'];
        $dataSaida = $postVars['data_saida'];
        $dataChegada = $postVars['data_chegada'];
        $horaSaida = $postVars['hora_saida'].":00";
        $horaChegada = $postVars['hora_chegada'].":00";

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
            return self::getSaida("O horário para cadastro de assinaturas na lista de saída já se encerrou!<br>Contate um assistente para realizar sua assinatura!");
        }

        if (!("05:00:00" <= $horaSaida && $horaSaida <= "23:00:00"))
        {
            return self::getSaida("O horário de saída deve estar compreendido entre as 05:00 e as 23:00 horas!");
        }

        if (!("05:00:00" <= $horaChegada && $horaChegada <= "23:00:00"))
        {
            return self::getSaida("O horário de chegada deve estar compreendido entre as 05:00 e as 23:00 horas!");
        }

        if ($dataAtual > $dataSaida)
        {
            return self::getSaida("A data de saída não pode ser anterior a data atual!");
        }

        if ($dataAtual == $dataSaida)
        {
            if ($horaAtual > $horaSaida)
            {
                return self::getSaida("O horário de saída deve ser posterior ao horário atual!");
            }
        }

        if ($horaSaida >= $horaChegada && $dataSaida == $dataChegada)
        {
            return self::getSaida("O horário de chegada deve ser posterior ao horário de saída!");
        }

        if ($dataSaida > $dataChegada)
        {
            return self::getSaida("A data de chegada não pode ser anterior a data de saída!");
        }

        // INICIALIZA A SESSÃO
        \App\Session\Login::init();
        
        // VERIFICA SE O ALUNO JÁ POSSUI UMA ASSINATURA ATIVA
        $ob = Entitysaida::getSignatureByStudent($_SESSION['user']['usuario']['id']);

        if (!empty($ob))
        {
            foreach ($ob as $item)
            {
                if ($item->ativa)
                {
                    return self::getSaida("O aluno já possui uma assinatura ativa nesta lista!");
                }
            }
        }

        // CADASTRA A ASSINATURA
        $obList = new EntitySaida(0, $_SESSION['user']['usuario']['id'], null, true, $destino, $dataSaida, $dataChegada, $horaSaida, $horaChegada);
        $obList->cadastrar();

        // RETORNA A VIEW
        return self::getSaida("Assinatura registrada!", true);
    }
}

?>