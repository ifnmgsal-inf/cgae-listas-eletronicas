<?php

namespace App\Http\Middlewares;

use \App\Model\Entity\Listas;

/**
 * Middleware para a atualização automática de assinaturas
 */
class UpdateLists
{
    /**
     * Executa o middleware
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle($request, $next)
    {
        $lists = $this->getActiveLists();

        if (!is_null($lists))
        {
            $this->verifyLists($lists);
        }

        return $next($request);
    }

    /**
     * Recupera as assinaturas ativas
     * @return array|null
     */
    public function getActiveLists()
    {
        // RECUPERA AS ASSINATURAS ATIVAS
        $lists['vai_volta'] = Listas\VaiVolta::processData(Listas\VaiVolta::getSignatures("ativa = true"));
        $lists['pernoite'] = Listas\Pernoite::processData(Listas\Pernoite::getSignatures("ativa = true"));
        $lists['saida'] = Listas\Saida::processData(Listas\Saida::getSignatures("ativa = true"));
        
        if (empty($lists['vai_volta']) && empty($lists['pernoite']) && empty($lists['saida']))
        {
            return null;
        }

        return $lists;
    }

    /**
     * Verifica a situação das listas ativas
     * @param array $lists
     */
    public function verifyLists($lists)
    {
        date_default_timezone_set("America/Sao_Paulo");
        $horaAtual = date("H:i:s", time());
        $dataAtual = date("Y-m-d", time());

        foreach ($lists as $list => $itens)
        {
            $ids = [];

            foreach ($itens as $item)
            {
                $data = $list == "vai_volta" ? $item->data : $item->dataChegada;
                $horaChegada = $item->horaChegada;

                if ($data < $dataAtual)
                {
                    $ids[] = $item->id;
                    continue;
                }

                else if ($data == $dataAtual)
                {
                    if ($horaChegada < $horaAtual)
                    {
                        $ids[] = $item->id;
                        continue;
                    }
                }
            }

            switch ($list)
            {
                case "vai_volta":
                    foreach ($ids as $id)
                    {
                        Listas\VaiVolta::atualizarAssinaturas("id = ".$id, [
                            "ativa" => false
                        ]);
                    }

                    break;

                case "pernoite":
                    foreach ($ids as $id)
                    {
                        Listas\Pernoite::atualizarAssinaturas("id = ".$id, [
                            "ativa" => false
                        ]);
                    }

                    break;

                case "saida":
                    foreach ($ids as $id)
                    {
                        Listas\Saida::atualizarAssinaturas("id = ".$id, [
                            "ativa" => false
                        ]);
                    }

                    break;
            }
        }
    }
}

?>