<?php

namespace App\Controller\Assistant\Signatures;

use App\Controller\Common\Alert;
use App\Controller\Assistant\Page;
use App\Model\Entity\Aluno;
use App\Model\Entity\Listas;

/**
 * Controlador da página de cadastrar assinatura
 */
class NewSignature extends Page
{
    /**
     * Entrypoint GET da rota
     * @param string $name Lista da assinatura
     * @param string $message Mensagem de status
     * @param bool $success Tipo da mensagem de status
     * @return string View renderizada
     */
    public static function getView($name = "null", $message = null, $success = false)
    {
        parent::setActiveModule("signatures");

        $content = parent::render("signature/new/index", [
            "name" => $name,
            "status" => is_null($message) ? "" : ($success ? Alert::getSuccess($message) : Alert::getError($message))
        ]);

        return parent::getPage("Assinatura", $content);
    }

    /**
     * Entrypoint POST da rora
     * @param Request $request Objeto de requisição
     * @return string View renderizada
     */
    public static function setView($request)
    {
        $postVars = $request->getPostVars();
        $message = null;
        $success = false;
        $pass = true;

        $aluno = $postVars['aluno'];
        $obAluno = Aluno::getAlunoByIdRefeitorio($aluno);

        if ($obAluno == null)
        {
            $message = "Aluno não encontrado!";
        }

        else
        {
            $hourFinal = "23:00:00";
            $hourInitial = "05:00:00";

            switch ($postVars['type'])
            {
                case "vai_volta":
                    $destino = $postVars['destino'];
                    $data = $postVars['data'];
                    $horaSaida = $postVars['hora_saida'].(strlen($postVars['hora_saida']) == 5 ? ":00" : "");
                    $horaChegada = $postVars['hora_chegada'].(strlen($postVars['hora_chegada']) == 5 ? ":00" : "");

                    date_default_timezone_set("America/Sao_Paulo");
                    $dataAtual = date("Y-m-d", time());
                    $horaAtual = date("H:i:s", time() + 60);

                    $ob = Listas\VaiVolta::getSignatureByStudent($obAluno->id);

                    if (!empty($ob))
                    {
                        foreach ($ob as $item)
                        {
                            if ($item->ativa)
                            {
                                $message = "O aluno já possui uma assinatura ativa nesta lista!";
                                $pass = false;
                                break;
                            }
                        }
                    }
                    
                    if (!$pass)
                    {
                        break;
                    }

                    if (!($hourInitial <= $horaSaida && $horaSaida <= $hourFinal))
                    {
                        $message = "O horário de saída deve estar compreendido entre as 05:00 e as 23:00 horas!";
                    }

                    else if (!($hourInitial <= $horaChegada && $horaChegada <= $hourFinal))
                    {
                        $message = "O horário de chegada deve estar compreendido entre as 05:00 e as 23:00 horas!";
                    }

                    else if ($horaSaida >= $horaChegada)
                    {
                        $message = "O horário de chegada deve ser posteior ao horário de saída!";
                    }

                    else if ($dataAtual > $data)
                    {
                        $message = "A data de saída não pode ser anterior a data atual!";
                    }

                    else if ($dataAtual == $data && $horaAtual > $horaSaida)
                    {
                        $message = "O horário de saída deve ser posteior ao horário atual!";
                    }

                    else
                    {
                        $message = "Cadastrado com sucesso!";
                        $success = true;
                        $obList = new Listas\VaiVolta(0, $obAluno->id, null, true, $destino, $data, $horaSaida, $horaChegada);
                        $obList->cadastrar();
                    }

                    break;

                case "saida":
                    $destino = $postVars['destino'];
                    $dataSaida = $postVars['data_saida'];
                    $dataChegada = $postVars['data_chegada'];
                    $horaSaida = $postVars['hora_saida'].(strlen($postVars['hora_saida']) == 5 ? ":00" : "");
                    $horaChegada = $postVars['hora_chegada'].(strlen($postVars['hora_chegada']) == 5 ? ":00" : "");

                    date_default_timezone_set("America/Sao_Paulo");
                    $dataAtual = date("Y-m-d", time());
                    $horaAtual = date("H:i:s", time() + 60);

                    $ob = Listas\Saida::getSignatureByStudent($obAluno->id);

                    if (!empty($ob))
                    {
                        foreach ($ob as $item)
                        {
                            if ($item->ativa)
                            {
                                $message = "O aluno já possui uma assinatura ativa nesta lista!";
                                $pass = false;
                                break 2;
                            }
                        }
                    }

                    if (!$pass)
                    {
                        break;
                    }

                    if (!($hourInitial <= $horaSaida && $horaSaida <= $hourFinal))
                    {
                        $message = "O horário de saída deve estar compreendido entre as 05:00 e as 23:00 horas!";
                    }

                    else if (!($hourInitial <= $horaChegada && $horaChegada <= $hourFinal))
                    {
                        $message = "O horário de chegada deve estar compreendido entre as 05:00 e as 23:00 horas!";
                    }

                    else if ($dataSaida > $dataChegada)
                    {
                        $message = "A data de chegada não pode ser anterior a data de saída!";
                    }

                    else if ($horaSaida >= $horaChegada && $dataSaida == $dataChegada)
                    {
                        $message = "O horário de chegada deve ser posterior ao horário de saída!";
                    }

                    else if ($dataAtual > $dataSaida)
                    {
                        $message = "A data de saída não pode ser anterior a data atual!";
                    }

                    else if ($dataAtual == $dataSaida && $horaAtual > $horaSaida)
                    {
                        $message = "O horário de saída deve ser posterior ao horário atual!";
                    }

                    else
                    {
                        $message = "Cadastrado com sucesso!";
                        $success = true;
                        $obList = new Listas\Saida(0, $obAluno->id, null, true, $destino, $dataSaida, $dataChegada, $horaSaida, $horaChegada);

                        $obList->cadastrar();
                    }

                    break;

                case "pernoite":
                    $endereco = $postVars['endereco'];
                    $nomeResponsavel = $postVars['nome_responsavel'];
                    $telefone = trim($postVars['telefone']);
                    $dataSaida = $postVars['data_saida'];
                    $dataChegada = $postVars['data_chegada'];
                    $horaSaida = $postVars['hora_saida'].(strlen($postVars['hora_saida']) == 5 ? ":00" : "");
                    $horaChegada = $postVars['hora_chegada'].(strlen($postVars['hora_chegada']) == 5 ? ":00" : "");
            
                    date_default_timezone_set("America/Sao_Paulo");
                    $dataAtual = date("Y-m-d", time());
                    $horaAtual = date("H:i:s", time() + 60);
            
                    $ob = Listas\Pernoite::getSignatureByStudent($obAluno->id);

                    if (!empty($ob))
                    {
                        foreach ($ob as $item)
                        {
                            if ($item->ativa)
                            {
                                $message = "O aluno já possui uma assinatura ativa nesta lista!";
                                $pass = false;
                                break;
                            }
                        }
                    }

                    if (!$pass)
                    {
                        break;
                    }

                    if (!($hourInitial <= $horaSaida && $horaSaida <= $hourFinal))
                    {
                        $message = "O horário de saída deve estar compreendido entre 05:00 e 23:00 horas!";
                    }

                    else if (!($hourInitial <= $horaChegada && $horaChegada <= $hourFinal))
                    {
                        $message = "O horário de chegada deve estar compreendido entre 05:00 e 23:00 horas!";
                    }

                    else if ($dataSaida >= $dataChegada)
                    {
                        $message = "A data de chegada deve ser posterior a data de saída!";
                    }
            
                    else if ($dataAtual > $dataSaida)
                    {
                        $message = "A data de saída não pode ser anterior a data atual!";
                    }
            
                    else if ($dataAtual == $dataSaida && $horaAtual > $horaSaida)
                    {
                        $message = "O horário de saída deve ser posterior ao horário atual!";
                    }

                    else
                    {
                        $message = "Cadastrado com sucesso!";
                        $success = true;
                        $obList = new Listas\Pernoite(0, $obAluno->id, null, true, $endereco, $nomeResponsavel, $telefone, $dataSaida, $dataChegada, $horaSaida, $horaChegada);

                        $obList->cadastrar();
                    }

                    break;
            }
        }

        return self::getView($postVars['type'], $message, $success);
    }
}