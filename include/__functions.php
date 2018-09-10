<?
// Check e-mail
function checkEmail($email)
{
    if (preg_match("/^[a-z0-9\.\-]+@[a-z0-9\.\-]+\.[a-z]{2,5}$/i", $email, $match)) {
        return true;
    }

    return false;
}

function dump()
{
    ob_start();
    call_user_func_array("var_dump", func_get_args());
    $varDump = "<?php\n" . ob_get_contents() . "?>";
    ob_end_clean();

    ini_set("highlight.comment", "#008000");
    ini_set("highlight.default", "#fa2772");  //fa2772
    ini_set("highlight.html", "#808080");
    ini_set("highlight.keyword", "#cfd0c2");
    ini_set("highlight.string", "#36af90");   //36af90

    $varDump = highlight_string($varDump, true);
    $varDump = str_replace('$lt;?php', '', $varDump);
    $varDump = str_replace('?&gt;', '', $varDump);

    echo "<meta charset='UTF-8'>";
    echo "<pre>";
    echo "<div style='color:#fff; 
                font-family: Monospaced, monospace; 
                background: #282826; 
                font-size: 14px; 
                border: 1px dotted #c0c0c0; 
                padding: 10px'>";
    echo trim($varDump);
    echo "</pre>";
    echo "</div>";
}

