<?php
namespace AeolusCMS\Helpers;

class File {
    
    public static function fileExists($filename) {
        return (\file_exists($filename));
    }

    public static function Read($filename,$remote=false) {
        if (!$remote) {
            if (\file_exists($filename)) {
                $handle = \fopen($filename, "r");
                $content = '';
                if (filesize($filename) > 0) {
                    $content = \fread($handle, \filesize($filename));
                }
                \fclose($handle);
                return $content;
            }
            else {
                return '';
            }
        }
        else {
            $content = \file_get_contents($filename);
            return $content;
        }
    }
    
    public static function Write($data,$filename,$append=false){
        if (!$append)
            $mode = "w";
        else
            $mode = "a";

        if (!file_exists(dirname($filename))) {
            mkdir(dirname($filename), 0777, true);
        }

        if($handle = \fopen($filename, $mode)){
            \fwrite($handle, $data);
            \fclose($handle);
            return true;
        }
        return false;
    }
    
    public static function getExtension($filename) {                
        $fileParts = \explode(".",$filename);
        return \end($fileParts);
    }
    
    public static function mkdir($path, $mode = 0755, $recursive = true) {
        $path = \str_replace("\\", "/", $path);
        $path = \explode("/", $path);

        $rebuild = '';
        foreach($path AS $p) {
            // Check for Windows drive letter
            if(\strstr($p, ":") != false) {
                $rebuild = $p;
                continue;
            }
            $rebuild .= "/$p";
            //echo "Checking: $rebuild\n";
            if(!\is_dir($rebuild))
                $ret = \mkdir($rebuild, $mode, $recursive);
        }
    }

    public static function Delete($dir) {
        if (\is_dir($dir)) {
            $objects = \scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir."/".$object))
                        self::Delete($dir."/".$object);
                    else
                        @\unlink($dir."/".$object);
                }
            }
            rmdir($dir);
        }
    }

    public static function chmod_r($path, $mod = 0755) {
        $dir = new DirectoryIterator($path);
        foreach ($dir as $item) {
            \chmod($item->getPathname(), $mod);
            if ($item->isDir() && !$item->isDot()) {
                self::chmod_r($item->getPathname());
            }
        }
    }
    
    public static function copy($src, $dest) {
        if(!\is_dir($src)) return false;
        if(!\is_dir($dest)) {
            if(!\mkdir($dest)) {
                return false;
            }
        }

        $i = new \DirectoryIterator($src);
        foreach($i as $f) {
            /* @var \DirectoryIterator $f*/
            if($f->isFile()) {
                \copy($f->getRealPath(), "$dest/" . $f->getFilename());
            } else if(!$f->isDot() && $f->isDir()) {
                self::copy($f->getRealPath(), "$dest/$f");
            }
        }
    }
    
    public static function move($src, $dest) {
        if(!is_dir($src)) {
            \rename($src, $dest);
            return true;
        }
        if(!\is_dir($dest)) {
            if(!\mkdir($dest)) {
                return false;
            }    
        }
        $i = new \DirectoryIterator($src);
        foreach($i as $f) {
            /* @var \DirectoryIterator $f*/
            if($f->isFile()) {
                \rename($f->getRealPath(), "$dest/" . $f->getFilename());
            } else if(!$f->isDot() && $f->isDir()) {
                self::move($f->getRealPath(), "$dest/$f");
                @\unlink($f->getRealPath());
            }
        }
        @\unlink($src);
    }

    public static function listing($path) {
        $arr = array();
        if(\is_dir($path)) {
            $i = new \DirectoryIterator($path);
            foreach($i as $f) {
                /* @var \DirectoryIterator $f*/
                if(!$f->isDot())
                    $arr[] = $f->getFilename();
            }
            return $arr;
        }
        return false;
    }
    
    public static function rmdirContent($path) {
        $i = new \DirectoryIterator($path);
        foreach($i as $f) {
            /* @var \DirectoryIterator $f*/
            if($f->isFile()) {
                \unlink($f->getRealPath());
            } else if(!$f->isDot() && $f->isDir()) {
                \rmdir($f->getRealPath());
            }
        }
        
    }
    
    public static function remove($path) {
        if(is_dir($path)) {
            return \rmdir($path);
        } else {
            if (self::fileExists($path)) {
                return \unlink($path);
            }
        }

        return false;
    }

    public static function folderSize($dir) {
        $size = 0;
        foreach (glob(rtrim($dir, '/').'/*', GLOB_NOSORT) as $each) {
            $size += \is_file($each) ? \filesize($each) : self::folderSize($each);
        }

        return $size;
    }

    public static function readFileChunked($filename, $retbytes = TRUE) {
        $chunk_size = 1024*1024;

        $buffer = '';
        $cnt = 0;
        $handle = \fopen($filename, 'rb');

        if ($handle === false) {
            return false;
        }

        while (!feof($handle)) {
            $buffer = \fread($handle, $chunk_size);
            echo $buffer;
            \ob_flush();
            \flush();

            if ($retbytes) {
                $cnt += \strlen($buffer);
            }
        }

        $status = \fclose($handle);

        if ($retbytes && $status) {
            return $cnt; // return num. bytes delivered like readfile() does.
        }

        return $status;
    }
}