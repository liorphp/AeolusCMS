<?php
namespace AeolusCMS\Helpers;

use DirectoryIterator;

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

    /**
     * Writes data into a gzipped tar archive (.tar.gz).
     *
     * @param string|array $data       File content, or array of [innerName => content] for multiple entries.
     * @param string       $filename   Target path, e.g. /path/backup.tar.gz
     * @param string|null  $innerName  Name of the file inside the archive. Defaults to the target
     *                                 file name without the .tar.gz / .tgz / .tar suffix.
     * @param int          $level      Gzip compression level, 0-9.
     * @return bool
     */
    public static function WriteTarGz($data, $filename, $innerName = null, $level = 9) {
        if (!\function_exists('gzencode')) {
            return false;
        }

        if (!\is_array($data)) {
            if ($innerName === null || $innerName === '') {
                $innerName = \preg_replace('/\.(tar\.gz|tgz|tar)$/i', '', \basename($filename));
                if ($innerName === '') {
                    $innerName = 'file';
                }
            }
            $data = array($innerName => $data);
        }

        $tar = '';
        foreach ($data as $name => $content) {
            $content = (string) $content;
            $header = self::tarHeader($name, \strlen($content));
            if ($header === false) {
                return false;
            }
            $tar .= $header . $content;
            $pad = (512 - (\strlen($content) % 512)) % 512;
            if ($pad > 0) {
                $tar .= \str_repeat("\0", $pad);
            }
        }
        // Two empty 512 byte blocks mark the end of the archive.
        $tar .= \str_repeat("\0", 1024);

        $gz = \gzencode($tar, $level);
        if ($gz === false) {
            return false;
        }

        $dir = \dirname($filename);
        if (!\is_dir($dir)) {
            \mkdir($dir, 0777, true);
        }

        return \file_put_contents($filename, $gz) !== false;
    }

    /**
     * Reads a gzipped tar archive (.tar.gz) created by WriteTarGz.
     *
     * @param string      $filename  Path to the archive.
     * @param string|null $entry     Name of the entry to extract. Null returns the first regular file.
     * @param bool        $all       true returns an associative array of [name => content] for every entry.
     * @return string|array|false    Content, array of contents, or false on failure / missing entry.
     */
    public static function ReadTarGz($filename, $entry = null, $all = false) {
        if (!\function_exists('gzdecode') || !\file_exists($filename)) {
            return false;
        }

        $raw = \file_get_contents($filename);
        if ($raw === false || $raw === '') {
            return false;
        }

        $tar = @\gzdecode($raw);
        if ($tar === false) {
            return false;
        }

        $entries = self::parseTar($tar, ($all || $entry !== null) ? null : 1);

        if ($all) {
            return $entries;
        }
        if ($entry !== null) {
            return \array_key_exists($entry, $entries) ? $entries[$entry] : false;
        }

        return \count($entries) > 0 ? \reset($entries) : false;
    }

    /**
     * Builds a 512 byte ustar header block.
     *
     * @return string|false
     */
    private static function tarHeader($name, $size, $mode = 0644, $mtime = null) {
        if ($mtime === null) {
            $mtime = \time();
        }

        $name = \ltrim(\str_replace("\\", "/", $name), "/");
        if ($name === '') {
            return false;
        }

        $prefix = '';
        if (\strlen($name) > 100) {
            $split = \strrpos(\substr($name, 0, 156), '/');
            if ($split === false || \strlen($name) - $split - 1 > 100) {
                return false; // Path too long for the ustar format.
            }
            $prefix = \substr($name, 0, $split);
            $name   = \substr($name, $split + 1);
            if (\strlen($prefix) > 155) {
                return false;
            }
        }

        $header  = \pack('a100', $name);
        $header .= \pack('a8', \sprintf('%07o', $mode & 0777));
        $header .= \pack('a8', \sprintf('%07o', 0));  // uid
        $header .= \pack('a8', \sprintf('%07o', 0));  // gid
        $header .= \pack('a12', \sprintf('%011o', $size));
        $header .= \pack('a12', \sprintf('%011o', $mtime));
        $header .= \str_repeat(' ', 8);               // checksum placeholder
        $header .= '0';                               // typeflag: regular file
        $header .= \pack('a100', '');                 // linkname
        $header .= \pack('a6', 'ustar') . '00';       // magic + version
        $header .= \pack('a32', '') . \pack('a32', ''); // uname + gname
        $header .= \pack('a8', '') . \pack('a8', '');   // devmajor + devminor
        $header .= \pack('a155', $prefix);
        $header .= \str_repeat("\0", 12);             // padding to 512

        $checksum = 0;
        for ($i = 0; $i < 512; $i++) {
            $checksum += \ord($header[$i]);
        }

        return \substr_replace($header, \sprintf("%06o\0 ", $checksum), 148, 8);
    }

    /**
     * Parses an uncompressed tar stream into [name => content].
     *
     * @param string   $tar
     * @param int|null $limit  Stop after this many regular files.
     * @return array
     */
    private static function parseTar($tar, $limit = null) {
        $entries = array();
        $length  = \strlen($tar);
        $offset  = 0;

        while ($offset + 512 <= $length) {
            $header  = \substr($tar, $offset, 512);
            $offset += 512;

            if (\trim($header, "\0") === '') {
                break; // End of archive.
            }

            $name = \trim(\substr($header, 0, 100), "\0 ");
            $size = \trim(\substr($header, 124, 12), "\0 ");
            $size = ($size === '') ? 0 : (int) \octdec($size);
            $type = \substr($header, 156, 1);

            if (\substr($header, 257, 5) === 'ustar') {
                $prefix = \trim(\substr($header, 345, 155), "\0 ");
                if ($prefix !== '') {
                    $name = $prefix . '/' . $name;
                }
            }

            if ($type === '0' || $type === "\0") {
                $entries[$name] = \substr($tar, $offset, $size);
                if ($limit !== null && \count($entries) >= $limit) {
                    return $entries;
                }
            }

            $offset += (int) (\ceil($size / 512) * 512);
        }

        return $entries;
    }
}