<?php

namespace App\Controller\Assistant\Signatures;

use App\Model\Entity\Listas;

/**
 * Controlador da página de ações da assinatura
 */
class Acao
{
    /**
     * Entrypoint POST da rota
     * @param Request $request Objeto de requisição
     * @param string $type Lista da assinatura
     * @param int $id ID da assinatura
     */
    public static function setView($request, $type, $id)
    {
        try 
        {
            $acao = $request->getPostVars()['acao'];
        }

        catch (\Exception)
        {
            $request->getRouter()->redirect("/ass/listas/".$type."/".$id);
        }

        switch ($acao)
        {
            case "excluir":
                switch ($type)
                {
                    case "vai_volta":
                        Listas\VaiVolta::getSignatureById($id)->excluir();
                        break;

                    case "saida":
                        Listas\Saida::getSignatureById($id)->excluir();
                        break;

                    case "pernoite":
                        Listas\Pernoite::getSignatureById($id)->excluir();
                        break;
                }

                $request->getRouter()->redirect("/ass/listas");

                break;

            case "encerrar":
                // OBTÉM A DATA E HORA ATUAL
                date_default_timezone_set("America/Sao_Paulo");
                $dataAtual = date("Y-m-d", time());
                $horaAtual = date("H:i:s", time() + 60);

                // ATUALIZA A ASSINATURA
                switch ($type)
                {
                    case "vai_volta":
                        $ob = Listas\VaiVolta::getSignatureById($id);

                        if ($ob->data == $dataAtual)
                        {
                            if ($ob->horaSaida > $horaAtual)
                            {
                                $ob->horaSaida = $horaAtual;
                            }
                        }

                        else if ($ob->data > $dataAtual)
                        {
                            $ob->data = $dataAtual;
                            $ob->horaSaida = $horaAtual;
                        }

                        $ob->horaChegada = $horaAtual;

                        $ob->atualizar([
                            "data" => $ob->data,
                            "hora_saida" => $ob->horaSaida,
                            "hora_chegada" => $ob->horaChegada,
                            "ativa" => false
                        ]);

                        break;

                    case "saida":
                        $ob = Listas\Saida::getSignatureById($id);

                        if ($ob->dataSaida == $dataAtual)
                        {
                            if ($ob->horaSaida > $horaAtual)
                            {
                                $ob->horaSaida = $horaAtual;
                            }
                        }

                        else if ($ob->dataSaida > $dataAtual)
                        {
                            $ob->dataSaida = $dataAtual;
                            $ob->horaSaida = $horaAtual;
                        }

                        $ob->horaChegada = $horaAtual;
                        $ob->dataChegada = $dataAtual;

                        $ob->atualizar([
                            "data_saida" => $ob->dataSaida,
                            "data_chegada" => $ob->dataChegada,
                            "hora_saida" => $ob->horaSaida,
                            "hora_chegada" => $ob->horaChegada,
                            "ativa" => false
                        ]);

                        break;

                    case "pernoite":
                        $ob = Listas\Pernoite::getSignatureById($id);

                        if ($ob->dataSaida == $dataAtual)
                        {
                            if ($ob->horaSaida > $horaAtual)
                            {
                                $ob->horaSaida = $horaAtual;
                            }
                        }

                        else if ($ob->dataSaida > $dataAtual)
                        {
                            $ob->dataSaida = $dataAtual;
                            $ob->horaSaida = $horaAtual;
                        }

                        $ob->horaChegada = $horaAtual;
                        $ob->dataChegada = $dataAtual;

                        $ob->atualizar([
                            "data_saida" => $ob->dataSaida,
                            "data_chegada" => $ob->dataChegada,
                            "hora_saida" => $ob->horaSaida,
                            "hora_chegada" => $ob->horaChegada,
                            "ativa" => false
                        ]);

                        break;
                }

                $request->getRouter()->redirect("/ass/listas/".$type."/".$id);

                break;

            default:
                $request->getRouter()->redirect("/ass/listas/".$type."/".$id);

                break;
        }
    }
}

?>