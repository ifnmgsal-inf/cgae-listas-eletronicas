<?php

namespace App\Model\Entity;

use App\Utils\Database\Database;

/**
 * Classe correspondente a tabela aluno
 */
class Aluno
{
    /**
     * ID do aluno
     * @var int
     */
    public $id;

    /**
     * Nome do aluno
     * @var string
     */
    public $nome;

    /**
     * Sexo do aluno
     * @var string
     */
    public $sexo;

    /**
     * Email do aluno
     * @var string
     */
    public $email;

    /**
     * Quarto do aluno
     * @var int
     */
    public $quarto;

    /**
     * Cama do aluno
     * @var int
     */
    public $cama;

    /**
     * Série do aluno
     * @var int
     */
    public $serie;

    /**
     * ID do refeitótio do aluno
     * @var int
     */
    public $idRefeitorio;

    /**
     * Senha de acesso do aluno
     * @var string
     */
    public $senha;

    /**
     * Define se o usuário pode assinar a lista de pernoite
     * @var bool
     */
    public $pernoite;

    /**
     * Nome do responsável pelo aluno
     * @var string
     */
    public $nomeResponsavel;

    /**
     * Cidade de origem do aluno
     * @var string
     */
    public $cidade;

    /**
     * Contato do responsável pelo aluno
     * @var string
     */
    public $telefoneResponsavel;

    /**
     * Define se o aluno ainda está ativo no sistema
     * @var bool
     */
    public $ativo;

    /**
     * Construtor da classe
     * @param int $id
     * @param string $nome
     * @param string $sexo
     * @param string $email
     * @param int $quarto
     * @param int $serie
     * @param int $idRefeitorio
     * @param string $senha
     * @param bool $pernoite
     * @param string $nomeResponsavel
     * @param string $cidade
     * @param string $telefoneResponsavel
     * @param bool $ativo
     */
    public function __construct($id = -1, $nome = null, $sexo = null, $email = null, $quarto = -1, $cama = -1, $serie = -1, $idRefeitorio = -1, $senha = null, $pernoite = false, $nomeResponsavel = null, $cidade = null, $telefoneResponsavel = null, $ativo = false)
    {
        $this->id = $id;
        $this->nome = $nome;
        $this->sexo = $sexo;
        $this->email = $email;
        $this->quarto = $quarto;
        $this->cama = $cama;
        $this->serie = $serie;
        $this->idRefeitorio = $idRefeitorio;
        $this->senha = $senha;
        $this->pernoite = $pernoite;
        $this->nomeResponsavel = $nomeResponsavel;
        $this->telefoneResponsavel = $telefoneResponsavel;
        $this->cidade = $cidade;
        $this->ativo = $ativo;
    }

    /**
     * Retorna um aluno com base no seu email
     * @param string $email Email a ser procurado
     * @return Aluno|null Instância de Aluno
     */
    public static function getAlunoByEmail($email)
    {
        return self::processData(self::getAlunos("email = '".$email."'"))[0] ?? null;
    }

    /**
     * Retorna um aluno com base no seu id
     * @param int $id ID a ser procurado
     * @return Aluno|null Instância de Aluno
     */
    public static function getAlunoById($id)
    {
        return self::processData(self::getAlunos("id = ".$id))[0] ?? null;
    }

    /**
     * Retorna um aluno com base no seu id
     * @param int $id ID a ser procurado
     * @return Aluno|null Instância de Aluno
     */
    public static function getAlunoByIdRefeitorio($id)
    {
        return self::processData(self::getAlunos("id_refeitorio = ".$id))[0] ?? null;
    }

    /**
     * Retorna alunos
     * @param string $where Condição de busca
     * @param string $order Ordem dos resultados
     * @param string $limit Limite de resultados
     * @param string $field Campos a serem retornados
     * @return \PDOStatement Resultados da busca
     */
    public static function getAlunos($where = null, $order = null, $limit = null, $field = "*")
    {
        return (new Database("aluno"))->select($where, $order, $limit, $field);
    }

    /**
     * Cadastra o aluno atual no banco de dados
     * @return bool
     */
    public function cadastrar()
    {
        $this->id = (new Database("aluno"))->insert([
            "nome" => $this->nome,
            "sexo" => $this->sexo,
            "email" => $this->email,
            "quarto" => $this->quarto,
            "cama" => $this->cama,
            "serie" => $this->serie,
            "id_refeitorio" => $this->idRefeitorio,
            "senha" => $this->senha,
            "responsavel" => $this->nomeResponsavel,
            "telefone_responsavel" => $this->telefoneResponsavel,
            "cidade" => $this->cidade,
            "pernoite" => $this->pernoite,
            "ativo" => $this->ativo
        ]);

        return true;
    }

    /**
     * Atualiza os dados do banco
     * @return bool
     */
    public function atualizar()
    {
        return (new Database("aluno"))->update("id = '".$this->id."'", [
            "nome" => $this->nome,
            "sexo" => $this->sexo,
            "email" => $this->email,
            "quarto" => $this->quarto,
            "cama" => $this->cama,
            "serie" => $this->serie,
            "id_refeitorio" => $this->idRefeitorio,
            "senha" => $this->senha,
            "responsavel" => $this->nomeResponsavel,
            "telefone_responsavel" => $this->telefoneResponsavel,
            "cidade" => $this->cidade,
            "pernoite" => $this->pernoite,
            "ativo" => $this->ativo
        ]);
    }

    /**
     * Exclui o aluno do banco
     * @return bool
     */
    public function excluir()
    {
        return (new Database("aluno"))->delete("id = ".$this->id);
    }
    
    /**
     * Processa dados PDOStatement para instâncias de Aluno
     * @param \PDOStatement Resultado de uma query
     * @return array|null Array de instâncias de Aluno
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

        // INSTÂNCIA OS OBJETOS DE ALUNO
        $itens = [];

        foreach ($results as $result)
        {
            $itens[] = new self($result['id'], $result['nome'], $result['sexo'], $result['email'], $result['quarto'], $result['cama'], $result['serie'], $result['id_refeitorio'], $result['senha'], $result['pernoite'], $result['responsavel'], $result['cidade'], $result['telefone_responsavel'], $result['ativo']);
        }

        return $itens;
    }
}

?>