<?php

namespace App\Model\Entity\Listas;

use \App\Utils\Database\Database;

/**
 * Correspondente a tabela vai_volta
 */
class VaiVolta
{
    /**
     * ID da assinatura
     * @var int
     */
    public int $id;

    /**
     * ID do aluno assinante da lista
     * @var int
     */
    public int $aluno;

    /**
     * @var int|null
     */
    public $pai;

    /**
     * Indica se a assinatura está ativa
     * @var bool
     */
    public bool $ativa;

    /**
     * Local de destino do aluno
     * @var string
     */
    public string $destino;

    /**
     * Data da saída do aluno
     * @var string
     */
    public string $data;

    /**
     * Horário de saída do aluno
     * @var string
     */
    public string $horaSaida;

    /**
     * Horário de chegada do aluno
     * @var string
     */
    public string $horaChegada;

    /**
     * Construtor da classe
     * @param int $id
     * @param int $aluno
     * @param bool $ativa
     * @param string $destino
     * @param string $data
     * @param string $horaSaida
     * @param string $horaChegada
     */
    public function __construct($id = -1, $aluno = -1, $pai = null, $ativa = true, $destino = null, $data = null, $horaSaida = null, $horaChegada = null)
    {
        $this->id = $id;
        $this->aluno = $aluno;
        $this->pai = $pai;
        $this->ativa = $ativa;
        $this->destino = $destino;
        $this->data = $data;
        $this->horaSaida = $horaSaida;
        $this->horaChegada = $horaChegada;
    }

    /**
     * Cadastra a assinatura atual no banco de dados
     * @return bool
     */
    public function cadastrar()
    {
        $this->id = (new Database("vai_volta"))->insert([
            "aluno" => $this->aluno,
            "pai" => $this->pai,
            "destino" => $this->destino,
            "data" => $this->data,
            "hora_saida" => $this->horaSaida,
            "hora_chegada" => $this->horaChegada,
        ]);

        return true;
    }

    /**
     * Atualiza as assinaturas
     * @param array $values [campo => valor]
     * @return bool
     */
    public function atualizar($values)
    {
        return (new Database('vai_volta'))->update("id = ".$this->id, $values);
    }
    
    /**
     * Atualiza as assinaturas
     * @param string $where Condição para atualização
     * @param array $values [campo => valor]
     * @return bool
     */
    public static function atualizarAssinaturas($where, $values)
    {
        return (new Database('vai_volta'))->update($where, $values);
    }

    /**
     * Exclui a assinatura atual
     * @return bool
     */
    public function excluir()
    {
        return (new Database('vai_volta'))->delete("id = ".$this->id);
    }

    /**
     * Retorna uma assinatura com base no seu id
     * @param int $id ID a ser procurado
     * @return VaiVolta|null Instância da assinatura
     */
    public static function getSignatureById($id)
    {
        return self::processData(self::getSignatures("id = ".$id))[0] ?? null;
    }

    /**
     * Retorna uma assinatura com base no aluno a assinou
     * @param int $id ID a ser procurado
     * @return array Array de instâncias de assinatura
     */
    public static function getSignatureByStudent($id)
    {
        return self::processData(self::getSignatures("aluno = ".$id));
    }
    
    public static function getSignatureByFather($id)
    {
        return self::processData(self::getSignatures("pai = ".$id))[0] ?? null;
    }

    /**
     * Retorna assinaturas
     * @param string $where Condição de procura
     * @param string $order Ordem dos resultados
     * @param string $limit Limite de resultados
     * @param string $fields Campos a serem retornados
     * @return \PDOStatement Resultado da busca
     */
    public static function getSignatures($where = null, $order = null, $limit = null, $fields = "*")
    {
        return (new Database("vai_volta"))->select($where, $order, $limit, $fields);
    }

    /**
     * Processa dados PDOStatement para instâncias de VaiVolta
     * @param \PDOStatement $data Resultado de uma query
     * @return array Array de instâncias de assinatura
     */
    public static function processData($data)
    {
        // TRANSFORMA OS RESULTADOS EM UM ARRAY
        $results = $data->fetchAll();

        // VERIFICA SE ALGUM RESULTADO FOI ENCONTRADO
        if (empty($results))
        {
            return [];
        }

        // INSTÂNCIA OS OBJETOS DE ASSINATURA
        $itens = [];

        foreach ($results as $result)
        {
            $itens[] = new self($result['id'], $result['aluno'], $result['pai'], $result['ativa'], $result['destino'], $result['data'], $result['hora_saida'], $result['hora_chegada']);
        }

        return $itens;
    }
}