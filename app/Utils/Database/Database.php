<?php

namespace App\Utils\Database;

/**
 * Classe de gerenciamento e manipulação de banco de dados
 */
class Database
{
    /**
     * Host de conexão com o banco de dados
     * @var string
     */
    private static $host;

    /**
     * Nome do banco de dados
     * @var string
     */
    private static $name;

    /**
     * Usuário do banco
     * @var string
     */
    private static $user;

    /**
     * Senha de acesso ao banco de dados
     * @var string
     */
    private static $pass;

    /**
     * Porta de acesso ao banco
     * @var integer
     */
    private static $port;

    /**
     * Nome da tabela a ser manipulada
     * @var string
     */
    private $table;

    /**
     * Instancia de conexão com o banco de dados
     * @var \PDO
     */
    private $connection;

    /**
     * Configura a classe
     * @param string $host
     * @param string $name
     * @param string $user
     * @param string $pass
     * @param integer $port
     */
    public static function config($host, $name, $user, $pass, $port = 3306)
    {
        self::$host = $host;
        self::$name = $name;
        self::$user = $user;
        self::$pass = $pass;
        self::$port = $port;
    }

    /**
     * Construtor da classe
     * @param string $table Nome da tabela a ser manipulada
     */
    public function __construct($table = null)
    {
        $this->table = $table;
        $this->setConnection();
    }

    /**
     * Cria o objeto de conexão com o banco de dados
     */
    private function setConnection()
    {
        $this->connection = new \PDO("mysql:host=".self::$host.";dbname=".self::$name.";port=".self::$port, self::$user, self::$pass);
        $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Método responsável por executar query's dentro do banco de dados
     * @param string $query SQL query a ser executada
     * @param array $params Parâmetros da query
     * @return \PDOStatement Resultado da query
     */
    public function execute($query, $params = [])
    {
        $stmt = $this->connection->prepare($query);
        $stmt->execute($params);

        return $stmt;
    }

    /**
     * Insere dados no banco
     * @param array $values [campo => valor] - Valores e seus campos correspondentes
     * @return int ID da instância inserida
     */
    public function insert($values)
    {
        $fields = array_keys($values);
        $binds = array_pad([], count($fields), "?");

        $query = "INSERT INTO ".$this->table." (".implode(",", $fields).") VALUES (".implode(",", $binds).")";
        $this->execute($query, array_values($values));

        return $this->connection->lastInsertId();
    }

    /**
     * Executa uma consulta ao banco de dados
     * @param  string $where Condição de busca
     * @param  string $order Ordem dos resultados
     * @param  string $limit Limite de resultados
     * @param  string $fields Campos a serem retornados
     * @return \PDOStatement Resultado da busca
     */
    public function select($where = null, $order = null, $limit = null, $fields = "*")
    {
        $where = strlen($where) ? "WHERE ".$where : "";
        $order = strlen($order) ? "ORDER BY ".$order : "";
        $limit = strlen($limit) ? "LIMIT ".$limit : "";

        //MONTA A QUERY
        $query = "SELECT ".$fields." FROM ".$this->table." ".$where." ".$order." ".$limit;
        
        //EXECUTA A QUERY
        return $this->execute($query);
    }

    /**
     * Atualiza o banco de dados
     * @param string $where Condição de atualização
     * @param array $values [campo => valor] - Valores e seus campos correspondentes
     * @return bool
     */
    public function update($where, $values)
    {
        $fields = array_keys($values);

        $query = "UPDATE ".$this->table." SET ".implode("= ?, ", $fields)."= ? WHERE ".$where;

        $this->execute($query, array_values($values));

        return true;
    }

    /**
     * Exclui dados do banco
     * @param string $where Condição de exclusão
     * @return bool
     */
    public function delete($where)
    {
        $query = "DELETE FROM ".$this->table." WHERE ".$where;
        
        $this->execute($query);

        return true;
    }
}