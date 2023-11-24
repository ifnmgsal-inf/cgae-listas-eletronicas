<?php

namespace App\Utils;

class MakePDF
{
    private static $directory;
    private static $vfsDir;

    private static function createVfsFiles()
    {
        self::initializeVariables();
        
        $output = "this.pdfMake = this.pdfMake || {}; this.pdfMake.vfs = {";
        
        foreach (self::$directory as $dir)
        {
            while (($file = $dir->read()) != false)
            {
                if ($file != ".." && $file != ".")
                {
                    $output .= "\"";
                    $output .= $file;
                    $output .= "\":\"";
                    $output .= base64_encode(file_get_contents($dir->path."/".$file));
                    $output .= "\",";
                }
            }
        }

        $output = substr($output, 0, -1);
        $output .= "}";

        if (file_put_contents(self::$vfsDir, $output) == false) throw new \Exception("Error processing the file", 500);
    }

    private static function initializeVariables()
    {
        self::$directory = array(
            dir(__DIR__."/../../src/fonts"),
            dir(__DIR__."/../../src/img/pdf")
        );
        
        self::$vfsDir = __DIR__."/../../src/js/pdfmake/vfs_files.js";
    }

    public static function checkVfsFile()
    {
        self::initializeVariables();
        if (!file_exists(self::$vfsDir)) self::createVfsFiles();
    }
}

?>