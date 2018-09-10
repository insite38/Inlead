<?
class img {
        function copySmallUsingSocket($uri, $id, $num) {
                global $socket;
                if (!file_exists("imagesCache/humb/$id/")) {
                        mkdir("imagesCache/humb/$id/") or die("Can't create dir imagesCache/humb/$id/");
                        }

                if (file_exists("imagesCache/humb/$id/$num.jpg")) return true;

                $data=$socket->getFomSocket("www.auto.vl.ru", $uri);

                $fId=fopen("imagesCache/humb/$id/".$num.".jpg", "wb") or die("Can't open imagesCache/humb/$id/".$num.".jpg for write");
                @flock($fId, LOCK_EX);
                fwrite($fId, $data, strlen($data));
                @flock($fId, LOCK_UN);
                return true;
                }

        function copyBigUsingSocket($uri, $id, $num) {
                global $socket;
                if (!file_exists("imagesCache/$id/")) {
                        mkdir("imagesCache/$id/") or die("Can't create dir imagesCache/$id/");
                        }

                if (file_exists("imagesCache/$id/$num.jpg")) return true;

                $data=$socket->getFomSocket("www.auto.vl.ru", $uri);

                $fId=fopen("imagesCache/$id/".$num.".jpg", "wb") or die("Can't open imagesCache/$id/".$num.".jpg for write");
                @flock($fId, LOCK_EX);
                fwrite($fId, $data, strlen($data));
                @flock($fId, LOCK_UN);

                $fname="imagesCache/$id/".$num.".jpg";

                $info    = @getimagesize($fname);
                $cp      = @imagecreatefromjpeg($fname);
                $source  = @imagecreatetruecolor($info[0], $info[1]);
                imagecopyresampled($source , $cp, 0, 0, 0, 0, $info[0], $info[1], $info[0], $info[1]);

                $wather  = @imagecreatefromgif("templates/images/waterMark.gif");
                $wmX = (bool)rand(0,1) ? 10 : (imagesx($source) - imagesx($wather)) - 10;
                $wmY = (bool)rand(0,1) ? 10 : (imagesy($source) - imagesy($wather)) - 10;
                imagecopymerge($source, $wather, $wmX, $wmY, 0, 0, imageSX($wather), imageSY($wather), 70);

                imagejpeg($source, $fname, 100);
                return true;
                }
        }


        ?>