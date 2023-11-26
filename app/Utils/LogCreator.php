<?php

namespace App\Utils;

class LogCreator
{
    public static function createLog()
    {
        \App\Session\Login::init();

        $ip = $_SERVER['REMOTE_ADDR'];
        $user = $_SESSION['user']['usuario']['id'];
        $type = $_SESSION['user']['usuario']['type'];
        
        date_default_timezone_set("America/Sao_Paulo");
        $time = date("d-m-Y H:i:s", time());

        $str = $ip.",".$user.",".$type.",".$time;

        self::writeFile($str);
    }

    private static function writeFile($newLine)
    {
        $file = fopen(__DIR__.'/../../log/login.csv', 'r');

        function get_all_lines($file) { 
            while (!feof($file)) {
                yield fgets($file);
            }
        }

        $lines = [];

        foreach (get_all_lines($file) as $line) {
            $lines[] = $line;
        }

        fclose($file);

        $content = "";
        $count = 0;

        foreach ($lines as $line)
        {
            $str = $line;

            if ($count + 1 == count($lines))
            {
                if (substr($line, strlen($line) - 1, strlen($line)) != "\n") $str = $line."\n";
            }

            $content .= $str;
            $count++;
        }

        $content .= $newLine;
        
        file_put_contents(__DIR__.'/../../log/login.csv', $content);
    }
}

?>