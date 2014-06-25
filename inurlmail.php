<?php
/*
  ##########################################################################################
 *  SCANNER INURL-MAIL     1.0
 *  PHP Version         5.4.7
 *  php5-curl           LIB
 *  cURL support	enabled
 *  cURL Information	7.24.0
 *  Apache              2.4
 *  allow_url_fopen =   On
 *  Motor de busca      GOOGLE
 *  Permição            Leitura & Escrita 
 *  -------------------------------------------------------------------------------------
 *  BUSCA LISTMAIL 
 *  OBJETIVO USAR O MOTOR DE BUSCA GOOGLE PARA CAPTURAR EMAILS LIST.
 *  A CADA URL ENCONTRADA PELO BUSCADOR, SERA EFETUADO UM FILTRO CAPTURANDO OS EMAILS
 *  CONTIDOS NA URL.
 *  -------------------------------------------------------------------------------------
 *  GRUPO GOOGLEINURL BRASIL - PESQUISA AVANÇADA.
 *  fb.com/GoogleINURL
 *  twitter.com/GoogleINURL
 *  blog.inurl.com.br
  ##########################################################################################
 */

error_reporting(0);
set_time_limit(0);
ini_set('display_errors', 0);
ini_set('max_execution_time', 0);
ini_set('allow_url_fopen', 1);

$GLOBALS['cont'] = 0;
$GLOBALS['resultado'] = NULL;
#FORMATAÇÃO DE ARGUMENTOS#######################################################

    system("command clear");
    $menu = "-----------------------------------------------------------------------------\n";
    $menu.= "\033[01;31m                       [ SCANNER INURL-MAIL ] \n\033[0m";
    $menu.= "-----------------------------------------------------------------------------\r\n";

    $menu.= "-------------------------------AJUDA-----------------------------------------\n";
    $menu.= "\033[01;33mphp inurlmail.php \033[01;31m --dork=\033[01;33m'site:.com.br hotmail ext:txt'\033[01;31m --arquivo=\033[01;33m'mails.txt'\n";
    $menu.= "Exemplo de dorks: http://blog.inurl.com.br/search/label/email\n";
    $menu.= "Ajuda: php inurlmail.php \033[01;31majuda \n\033[0m";		
    $menu.= "-----------------------------------------------------------------------------\r\n";
    echo $menu;




function argumentos($argv, $campo) {
    $_ARG = array();
    foreach ($argv as $arg) {
        if (ereg('--[a-zA-Z0-9]*=.*', $arg)) {
            $str = split("=", $arg);
            $arg = '';
            $key = ereg_replace("--", '', $str[0]);
            for ($i = 1; $i < count($str); $i++) {
                $arg .= $str[$i];
            }
            $_ARG[$key] = $arg;
        } elseif (ereg('-[a-zA-Z0-9]', $arg)) {
            $arg = ereg_replace("-", '', $arg);
            $_ARG[$arg] = 'true';
        }
    }
    return $_ARG[$campo];
}

################################################################################
#VALIDAÇÃO DE ARGUMENTOS########################################################

function validar($argv, $id, $campo) {

    if (isset($argv[$id]) && ereg('--[a-zA-Z0-9]*=.*', $argv[$id]) && !empty($argv[$id])) {

        $validacao = argumentos($argv, $campo);
    }
    return $validacao;
}

################################################################################
#EXTRAÇÃO DE EMAIL##############################################################

function extrairMail($html) {
    preg_match_all('/([\w\d\.\-\_]+)@([\w\d\.\_\-]+)/mi', $html, $matches);

    foreach ($matches['0'] as $valor) {
        echo "[\033[01;31m {$GLOBALS['cont']} \033[0m]- {$valor}\r\n";
        $GLOBALS['resultado'].="{$valor}\r\n";
        $GLOBALS['cont']++;
    }
}

################################################################################
#ENVIAR INFORMAÇÕES PARA GOOGLE#################################################

