<?php

namespace App\Utils;

class ImportManager
{
    private static $map = [
        "student" => [
            "base" => [
                "css" => [],
                "js" => []
            ],

            "home" => [
                "css" => [],
                "js" => []
            ],

            "signatures" => [
                "css" => [],
                "js" => []
            ],

            "profile" => [
                "css" => [],
                "js" => []
            ]
        ]
    ];

    public static function getImports($view, $user)
    {
        $content = "";

        $keys = array_keys(self::$map[$user][$view]);
        $values = array_values(self::$map[$user][$view]);
        $i = 0;

        foreach ($values as $file)
        {
            $content .= self::renderImport($file, $keys[$i]);
            $i++;
        }

        return $content;
    }

    private static function renderImport($file, $type)
    {
        switch ($type)
        {
            case "css":
                return "<link href=\"".URL."/src/css/".$file.".css\" rel=\"stylesheet\">";

            case "js":
                return "<script src=\"".URL."/src/js/".$file.".js\"></script>";
        }
    }
}

?>