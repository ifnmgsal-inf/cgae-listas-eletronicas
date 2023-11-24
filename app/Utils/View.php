<?php

namespace App\Utils;

class View
{
    /**
     * Variáveis padrão da View
     * @var array
     */
    private static $vars = [];
    
    /**
     * Define os dados iniciais da classe
     * @param array $vars Variáveis padrão à todas as views
     */
    public static function init($vars = [])
    {
        self::$vars = $vars;
    }

    /**
     * Retorna o conteúdo de uma View
     * @param string $view Caminho relativo do arquivo a ser carregado
     * @return string
     */
    private static function getContentView($view)
    {
        // DEFINE O CAMINHO RELATIVO DO ARQUIVO A SER RENDERIZADO
        $file = __DIR__."/../../src/view/".$view.".html";

        // RENDERIZA O CONTEÚDO DO ARQUIVO
        return file_exists($file) ? file_get_contents($file) : "";
    }

    /**
     * Retorna o conteúdo renderizado de uma view
     * @param string $view Caminho relativo do arquivo a ser carregado
     * @param array $vars Variáveis necessárias para a renderização do arquivo
     * @return string
     */
    public static function render($view, $vars = []) 
    {
        // OBTÉM OS DADOS DO ARQUIVO
        $content = self::getContentView($view);

        // MESCLA AS VARIÁVEIS EXCLUSIVAS COM AS PADRÕES
        $vars = array_merge(self::$vars, $vars);
        $keys = array_keys($vars);

        // REALIZA O MAPEAMENTO DA VARIÁVEIS
        $keys = array_map(function ($item) 
        {
            return "{{".$item."}}";
        }, $keys);

        // RETORNA O CONTEÚDO DA VIEW JÁ RENDERIZADO COM AS DEVIDAS VARIÁVEIS
        return str_replace($keys, array_values($vars), $content);
    }
}

?>