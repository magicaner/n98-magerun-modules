<?php
namespace UMC;

class FileSystem
{
    public static function remove($path)
    {
        return (is_string($path) && is_file($path)) ? @unlink($path) : array_map(
            'self::remove',
            glob($path . '/*')) == @rmdir($path);
    }

    public static function removeDir($path)
    {
        $fileSystem = new \N98\Util\Filesystem();
        return $fileSystem->recursiveRemoveDirectory($path);
    }

    public static function clear($path)
    {
        if (self::is_dir_empty($path)) {
            return true;
        }
        return (is_string($path) && is_file($path)) ? @unlink($path) : array_map(
            'self::remove',
            glob($path . '/*'));
    }

    public static function is_dir_empty($dir)
    {
        if (! is_readable($dir)) {
            return null;
        }
        $handle = opendir($dir);
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
                return false;
            }
        }
        return true;
    }

    public static function mkdir($pathname, $mode = 0777)
    {
        $old = umask(0);
        is_dir(dirname($pathname)) || self::mkdir(dirname($pathname), $mode);
        return is_dir($pathname) || @mkdir($pathname, $mode);
    }

    public static function filewrite($path, $content, $mode = "w", $chmod = 0777)
    {
        $path_info = pathinfo($path);

        if (! file_exists($path_info['dirname'])) {
            self::mkdir($path_info['dirname']);
        }

        $fp = fopen($path, $mode);
        $res = fputs($fp, $content);
        fclose($fp);
        if ($chmod !== false) {
            chmod($path, $chmod);
        }

        return $res;
    }

    public static function filewriteCSV($path, $content, $delimiter = ",", $mode = "w", $chmod = 0777)
    {
        $path_info = pathinfo($path);

        if (! file_exists($path_info['dirname'])) {
            self::mkdir($path_info['dirname']);
        }

        $fp = fopen($path, $mode);
        foreach ($content as $row) {
            $res = fputcsv($fp, $row, $delimiter);
        }
        fclose($fp);
        chmod($path, $chmod);

        return $res;
    }

    public static function fileExists($path)
    {
        return file_exists($path);
    }

    public static function readDir($path, $ignore = '/^(\.svn|\.subversion|\.|\.\.)$/')
    {
        if (! file_exists($path)) {
            return false;
        }
        $paths = array();
        if ($handle = opendir($path)) {
            while (false !== ($file = readdir($handle))) {
                if (preg_match($ignore, $file)) {
                    continue;
                }
                $paths[] = $file;
            }
            closedir($handle);
        }

        return $paths;
    }

    public static function getAllFilesRecursively($path, $ignore = '/^(\.svn|\.subversion|\.|\.\.)$/')
    {
        $files = array();
        $paths = self::readDir($path, $ignore);
        if ($paths) {
            foreach ($paths as $fname) {
                if (is_dir($path . '/' . $fname)) {
                    $files = array_merge($files, self::getAllFilesRecursively($path . '/' . $fname));
                } else {
                    $files[] = $path . '/' . $fname;
                }
            }
        }
        return $files;
    }

    public static function readfile($path)
    {
        if (! file_exists($path)) {
            return false;
        } else {
            return file_get_contents($path);
        }
    }

    public static function corectMBSize($size)
    {
        // less than 1 kb
        if ($size < 1024) {
            return number_format($size, 2) . " bytes";
        } elseif ($size < 1048576) { // less than 1 mb.
            return number_format($size / 1024, 2) . " kb";
        } elseif ($size >= 1048576) {
            return number_format($size / 1048576, 2) . " mb";
        }
    }

    public static function rename($oldPath, $newPath, $overwrite = true)
    {
        if ($overwrite) {
            self::remove($newPath);
        }
        return rename($oldPath, $newPath);
    }

    public static function copy($oldPath, $newPath, $overwrite = true)
    {
        if ($overwrite) {
            self::remove($newPath);
        }
        return copy($oldPath, $newPath);
    }
}