function eviarPacote($packet, $config) {

    if (isset($config['ipProxy'])) {
        $ock = fsockopen($config['ipProxy'], $config['porta']);
        if (!$ock) {
            echo "\033[01;31m Proxy não responde {$config['ipProxy']} : {$config['porta']}\r\n\033[0m";
            die;
        }
    } else {

        $ock = fsockopen(gethostbyname($config['host']), $config['port']);
        if (!$ock) {
            echo "\033[01;31m Host não responde {$config['host']} : {$config['port']}\r\n\033[0m";
            die;
        }
    }

    fputs($ock, $packet);
    $buffer = NULL;
    while (!feof($ock)) {
        $buffer.=fgets($ock);
    }
    fclose($ock);
    return($buffer);
}

################################################################################
#REQUEST PARA CAPTURA DE EMAILS#################################################

function requestCurl($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.' . date('s') . '(Windows; U; Windows NT 6.' . date('s') . '; en-US; rv:1.' . date('s') . '.1.2) Gecko/2009072' . date('s') . ' Firefox/3.' . date('s') . '.2 GTB5');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_REFERER, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $result = curl_exec($ch);
    if (isset($result)) {
        return $result;
    } else {

        return FALSE;
    }
}

################################################################################
#FILTRO DE URL DO RESULTADO GOOGLE##############################################

function formatarResult($html) {
    preg_match_all('#\b((((ht|f)tps?://)|(www|ftp)\.)[a-zA-Z0-9\.\#\@\:%_/\?\=\~\-]+)#i', $html, $match);
    $match[1] = array_unique($match[1]);
    for ($i = 0; $i < count($match[1]); $i++) {
        if (isset($match[1][$i]) && !strstr($match[1][$i], "google") && !strstr($match[1][$i], "youtube") && !strstr($match[1][$i], "orkut") && !strstr($match[1][$i], "schema") && !strstr($match[1][$i], "blogger")) {
            extrairMail(requestCurl($match[1][$i]));
        }
    }
}

################################################################################
#MENU AJUDA#####################################################################
if (isset($argv[1]) && $argv[1] == "ajuda") {
    system("command clear");
    $menu = "-----------------------------------------------------------------------------\n";
    $menu.= "\033[01;31m                       [ SCANNER INURL-MAIL ] \n\033[0m";
    $menu.= "-----------------------------------------------------------------------------\r\n";

    $menu.= "-------------------------------AJUDA-----------------------------------------\n";
    $menu.= "\033[01;31mphp inurlmail.php --dork='site:.com.br hotmail ext:txt' --arquivo='mails.txt'\n\033[0m";
    $menu.= "Exemplo de dorks: http://blog.inurl.com.br/search/label/email\n\033[0m";	
    $menu.= "-----------------------------------------------------------------------------\n";
    echo $menu;
    exit();
}
################################################################################
#VALIDANDO ARGUMENTOS###########################################################
if (isset($_SERVER['argv'][1])) {
    $dork = validar($_SERVER['argv'], 1, 'dork');
} else {
    print"Defina a dork de pesquisa.\r\n";
    unset($dork);
    exit();
}
if (isset($_SERVER['argv'][2])) {
    $arquivo = validar($_SERVER['argv'], 2, 'arquivo');
} else {
    print"Defina o arquivo resultado.\r\n";
    unset($arquivo);
    exit();
}

################################################################################
#CONFIGURAR#####################################################################
$config['host'] = 'www.google.com.br';
$config['dork'] = urlencode($dork);
$config['url'] = "/search?q={$config['dork']}&num=1500&btnG=Search";
$config['port'] = 80;
$config['host'] = trim($config['host']);
$packet = "GET {$config['url']} HTTP/1.0\r\n";
$packet.="Host: {$config['host']}\r\n";
$packet.="Connection: Close\r\n\r\n";
################################################################################

print_r(formatarResult(eviarPacote($packet, $config)));

    echo "\r\nArquivo..: {$arquivo}\r\n";
    $abrirtxt = fopen($arquivo, "a");
    if ($abrirtxt == false) {
        die('Não foi possível criar o arquivo.');
    }
    fwrite($abrirtxt, $GLOBALS['resultado']);
    fclose($abrirtxt);


?>
