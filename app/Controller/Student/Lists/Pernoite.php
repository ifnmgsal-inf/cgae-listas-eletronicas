<?php

namespace App\Controller\Student\Lists;

use App\Controller\Common\Alert;
use App\Controller\Student\Page;
use App\Model\Entity\Listas\Pernoite as EntityPernoite;

/**
 * Controlador da página da lista de pernoite (aluno)
 */
class Pernoite extends Page
{
    /**
     * Retorna a view da lista de pernoite
     * @param string $message Texto da mensagem de status
     * @param bool $success Indica se a mensagem corresponde a um processo bem ou mal sucedido
     * @return string View renderizada
     */
    public static function getPernoite($message = null, $success = false)
    {
        // CONFIGURA A NAVBAR
        parent::setActiveModule("listas");

        // RENDERIZA A VIEW
        $content = parent::render("lists/pernoite", [
            "status" => !is_null($message) ? (!$success ? Alert::getError($message) : Alert::getSuccess($message)) : ""
        ]);

        return parent::getPage("Listas | Pernoite", $content);
    }

    /**
     * Cadastra a assinatura no banco
     * @param Request $request Objeto de requisição
     * @return string View renderizada
     */
    public static function setPernoite($request)
    {
        // INICIALIZA A SESSÃO
        \App\Session\Login::init();

        // OBTÉM OS DADOS DO ALUNO
        $ob = \App\Model\Entity\Aluno::getAlunoById($_SESSION['user']['usuario']['id']);

        // VERIFICA SE O ALUNO POSSUI PERMISSÃO PARA REALIZAR A ASSINATURA
        if (!$ob->pernoite)
        {
            return self::getPernoite("O aluno não possui permissão para assinar a lista de pernoite!");
        }

        // OBTÉM AS VARIÁVEIS DE POST
        $postVars = $request->getPostVars();

        $endereco = $postVars['endereco'];
        $nomeResponsavel = $postVars['nome_responsavel'];
        $telefone = trim($postVars['telefone']);
        $dataSaida = $postVars['data_saida'];
        $dataChegada = $postVars['data_chegada'];
        $horaSaida = $postVars['hora_saida'].":00";
        $horaChegada = $postVars['hora_chegada'].":00";

        // OBTÉM A DATA E HORA ATUAL
        date_default_timezone_set("America/Sao_Paulo");
        $dataAtual = date("Y-m-d", time());
        $horaAtual = date("H:i:s", time() + 60);

        // VERIFICA SE OS DADOS DA ASSINATURA SÃO VÁLIDOS
        if (!("05:00:00" <= $horaSaida && $horaSaida <= "23:00:00"))
        {
            return self::getPernoite("O horário de saída deve estar compreendido entre 05:00 e 23:00 horas!");
        }

        if (!("05:00:00" <= $horaChegada && $horaChegada <= "23:00:00"))
        {
            return self::getPernoite("O horário de chegada deve estar compreendido entre 05:00 e 23:00 horas!");
        }

        if ($dataAtual > $dataSaida)
        {
            return self::getPernoite("A data de saída não pode ser anterior a data atual!");
        }

        if ($dataAtual == $dataSaida)
        {
            if ($horaAtual > $horaSaida)
            {
                return self::getPernoite("O horário de saída deve ser posterior ao horário atual!");
            }
        }

        if ($dataSaida >= $dataChegada)
        {
            return self::getPernoite("A data de chegada deve ser posterior a data de saída!");
        }

        // VERIFICA SE O ALUNO JÁ POSSUI UMA ASSINATURA EM ABERTO
        $ob = EntityPernoite::getSignatureByStudent($_SESSION['user']['usuario']['id']);

        if (!empty($ob))
        {
            foreach ($ob as $item)
            {
                if ($item->ativa)
                {
                    return self::getPernoite("O aluno já possui uma assinatura ativa nesta lista!");
                }
            }
        }

        // CADASTRA A ASSINATURA
        $obList = new EntityPernoite(0, $_SESSION['user']['usuario']['id'], null, true, $endereco, $nomeResponsavel, $telefone, $dataSaida, $dataChegada, $horaSaida, $horaChegada);
        $obList->cadastrar();

        // RETORNA A VIEW DA PÁGINA
        return self::getPernoite("Assinatura registrada!", true);
    }
}

?>