function putImageDB($tmp_name, $path, $width, $height)
{
    global $sql;

    $Image = image::getInstance();
    $newName = $Image->genFileName();
    $newImage = $Image->resizeEx($tmp_name, $newName, array($path, $width, $height));

    $sql->query("INSERT INTO #__#mediaLibrary (`image`, `path`) 
                            VALUES ('" . $newImage . "', '" . $path . "')");
    return array( 'id' => $sql->lastId(), 'name' => $newImage);
}

function deleteImage($where = '')
{
    global $sql;

    if ($where == ''){ dump('не передали where'); }

    $sql->query("SELECT `id`, `path`, `image` 
                            FROM #__#mediaLibrary 
                            WHERE " . $where . " LIMIT 1");
    $sql->next_row();

    if (!isset($sql->result['id'])){ return 'изображение не найдено'; }

    $sql->query("DELETE FROM #__#mediaLibrary WHERE `id` = '" . $sql->result['id'] . "'");
    unlink($sql->result['path'] . $sql->result['image']);
    return true;
}

function fieldsConfSave($fields, $category, $api)
{
    global $sql;

    foreach ($fields as $name => $field){

        $sql->query("SELECT `id` 
                                    FROM #__#config
                                    WHERE `name` = '" . $name . "'");
        $haveId = ($sql->next_row() > 0) ? $sql->result['id'] : false;

        if ($haveId){ //  обновляем

            $sql->query("UPDATE #__#config
                                        SET 
                                        `category` = '" . $category . "',
                                        `type` = 'api',
                                        `value` = '" . $field . "'
                                        WHERE `id` = '" . $haveId . "'");
        } else { // добавляем

            $sql->query("INSERT INTO #__#config 
                                        (`category`, `type`, `name`, `value`, `lang`, `city_name`)
                                        VALUES(
                                        '" . $category . "',
                                        '" . $api . "',
                                        '" . $name . "',
                                        '" . $field . "',
                                        'ru',
                                        'irkutsk')");
        }
    }
}

// Generate rand key
function genKey($len)
{
    $rString = "";
    $incr = 1;
    $buf = "qwertyuiopasdfghjklzxcvbnm1234567890";
    while ($incr <= $len) {
        $incr++;
        $rString .= substr($buf, mt_rand(0, strlen($buf)) - 1, 1);
    }

    return $rString;
}

// генератор картинок с видео ютуб
function youtubecode($url){
    $pattern = array(
            '/https/',
            '/\:/',
            '/\/\//',
            '/www./',
            '/youtu.be\//',
            '/youtube.com\//',
            '/watch\?v=/',
            '/\&feature=/'
    );
    return preg_replace($pattern, '', $url);
}

// правильные ссылки
function correcturl($urla){
    $pref = 'http://';

    if(preg_match('/https/', $urla))
        $pref = 'https://';

    if(preg_match('/http/', $urla))
        $pref = 'http://';

    $pattern = array(
        '/www./',
        '/https:\/\//',
        '/http:\/\//'
        );

    return $pref.preg_replace($pattern, '', $urla);
}

// Function slashes
function slash($string)
{
    if (get_magic_quotes_gpc() == false) {
        return addslashes($string);
    } else {
        return $string;
    }

}

function slashArray($array)
{
    $return = array();
    if (get_magic_quotes_gpc() == false) {
        foreach ($array as $key => $value) {
            if (!is_array($array[$key])) $return[$key] = addslashes($value); else $return[$key] = slashArray($return[$key]);
        }

        return $return;
    } else {
        return $array;
    }

}

function strip($string)
{
    return (!is_array($string) ? stripslashes($string) : stripArray($string));
}

function stripArray($array)
{
    $return = array();
    foreach ($array as $key => $value) {
        if (is_array($array[$key])) {
            $return[$key] = stripArray($array[$key]);
        } else {
            $return[$key] = stripslashes($value);
        }
    }

    return $return;
}

function go($uri)
{
    header("Location: " . $uri);
    exit();
}

function page404()
{
    global $mainTemplate, $main, $API;
    header("HTTP/1.1 404 Not found", true, 404);
    $mainTemplate->file($main->setTemplate("api/404.html"));
    $mainTemplate->assign("title", $main->getConfig("main", "api", "projectTitle"));
    $mainTemplate->stop();
}

function page403()
{
    global $mainTemplate, $main;
    header("HTTP/1.1 403 Forbidden", true, 403);
    $mainTemplate->file($main->setTemplate("api/403.html"));
    $mainTemplate->assign("title", $main->getConfig("main", "api", "projectTitle"));
    $mainTemplate->stop();
}

function page500()
{
    global $mainTemplate, $main;
    header("HTTP/1.1 500 Internal server error", true, 500);
    $mainTemplate->file($main->setTemplate("api/500.html"));
    $mainTemplate->assign("title", $main->getConfig("main", "api", "projectTitle"));
    $mainTemplate->stop();
}

function message($title, $desc = "", $uri = "", $url = "")
{
    global $base, $lang, $module;

    if (preg_match("/^\/(.*)$/i", $uri, $match)) {
        $uri = $match[1];
    }


    if (empty($uri)) {
        $ref = getenv("HTTP_REFERER");
        $uri = (!empty($ref) ? $ref : 'index.php');
    } else {
        $uri = "/" . $uri;
    }

    $template = new template();

    if ($module === "admin") {
        $template->file(api::setTemplate("api/message_admin.html"));
    } else {
        $template->file(api::setTemplate("api/message_site.html"));
    }

    $template->assign("title", $title);
    $template->assign("desc", $desc);
    $template->assign("uri", $uri);
    $template->out();
    exit();
    go($uri);

    return true;
}

function sl($string)
{
    return strtolower($string);
}

function su($string)
{
    return strtoupper($string);
}

function moneyToString($number)
{
    $origNumber = $number;
    $dop0 = array(
        "рублей",
        "тысяч",
        "миллионов",
        "миллиардов"
    );

    $dop1 = array(
        "рубль",
        "тысяча",
        "миллион",
        "миллиард"
    );

    $dop2 = array("рубля",
        "тысячи",
        "миллиона",
        "миллиарда"
    );

    $s1 = array(
        "",
        "один",
        "два",
        "три",
        "четыре",
        "пять",
        "шесть",
        "семь",
        "восемь",
        "девять"
    );

    $s11 = array("",
        "одна",
        "две",
        "три",
        "четыре",
        "пять",
        "шесть",
        "семь",
        "восемь",
        "девять"
    );

    $s2 = array("",
        "десять",
        "двадцать",
        "тридцать",
        "сорок",
        "пятьдесят",
        "шестьдесят",
        "семьдесят",
        "восемьдесят",
        "девяносто"
    );

    $s22 = array(
        "десять",
        "одиннадцать",
        "двенадцать",
        "тринадцать",
        "четырнадцать",
        "пятнадцать",
        "шестнадцать",
        "семнадцать",
        "восемнадцать",
        "девятнадцать");

    $s3 = array(
        "",
        "сто",
        "двести",
        "триста",
        "четыреста");

    if ($number == 0) {
        return "ноль " . $dop0[0];
    }


    $t_count = ceil(strlen($number) / 3);

    for ($i = 0; $i < $t_count; $i++) {
        $k = $t_count - $i - 1;
        $triplet[$k] = $number % 1000;
        $number = floor($number / 1000);
    }

    $res = "";

    for ($i = 0; $i < $t_count; $i++) {
        $t = $triplet[$i];
        $k = $t_count - $i - 1;
        $n1 = floor($t / 100);
        $n2 = floor(($t - $n1 * 100) / 10);
        $n3 = $t - $n1 * 100 - $n2 * 10;


        if ($n1 < 5) {
            $res .= $s3[$n1] . " ";
        } elseif ($n1) {
            $res .= $s1[$n1] . "сот ";
        }

        if ($n2 > 1) {
            $res .= $s2[$n2] . " ";
        }

        if ($n3 and $k == 1) {
            $res .= $s11[$n3] . " ";
        } elseif ($n3) {
            $res .= $s1[$n3] . " ";
        } elseif ($n2 == 1) {
            $res .= $s22[$n3] . " ";
        } elseif ($n3 and $k == 1) {
            $res .= $s11[$n3] . " ";
        } elseif ($n3) {
            $res .= $s1[$n3] . " ";
        }

        if ($n3 == 1 and $n2 != 1) {
            $res .= $dop1[$k] . " ";
        } elseif ($n3 > 1 and $n3 < 5 and $n2 != 1) {
            $res .= $dop2[$k] . " ";
        } elseif ($t or $k == 0) {
            $res .= $dop0[$k] . " ";
        }

    }


    return trim($res . substr(sprintf("%01.2f", $origNumber), -2, 2) . " копеек");
}

function trimString($text, $maxchar, $end = '...')
{
    if (strlen($text) > $maxchar) {
        $words = explode(' ', $text);
        $output = '';
        $i = 0;
        while (true) {
            $length = (strlen($output) + strlen($words[$i]));
            if ($length > $maxchar) break;
            else {
                $output = $output . " " . $words[$i];
                ++$i;
            }
        }
        $output .= $end;
    } else {
        $output = $text;
    }

    return $output;
}

/**
 * @param $msg
 * @param string $type - can be error/info/positive
 * @return string - templated html
 */
function msgTemplate($msg, $type = 'error')
{
    $template = new template(api::setTemplate('api/message.' . $type . '.html'));
    $template->assign('message', $msg);

    return $template->get();
}

/**
 * @param string $selected [default ''] name option to be selected
 * @param string $select_name [default 'template'] name attr of select tag
 * @param array $exclude [default array('admin.html', 'ajax.html')] contains templates to hide
 * @return string html with select=>options
 */
function genTemplateList($selected = '', $select_name = 'template', $exclude = array('admin.html', 'ajax.html'))
{
    $tmpl_folder = 'templates/ru/';
    $files_list = scandir($tmpl_folder);

    $result = '<select name="' . $select_name . '"><option value=""></option>';

    foreach ($files_list as $file_name) {
        if (preg_match('/^[0-9A-Za-z_]+\.html$/', $file_name) && !in_array($file_name, $exclude)) {
            $result .= '<option value="' . $file_name . '" ' . ($selected == $file_name ? 'selected="selected"' : '') . '>' . $file_name . '</option>';
        }
    }


    $result .= '</select>';

    return $result;
}

function genTemplateListInner($selected = '', $select_name = 'template', $exclude = array('admin.html', 'ajax.html'))
{
    $tmpl_folder = 'templates/ru/';
    $files_list = scandir($tmpl_folder);

    $result = '<select name="' . $select_name . '"><option value="inner.html" selected>inner.html</option>';

    foreach ($files_list as $file_name) {
        if (preg_match('/^[0-9A-Za-z_]+\.html$/', $file_name) && !in_array($file_name, $exclude)) {
            $result .= '<option value="' . $file_name . '" ' . ($selected == $file_name ? 'selected="selected"' : '') . '>' . $file_name . '</option>';
        }
    }


    $result .= '</select>';

    return $result;
}

function adminSelectLang($defaultValue)
{
    global $lang, $allLang;
    if (empty($defaultValue)) {
        $defaultValue = $lang;
    }

    if (empty($_SESSION['post'])) {
        $_SESSION['post'] = $defaultValue;
    }

    if (!empty($_POST['lang'])) {
        if ($_POST['lang'] == "ru" || $_POST['lang'] == "en" || $_POST['lang'] == "zh") {
            $_SESSION['post'] = $_POST['lang'];
        } else
            $_SESSION['post'] = $defaultValue;
    }

    $return = "<select name=\"lang\" size=\"1\" >";

    foreach ($allLang as $value) {
        $return .= "<option value=\"" . $value . "\"" . (sl($_SESSION['post']) == sl($value) ? " selected=\"selected\"" : "") . ">" . strtoupper($value) . "</option>";
    }
    $return .= "</select>";

    return $return;
}

function getUrl()
{
    $url = $_SERVER["SERVER_NAME"];
    $url .= ($_SERVER["SERVER_PORT"] != 80) ? ":" . $_SERVER["SERVER_PORT"] : "";
    $url .= $_SERVER["REQUEST_URI"];
    return $url;
}

function tonumbers($data){
    $pattern = array('/\-/','/\_/','/ /', '/\,/', '/\./');
    // return ($data != '')?preg_replace($pattern, '', $data):0;
    return ($data != '')?number_format(preg_replace($pattern, '', $data), 0, '', ' '):0;
}

function decimalPrice($price, $round = 2, $decimals = 2, $dec_point = ',', $thousands_sep = ' ')
{
    return number_format(round(($price), $round), $decimals, $dec_point, $thousands_sep);
}

function searchTransliterateNameInTable($table_name, $column, $value)
{
    global $sql;

    $return = '';
    $while = true;

    $name = str2url($value);

    $a = 0;
    $next = "";

    while ($while) {
        if ($a > 0) $next = "_" . $a;
        $sql->query("SELECT `id` FROM `" . $table_name . "` WHERE `" . $column . "` = '" . $name . $next . "'", true);
        if ((int)$sql->result === 0) {
            $while = false;
            break;
        }
        $a++;
    }

    $return = $name . $next;

    return $return;
}

function str2url($str)
{
    // переводим в транслит
    $str = rus2translit($str);
    // в нижний регистр
    $str = mb_strtolower($str, 'UTF-8');
    // заменям все ненужное нам на "-"
    $str = preg_replace('~[^-a-z0-9_]+~u', '-', $str);
    // удаляем начальные и конечные '-'
    $str = trim($str, "-");
    return $str;
}


function rus2translit($string)
{
    $converter = array(
        'а' => 'a', 'б' => 'b', 'в' => 'v',
        'г' => 'g', 'д' => 'd', 'е' => 'e',
        'ё' => 'e', 'ж' => 'zh', 'з' => 'z',
        'и' => 'i', 'й' => 'y', 'к' => 'k',
        'л' => 'l', 'м' => 'm', 'н' => 'n',
        'о' => 'o', 'п' => 'p', 'р' => 'r',
        'с' => 's', 'т' => 't', 'у' => 'u',
        'ф' => 'f', 'х' => 'h', 'ц' => 'c',
        'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',
        'ь' => '', 'ы' => 'y', 'ъ' => '',
        'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
        'А' => 'A', 'Б' => 'B', 'В' => 'V',
        'Г' => 'G', 'Д' => 'D', 'Е' => 'E',
        'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z',
        'И' => 'I', 'Й' => 'Y', 'К' => 'K',
        'Л' => 'L', 'М' => 'M', 'Н' => 'N',
        'О' => 'O', 'П' => 'P', 'Р' => 'R',
        'С' => 'S', 'Т' => 'T', 'У' => 'U',
        'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
        'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch',
        'Ь' => '', 'Ы' => 'Y', 'Ъ' => '',
        'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',
        '№' => 'N'
    );
    return strtr($string, $converter);
}

//function plural_form_basket($number, $teg = '', $after = array('товар', 'товара', 'товаров'))
//{
//    $cases = array(2, 0, 1, 1, 1, 2);
//    if ($teg) $number = "<" . $teg . ">{$number}</" . $teg . ">";
//    return $number;//.' '.$after[ ($number%100>4 && $number%100<20)? 2: $cases[min($number%10, 5)] ];
//}
function plural_form_basket($number, $one = 'товар', $two = 'товара', $five = 'товаров')//'товар', 'товара', 'товаров'
{
    if (($number - $number % 10) % 100 != 10) {
        if ($number % 10 == 1) {
            $result = $one;
        } elseif ($number % 10 >= 2 && $number % 10 <= 4) {
            $result = $two;
        } else {
            $result = $five;
        }
    } else {
        $result = $five;
    }
    return $number . ' ' . $result;
}