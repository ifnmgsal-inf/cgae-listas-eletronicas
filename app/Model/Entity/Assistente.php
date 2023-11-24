<?php

namespace App\Model\Entity;

use App\Utils\Database\Database;

/**
 * Classe correspondente a tabela assistente
 */
class Assistente
{
    /**
     * ID do assistente
     * @var int
     */
    public $id;

    /**
     * Nome do assistente
     * @var string
     */
    public $nome;

    /**
     * Email do assistente
     * @var string
     */
    public $email;

    /**
     * Senha de acesso do assistente
     * @var string
     */
    public $senha;

    /**
     * Construtor da classe
     * @param int $id
     * @param string $nome
     * @param string $email
     * @param string $senha
     */
    public function __construct($id = -1, $nome = null, $email = null, $senha = null)
    {
        $this->id = $id;
        $this->nome = $nome;
        $this->email = $email;
        $this->senha = $senha;
    }

    /**
     * Retorna um assistente com base no seu email
     * @param string $email Email a ser procurado
     * @return Assistente|null Instância do assistente
     */
    public static function getAssistenteByEmail($email)
    {
        return self::processData(self::getAssistentes("email = '".$email."'"))[0] ?? null;
    }

    /**
     * Retorna um assistente com base no seu id
     * @param int $id ID a ser procurado
     * @return Assistente|null Instância de Assistente
     */
    public static function getAssistenteById($id)
    {
        return self::processData(self::getAssistentes("id = ".$id))[0] ?? null;
    }

    /**
     * Retorna assitentes
     * @param string $where Condição de busca
     * @param string $order Ordem dos resultados
     * @param string $limit Limite de resultados
     * @param string $field Campos a serem retornados
     * @return \PDOStatement Resultados da busca
     */
    public static function getassistentes($where = null, $order = null, $limit = null, $field = "*")
    {
        return (new Database("assistente"))->select($where, $order, $limit, $field);
    }

    /**
     * Cadastra o assistente atual no banco de dados
     * @return bool
     */
    public function cadastrar()
    {
        $this->id = (new Database("assistente"))->insert([
            "nome" => $this->nome,
            "email" => $this->email,
            "senha" => $this->senha,
        ]);

        return true;
    }

    /**
     * Atualiza os dados do banco
     * @return bool
     */
    public function atualizar()
    {
        return (new Database("assistente"))->update("id = '".$this->id."'", [
            "nome" => $this->nome,
            "email" => $this->email,
            "senha" => $this->senha
        ]);
    }

    /**
     * Exclui o assistente do banco
     * @return bool
     */
    public function excluir()
    {
        return (new Database("assistente"))->delete("id = ".$this->id);
    }
    
    /**
     * Processa dados PDOStatement para instâncias de assistente
     * @param \PDOStatement Resultado de uma query
     * @return array|null Array de instâncias de Assistente
     */
    public static function processData($data)
    {
        // TRANSFORMA OS RESULTADOS EM UM ARRAY
        $results = $data->fetchAll();

        // VERIFICA SE EXISTE ALGUM RESULTADO
        if (empty($results))
        {
            return null;
        }

        // INSTÂNCIA OS OBJETOS DE ASSISTENTE
        $itens = [];

        foreach ($results as $result)
        {
            $itens[] = new self($result['id'], $result['nome'], $result['email'], $result['senha']);
        }

        return $itens;
    }
}

?>