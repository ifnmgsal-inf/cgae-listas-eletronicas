<?php

namespace App\Http\Middlewares;

class RequireLogin
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
     * Verifica se existe um login de aluno
     * @param Request $request
     */
    private function verifyLogin($request)
    {
        // INICIALIZA A SESSÃO
        \App\Session\Login::init();

        // VERIFICA AS VARIÁVEIS DE SESSÃO
        if (isset($_SESSION['user']['usuario']))
        {
            return;
        }

        // REALIZA O REDIRECIONAMENTO
        $request->getRouter()->redirect("/entrar");
    }
}

?>