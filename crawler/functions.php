<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/5/2
 * Time: 13:52
 */

function confirmRegionItem($region_code, $parent_code, $region_name, $region_type, $region_address)
{
    $file = __DIR__ . '/../log/region.' . getmypid() . '.sql';

    $sql = "insert into region(region_id,parent_id,region_name,region_type) "
        . "values ('{$region_code}','{$parent_code}','{$region_name}','{$region_type}'); "
        . " -- {$region_address}";
    //echo $sql . PHP_EOL;

    file_put_contents($file, $sql . PHP_EOL, FILE_APPEND);
}

function readPage($url, $fromCharset = 'GB2312', $toCharset = 'UTF-8//IGNORE')
{
    $html = '';
    for ($i = 0; $i < 5; $i++) {
        $html = (new \sinri\ark\io\curl\ArkCurl())->prepareToRequestURL(\sinri\ark\io\ArkWebInput::METHOD_GET, $url)->execute();
        //$html= file_get_contents($url);
        if (!empty($html)) break;
        sleep(rand(1, 5));
    }
    if (empty($html)) return '';
    $html = iconv($fromCharset, $toCharset, $html);
    return $html;
}

function parseNextLevelElementsInPage($url)
{
    $url = urlSimplify($url);
    //echo "READ URL: ".$url.PHP_EOL;
    $html = readPage($url);
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