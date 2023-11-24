<?php

namespace App\Http;

use App\Controller\Common\Error;

class Router
{
    /**
     * URL completa do sistema
     * @var string
     */
    private $url;

    /**
     * Prefixo de todas as rotas
     * @var string
     */
    private $prefix;

    /**
     * Índices de rotas
     * @var array
     */
    private $routes;

    /**
     * Instância de requisição
     * @var Request
     */
    private $request;

     /**
      * Content-Type padrão da resposta
      * @var string
      */
    private $contentType = "text/html";

    /**
     * Construtor da classe
     * @param string $url URL completa do sistema
     */
    public function __construct($url)
    {
        $this->request = new Request($this);
        $this->url = $url;
        $this->setPrefix();
    }

    /**
     * Define o prefixo das rotas
     */
    private function setPrefix()
    {
        $parseUrl = parse_url($this->url);
        $this->prefix = $parseUrl["path"] ?? "";
    }

    /**
     * Altera o valor do content-type
     * @param string $contentType Novo tipo de conteúdo
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    /**
     * Adiciona uma rota na classe
     * @param string $method Método HTTP da rota
     * @param string $route Rota a ser utilizada
     * @param array $params Parâmetros da rota
     */
    public function addRoute($method, $route, $params = [])
    {
        // PROCURANDO A FUNÇÃO CONTROLADORA DA ROTA
        foreach ($params as $key => $value)
        {
            if ($value instanceof \Closure)
            {
                $params['controller'] = $value;
                unset($params[$key]);
            }
        }

        $params['middlewares'] = $params['middlewares'] ?? [];
        $params['variables'] = [];
        $patternVariable = "/{(.*?)}/";

        // PROCURANDO POR VARIÁVEIS NA URL
        if (preg_match_all($patternVariable, $route, $matches))
        {
            $route = preg_replace($patternVariable, "(.*?)", $route);
            $params["variables"] = $matches[1];
        }

        // DEFININDO O PADRÃO DA ROTA
        $patternRoute = "/^" . str_replace("/", "\/", $route) . "$/";

        // ADICIONANDO A ROTA AO CONJUNTO DE ROTAS
        $this->routes[$patternRoute][$method] = $params;
    }

    /**
     * Define uma rota de GET
     * @param string $route Rota a ser adicionada
     * @param array $params Parâmetros da rota
     */
    public function get($route, $params = [])
    {
        return $this->addRoute("GET", $route, $params);
    }

    /**
     * Define uma rota de POST
     * @param string $route Rota a ser adicionada
     * @param array $params Parâmetros da rota
     */
    public function post($route, $params = [])
    {
        return $this->addRoute("POST", $route, $params);
    }

    /**
     * Define uma rota de PUT
     * @param string $route Rota a ser adicionada
     * @param array $params Parâmetros da rota
     */
    public function put($route, $params = [])
    {
        return $this->addRoute("PUT", $route, $params);
    }

    /**
     * Define uma rota de DELETE
     * @param string $route Rota a ser adicionada
     * @param array $params Parâmetros da rota
     */
    public function delete($route, $params = [])
    {
        return $this->addRoute("DELETE", $route, $params);
    }

    /**
     * Retorna a URI sem o prefixo
     * @return string
     */
    private function getUri()
    {
        $uri = $this->request->getUri();
        $xUri = strlen($this->prefix) ? explode($this->prefix, $uri) : [$uri];
        return rtrim(end($xUri), "/");
    }

    /**
     * Retorna os dados da rota atual
     * @return array
     */
    private function getRoute()
    {
        $uri = $this->getUri();
        $httpMethod = $this->request->getHttpMethod();
    
        // VERIFICANDO AS ROTAS ATUAIS
        foreach ($this->routes as $patternRoute => $methods)
        {
            // PROCURANDO A ROTA ATUAL NO CONJUNTO DE ROTAS
            if (preg_match($patternRoute, $uri, $matches))
            {
                // VERIFICANDO SE A ROTA PERMITE O MÉTODO HTTP UTILIZADO
                if (isset($methods[$httpMethod]))
                {
                    unset($matches[0]);

                    $keys = $methods[$httpMethod]["variables"];
                    $methods[$httpMethod]["variables"] = array_combine($keys, $matches);
                    $methods[$httpMethod]["variables"]["request"] = $this->request;

                    foreach ($methods[$httpMethod]["variables"] as $key => $value)
                    {
                        switch ($key)
                        {
                            case "request":
                                continue 2;

                            default:
                                if (count(explode("/", $value)) > 1)
                                {
                                    $methods[$httpMethod]["variables"][$key] = explode("/", $value)[0];
                                }

                                break;
                        }
                    }

                    if (isset($methods[$httpMethod]["variables"]["id"]))
                    {
                        if (!is_numeric($methods[$httpMethod]["variables"]["id"]))
                        throw new \Exception("parameter invalid", 500);
                    }

                    // RETORNANDO A ROTA ATUAL
                    return $methods[$httpMethod];
                }

                else 
                {
                    throw new \Exception("method not allowed", 405);
                }
            }
        }

        throw new \Exception("page not found", 404);
    }

    /**
     * Executa a rota atual
     * @return Response
     */
    public function run()
    {
        try 
        {
            $route = $this->getRoute();

            // VERIFICA SE A ROTA POSSUI UM PARÂMETRO DO TIPO CLOSURE
            if (!isset($route['controller']))
            {
                throw new \Exception("url could not be proccessed", 500);
            }

            $args = [];
            $reflection = new \ReflectionFunction($route['controller']);

            // PREENCHE O ARRAY ARGS COM OS PARÂMETROS DA FUNÇÃO CONTROLADORA DA ROTA
            foreach ($reflection->getParameters() as $parameter)
            {
                $name = $parameter->getName();
                $args[$name] = $route['variables'][$name] ?? "";
            }

            // REALIZA O PROCESSAMENTO DOS MIDDLEWARES
            return (new Middlewares\Queue($route['middlewares'], $route['controller'], $args))->next($this->request);
        }

        catch (\Exception $e)
        {
            // RETORNA AS PÁGINAS DE ERRO
            return self::getErrorPage($e);
        }
    }

    /**
     * Retorna a URL atual
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->url.$this->getUri();
    }

    /**
     * Redireciona o usuário
     * @var string $route
     */
    public function redirect($route)
    {
        $url = $this->url.$route;
        header("location: ".$url);
        exit();
    }

    /**
     * Controla a exibição das telas de erro
     * @param \Exception $e Exceção lançada pelo código
     * @return Response
     */
    private function getErrorPage($e)
    {
        // RETORNA UMA RESPOSTA CONTENDO O CÓDIGO HTTP DA EXCEÇÃO E A DEVIDA PÁGINA DE ERRO
        return new Response($e->getCode(), Error::getView($e));
    }
}

?>