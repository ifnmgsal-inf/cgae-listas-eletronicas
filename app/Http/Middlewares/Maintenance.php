<?php

namespace App\Http\Middlewares;

class Maintenance
{
    /**
     * Executa o middleware
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle($request, $next)
    {
        if (getenv("MAINTENANCE") === "true")
        {
            throw new \Exception("maintenance", 503);
        }

        return $next($request);
    }
}

?>