<?php

namespace App\Http\Middlewares;

class Queue
{
    /**
     * Mapeamento de middlewars
     * @var array
     */
    private static $map = [];
    
    /**
     * Mapeamento de middlewars que serão carregados em todas as rotas
     * @var array
     */
    private static $default = [];

    /**
     * Fila de middlewares a serem executados
     * @var array
     */
    private $middlewares = [];

    /**
     * Função de execução do controlador
     * @var \Closure
     */
    private $controller;

    /**
     * Argumentos da função do controlador
     * @var array
     */
    private $controllerArgs = [];

    /**
     * Constrói a classe de fila de middlewares
     * @param array $middlewares
     * @param Closure $controller
     * @param array $controllerArgs
     */
    public function __construct($middlewares, $controller, $controllerArgs)
    {
        // MESCLA OS MIDDLEWARES EXCLUSIVOS DA ROTA ATUAL COM OS MIDDLEWARES PADRÃO DE TODAS AS ROTAS
        $this->middlewares = array_merge(self::$default, $middlewares);
        $this->controller = $controller;
        $this->controllerArgs = $controllerArgs;
    }

    /**
     * Define o mapeamento de middlewares
     * @param array $map Array de mapeamento das classes dos middlewares
     */
    public static function setMap($map)
    {
        self::$map = $map;
    }

    /**
     * Define o mapeamento de middlewares padrões
     * @param array $default Array de mapeamento de middlewares padrões em todas as rotas 
     */
    public static function setDefault($default)
    {
        self::$default = $default;
    }

    /**
     * Executa o próximo nível da gila de middlewares
     * @param Request $request Objeto de requisição
     * @return Response
     */
    public function next($request)
    {
        // VERIFICA SE A FILA DE MIDDEWARES ESTÁ VAZIA
        if (empty($this->middlewares))
        {
            // EXECUTA A FUNÇÃO CONTROLADORA DA ROTA ATUAL
            return call_user_func_array($this->controller, $this->controllerArgs);
        }

        // RETIRA O PRIMEIRO MIDDLEWARE DA FILA
        $middleware = array_shift($this->middlewares);

        // VERIFICA SE O MIDDLEWARE A SER EXECUTADO ESTÁ MAPEADO
        if (!isset(self::$map[$middleware]))
        {
            throw new \Exception("failed to proccess the middleware ".$middleware, 500);
        }

        $queue = $this;

        // REPLICA A FUNÇÃO ATUAL PARA A RECURSIVIDADE
        $next = function ($request) use ($queue)
        {
            return $queue->next($request);
        };

        // EXECUTA O MIDDLEWARE ATUAL
        return (new self::$map[$middleware])->handle($request, $next);
    }
}