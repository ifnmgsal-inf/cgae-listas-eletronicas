<?php

namespace App\Http\Middlewares;

class WithoutLogin
{
    /**
     * Executa o middleware
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle($request, $next)
    {
        $this->verifyLogin($request);

        return $next($request);
    }

    /**
     * Verifica se existe login
     * @param Request $request
     */
    private function verifyLogin($request)
    {
        // INICIALIZA A SESSÃO
        \App\Session\Login::init();

        // VERIFICA AS VARIÁVEIS DE SESSÃO
        if (!isset($_SESSION['user']['usuario']))
        {
            return;
        }

        if ($_SESSION['user']['usuario']['type'] == "student")
        {
            $request->getRouter()->redirect("/");
        }
        
        else if ($_SESSION['user']['usuario']['type'] == "assistant")
        {
            $request->getRouter()->redirect("/ass");
        }

        \App\Session\Login::logout();
        throw new \Exception("user not identified", 500);
    }
}

?>