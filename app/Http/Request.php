<?php

namespace App\Http;

class Request
{
    /**
     * Instância do Router
     * @var Router
     */
    private $router;

    /**
     * Método http da requisição
     * @var string
     */
    private $httpMethod;

    /**
     * URI da página
     * @var string
     */
    private $uri;

    /**
     * Parâmetros da URL ($_GET)
     * @var array
     */
    private $queryParams = [];

    /**
     * Variáveis recebidas no POST da página ($_POST)
     * @var array
     */
    private $postVars = [];

    /**
     * Cabeçalhos da requisição
     * @var array
     */
    private $headers = [];

    /**
     * Construtor da classe
     * @param Router $router
     */
    public function __construct($router)
    {
        $this->router      = $router;
        $this->queryParams = $_GET ?? [];
        $this->headers     = getallheaders();
        $this->httpMethod  = $_SERVER["REQUEST_METHOD"] ?? "";
        $this->setUri();
        $this->setPostVars();
    }

    /**
     * Define as variáveis do POST
     */
    private function setPostVars()
    {
        if ($this->httpMethod == "GET")
        {
            return;
        }

        $this->postVars = $_POST ?? [];
    }

    /**
     * Define a URI
     */
    private function setUri()
    {
        $this->uri = $_SERVER["REQUEST_URI"] ?? "";
        $xUri = explode("?", $this->uri);
        $this->uri = $xUri[0];
    }

    /**
     * Retorna a instância de router
     * @return string
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * Retorna o método http da requisição
     * @return string
     */
    public function getHttpMethod()
    {
        return $this->httpMethod;
    }

    /**
     * Retorna a URI da requisição
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Retorna os headers da requisição
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Retorna os parâmetros da URL da requisição
     * @return array
     */
    public function getQueryParams()
    {
        return $this->queryParams;
    }

    /**
     * Retorna as variáveis POST da requisição
     * @return array
     */
    public function getPostVars()
    {
        return $this->postVars;
    }
}

?>