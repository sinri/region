<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/5/2
 * Time: 13:52
 */

function truncateRegionIdByType($region_code, $region_type)
{
    switch ($region_type) {
        case 'province':
            $region_code = substr($region_code, 0, 2);
            break;
        case 'city':
            $region_code = substr($region_code, 0, 4);
            break;
        case 'county':
            $region_code = substr($region_code, 0, 6);
            break;
        case 'town':
            $region_code = substr($region_code, 0, 9);
            break;
        case 'village':
            $region_code = substr($region_code, 0, 12);
            break;
    }
    return $region_code;
}

function confirmRegionItem($region_code, $parent_code, $region_name, $region_type, $region_address)
{
    $file = __DIR__ . '/../log/region.' . getmypid() . '.sql';

//    -- 第1～2位，为省级代码；
//    -- 第3～4 位，为地级代码；
//    -- 第5～6位，为县级代码；
//    -- 第7～9位，为乡级代码；
//    -- 第10～12位，为村级代码。

    $sql = "insert into region(region_id,parent_id,region_name,region_type) "
        . "values ('{$region_code}','{$parent_code}','{$region_name}','{$region_type}'); "
        . " -- {$region_address}";
    //echo $sql . PHP_EOL;

    file_put_contents($file, $sql . PHP_EOL, FILE_APPEND);
}

function readPage($url, $fromCharset = 'GB2312', $toCharset = 'UTF-8//IGNORE')
{
    $html = '';
    for ($i = 0; $i < 10; $i++) {
        $curl = (new \sinri\ark\io\curl\ArkCurl());
        $curl->prepareToRequestURL(\sinri\ark\io\ArkWebInput::METHOD_GET, $url);
        $curl->setCURLOption(CURLOPT_HEADER, 1);
        $html = $curl->execute();
        //$html= file_get_contents($url);


        // HTTP/1.1 503 Service Temporarily Unavailable
        // HTTP/1.1 502 Proxy Error

        if (strpos($html, 'HTTP/1.1 503 Service Temporarily Unavailable') !== false
            || strpos($html, 'HTTP/1.1 502 Proxy Error')) {
            $html = '';
        }

        if (!empty($html)) break;
        echo "TAKE A SLEEP..." . PHP_EOL;
        sleep(rand(10, 30));
    }
    if (empty($html)) {
        return '';
    }
    $html = iconv($fromCharset, $toCharset, $html);
    return $html;
}

function parseNextLevelElementsInPage($url, &$error = 'no error')
{
    $url = urlSimplify($url);
    //echo "READ URL: ".$url.PHP_EOL;
    $html = readPage($url);
    if (empty($html)) {
        $error = "URL GOT EMPTY: " . $url;
        return false;
    }
    if (preg_match_all('/<tr class=\'([a-z]+)tr\'><td><a href=\'([0-9a-z\.\/]+)\'>([^<]+)<\/a><\/td><td><a href=\'[0-9a-z\.\/]+\'>([^<]+)<\/a><\/td><\/tr>/', $html, $matches)) {
        //print_r($matches);
        //echo "MATCHED NODES".PHP_EOL;
        return $matches;
    } else {
        if (preg_match_all('/<tr class=\'([a-z]+)tr\'>([^<]*)<td>([^<]+)<\/td><td>[\d]+<\/td><td>([^<]+)<\/td><\/tr>/', $html, $matches)) {
            //echo "MATCHED LEAVES".PHP_EOL;
            //print_r($matches);
            return $matches;
        } else {
            //echo "MATCHED EMPTY".PHP_EOL;
            $error = "STRANGE OUTPUT FOR: " . $url . PHP_EOL . '========' . PHP_EOL . $html . PHP_EOL . "========" . PHP_EOL;
            return false;
        }
    }
}

function urlSimplify($address)
{
    $address = explode('/', $address);
    $keys = array_keys($address, '..');

    foreach ($keys AS $keypos => $key) {
        array_splice($address, $key - ($keypos * 2 + 1), 2);
    }

    $address = implode('/', $address);
    $address = str_replace('./', '', $address);

    return $address;
}