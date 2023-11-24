<?php

namespace App\Utils\Database;

/**
 * Classe responsável por administrar a paginação dos resultados de consulta ao banco de dados
 */
class Pagination
{
    /**
     * Limite de registros por página
     * @var int
     */
    private $limit;

    /**
     * Quantidade de resultados do banco
     * @var int
     */
    private $results;

    /**
     * Quantidade de páginas
     * @var int
     */
    private $pages;

    /**
     * Página atual
     * @var int
     */
    private $currentPage;

    /**
     * Construtor da classe
     * @param int $results
     * @param int $curentPage
     * @param int $limit
     */
    public function __construct($results, $currentPage = 1, $limit = 10)
    {
        $this->results = $results;
        $this->limit = $limit;
        $this->currentPage = (is_numeric($currentPage) && $currentPage > 0) ? $currentPage : 0;
        $this->calculate();
    }

    /**
     * Calcula a paginação
     */
    private function calculate()
    {
        $this->pages = $this->results > 0 ? ceil($this->results / $this->limit) : 1;
        $this->currentPage = $this->currentPage <= $this->pages ? $this->currentPage : $this->pages; 
    }

    /**
     * Retorna o limite de registros por página
     * @return string O limite de resultados por página
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Retorna as opções de páginas disponíveis
     * @return array Array de páginas disponíveis
     */
    public function getPages()
    {
        if ($this->pages == 1)
        {
            return [];
        }

        $pages = [];

        for ($i = 0; $i <= $this->pages; $i++)
        {
            $pages[] = [
                "page" => $i,
                "current" => $i == $this->currentPage
            ];
        }

        return $pages;
    }
}

?>