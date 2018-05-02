<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/5/2
 * Time: 09:36
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/functions.php';
date_default_timezone_set('Asia/Shanghai');

$file = __DIR__ . '/../log/region.sql';
if (file_exists($file)) unlink($file);

// Provinces
$baseUrl = "http://www.stats.gov.cn/tjsj/tjbz/tjyqhdmhcxhfdm/2016/";

$provincePageURL = $baseUrl . "index.html";
$html = readPage($provincePageURL);

//<a href='14.html'>山西省<br/></a>

preg_match_all('/<a href=\'([0-9]+).html\'>([^<]+)<br\/><\/a>/', $html, $provinceMatches);
//print_r($matches);

$queue = [];
$dict = [];

for ($province_index = 0; $province_index < count($provinceMatches[0]); $province_index++) {
    //echo "Province Index: ".$province_index.PHP_EOL;

    $province_code = $provinceMatches[1][$province_index];
    $cityPageURL = $baseUrl . $province_code . ".html";
    $province_name = $provinceMatches[2][$province_index];

    //echo "Province Name: ".$province_name.PHP_EOL;

    //$html=readPage($cityPageURL);

    //<tr class="citytr"><td><a href="44/4401.html">440100000000</a></td><td><a href="44/4401.html">广州市</a></td></tr>
    //preg_match_all('/<tr class=\'citytr\'><td><a href=\'([0-9a-z\.\/]+)\'>([^<]+)<\/a><\/td><td><a href=\'[0-9a-z\.\/]+\'>([^<]+)<\/a><\/td><\/tr>/',$html,$cityMatches);
    //print_r($cityMatches);

    $queue_item = [
        'url' => $cityPageURL,
        'code' => $province_code,
        'type' => 'province',
        'address' => $province_name,
    ];
    array_unshift($queue, $queue_item);

//    $dict[]=[
//        'region_id'=>$province_code,
//        'parent_code'=>1,
//        'region_name'=>$province_name,
//        'region_type'=>'province',
//    ];
    confirmRegionItem($province_code, 1, $province_name, 'province', $province_name);
}

while (count($queue) > 0) {
    $item = array_shift($queue);

    $now = date('Y-m-d H:i:s');
    echo "[{$now}] Took one from queue [{$item['address']}] and seek children, " . count($queue) . " left...";

    $elementMatches = parseNextLevelElementsInPage($item['url']);
    if ($elementMatches) {
        for ($elementIndex = 0; $elementIndex < count($elementMatches[0]); $elementIndex++) {
            $elementType = $elementMatches[1][$elementIndex];
            $elementSubURL = $item['url'] . '/../' . $elementMatches[2][$elementIndex];
            $elementCode = $elementMatches[3][$elementIndex];
            $elementName = $elementMatches[4][$elementIndex];

            $elementAddress = $item['address'] . $elementName;

            //echo "{$elementType} Index: ".$elementIndex.PHP_EOL;
            //echo "{$elementType} Code: ".$elementCode.PHP_EOL;
//        echo "{$elementType} Name: ".$elementName.PHP_EOL;
            //echo "Parent: ".$item['code'].PHP_EOL;

            if (!empty($elementMatches[2][$elementIndex])) {
                $queue_item = [
                    'url' => $elementSubURL,
                    'code' => $elementCode,
                    'type' => $elementType,
                    'address' => $elementAddress,
                ];
                array_unshift($queue, $queue_item);
            }

//        $dict[$elementCode]=[
//            'region_code'=>$elementCode,
//            'parent_code'=>$item['code'],
//            'region_name'=>$elementName,
//            'region_type'=>$elementType,
//        ];
            confirmRegionItem($elementCode, $item['code'], $elementName, $elementType, $elementAddress);
        }
        echo "done" . PHP_EOL;
    } else {
        echo "failed for URL: " . $item['url'] . PHP_EOL;
    }
}

//foreach ($dict as $code => $item){
//    $sql="insert into region(region_id,parent_id,region_name,region_type) "
//        ."values ('{$item['region_code']}','{$item['parent_code']}','{$item['region_name']}','{$item['region_type']}');";
//    echo $sql.PHP_EOL;
//}

