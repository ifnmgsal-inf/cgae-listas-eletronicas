<?php

namespace App\Controller\Student\Lists;

use App\Controller\Student\Page;
use App\Controller\Common\Alert;
use App\Model\Entity\Listas\VaiVolta as EntityVaiVolta;

/**
 * Controlador da página da lista de vai e volta (aluno)
 */
class VaiVolta extends Page
{
    /**
     * Retorna a view da lista vai e volta
     * @param string $message Texto da mensagem
     * @param bool $success Indica se a mensagem corresponde a um processo bem ou mal sucedido
     * @return string View renderizada
     */
    public static function getVaiVolta($message = null, $success = false)
    {
        // CONFIGURA A NAVBAR
        parent::setActiveModule("listas");

        $content = parent::render("lists/vai_volta", [
            "status" => !is_null($message) ? (!$success ? Alert::getError($message) : Alert::getSuccess($message)) : ""
        ]);

        return parent::getPage("Listas | Vai e Volta", $content);
    }

    /**
     * Cadastra a assinatura
     * @param Request $request Objeto da requisição
     * @return string View renderizada
     */
    public static function setVaiVolta($request)
    {
        // RECUPERA AS VARIÁVEIS DE POST
        $postVars = $request->getPostVars();

        $destino = $postVars['destino'];
        $data = $postVars['data'];
        $horaSaida = $postVars['hora_saida'].":00";
        $horaChegada = $postVars['hora_chegada'].":00";

        // OBTÉM A DATA E HORA ATUAIS
        date_default_timezone_set("America/Sao_Paulo");
        $dataAtual = date("Y-m-d", time());
        $horaAtual = date("H:i:s", time() + 60);

        // VERIFICA SE OS DADOS DA ASSINATURA SÃO VÁLIDOS

        if ($dataAtual > $data)
        {
            return self::getVaiVolta("A data de saída não pode ser anterior a data atual!");
        }

        if (!("05:00:00" <= $horaSaida && $horaSaida <= "23:00:00"))
        {
            return self::getVaiVolta("O horário de saída deve estar compreendido entre as 05:00 e as 23:00 horas!");
        }

        if (!("05:00:00" <= $horaChegada && $horaChegada <= "23:00:00"))
        {
            return self::getVaiVolta("O horário de chegada deve estar compreendido entre as 05:00 e as 23:00 horas!");
        }

        if ($dataAtual == $data)
        {
            if ($horaAtual > $horaSaida)
            {
                return self::getVaiVolta("O horário de saída deve ser posteior ao horário atual!");
            }
        }

        if ($horaSaida >= $horaChegada)
        {
            return self::getVaiVolta("O horário de chegada deve ser posteior ao horário de saída!");
        }

        // INICIALIZA A SESSÃO
        \App\Session\Login::init();
        
        // VERIFICA SE O ALUNO JÁ POSSUI UMA ASSINATURA ATIVA
        $ob = EntityVaiVolta::getSignatureByStudent($_SESSION['user']['usuario']['id']);

        if (!empty($ob))
        {
            foreach ($ob as $item)
            {
                if ($item->ativa)
                {
                    return self::getVaiVolta("O aluno já possui uma assinatura cadastrada nesta lista!");
                }
            }
        }

        // CADASTRA A ASSINATURA
        $obList = new EntityVaiVolta(0, $_SESSION['user']['usuario']['id'], null, true, $destino, $data, $horaSaida, $horaChegada);
        $obList->cadastrar();

        // RETORNA A VIEW
        return self::getVaiVolta("Assinatura registrada!", true);
    }
}

?>