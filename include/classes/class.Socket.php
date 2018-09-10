<?
class Socket {
        var $cacheDir;

        function getFomSocket($host, $uri, $port=80) {
                // Generate headers

                $returnData = "";

                $headers = "GET $uri HTTP/1.0\n";
                $headers.= "Host: ".$host."\n";
                $headers.= "User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; ru-RU; rv:1.7.12) Gecko/20050919 Firefox/1.0.7\n";
                $headers.= "Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5\n";
                $headers.= "Accept-Language: ru-ru,ru;q=0.5\n";
                $headers.= "Accept-Charset: windows-1251,utf-8;q=0.7,*;q=0.7\n";
                $headers.= "Connection: closed\n";
                $headers.= "Referer: http://www.auto.vl.ru/sales/\r\n\r\n";

                $socketId = fsockopen($host, $port);
                if ($socketId === false) {
                        return false;
                }

                fputs($socketId, $headers, strlen($headers));

                $fileStart = false;

                while (!feof($socketId)) {
                        $data=fgets($socketId);
                        if ($fileStart == false && ($data == "\r\n" || $data == "\r\n\r\n" || $data == "\r\r" || $data == "\n\n" || $data == "\n")) {
                                $fileStart = true;
                                continue;
                                }

                        if ($fileStart == false) continue;

                        $returnData.= $data;
                }
                @fclose($socketId);

                return $returnData;
                }

        function get($host, $uri, $port=80) { 
                // chache
                $this->checkCacheDir();
                $fromCache = $this->getFromCache($uri);

                if ($fromCache === false) {
                        $data = $this->getFomSocket($host, $uri, $port);
                        $this->writeCache($uri, $data);
                } else {
                        $data = $fromCache;
                }
                return $data;

        }

        function checkCacheDir() {
                if (!isset($this->cacheDir) || empty($this->cacheDir)) {
                        $this->cacheDir = "cache/".date("dmY");
                        }

                if (!file_exists($this->cacheDir)) {
                        mkdir($this->cacheDir) or die("Can't create dir: ".$this->cacheDir);
                }

                if (!is_writable($this->cacheDir)) {
                        exit("Can't write data to ".$this->cacheDir.". Please, set correct permissions");
                }
        }

        function getFromCache($uri) {
                $fileName=$this->cacheDir."/".md5(base64_encode($uri));
                if (file_exists($fileName) && filesize($fileName) != 0) {
                        $fId=fopen($fileName, "r") or die("Can't read data from ".$fileName);
                        @flock($fId, LOCK_SH);
                        $return=fread($fId, filesize($fileName));
                        @flock($fId, LOCK_UN);
                } else {
                        $return = false;
                }
        return $return;
        }

        function writeCache($uri, $data) {
                $fileName=$this->cacheDir."/".md5(base64_encode($uri));
                $fId=fopen($fileName, "w") or die("Can't write data to ".$fileName);
                @flock($fId, LOCK_EX);
                fwrite($fId, $data, strlen($data));
                @flock($fId, LOCK_UN);
                return true;
        }
}
?>