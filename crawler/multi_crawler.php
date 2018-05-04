<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/5/2
 * Time: 13:52
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/functions.php';
date_default_timezone_set('Asia/Shanghai');

// 0. constants

$baseUrl = "http://www.stats.gov.cn/tjsj/tjbz/tjyqhdmhcxhfdm/2016/";

// 1. load provinces

$provincePageURL = $baseUrl . "index.html";
$html = readPage($provincePageURL);
preg_match_all('/<a href=\'([0-9]+).html\'>([^<]+)<br\/><\/a>/', $html, $provinceMatches);

for ($province_index = 0; $province_index < count($provinceMatches[0]); $province_index++) {

    $province_code = truncateRegionIdByType($provinceMatches[1][$province_index], 'province');
    $cityPageURL = $baseUrl . $province_code . ".html";
    $province_name = $provinceMatches[2][$province_index];

    $gotChildPid = pcntl_fork();
    if ($gotChildPid > 0) {
        //parent
        continue;
    } else {
        //child
        confirmRegionItem($province_code, 1, $province_name, 'province', $province_name);

        $queue = [];
        $queue_item = [
            'url' => $cityPageURL,
            'code' => $province_code,
            'type' => 'province',
            'address' => $province_name,
        ];
        array_unshift($queue, $queue_item);

        break;
    }
}

// 2. load elements

while (count($queue) > 0) {
    $item = array_shift($queue);

    $now = date('Y-m-d H:i:s');
    echo "[{$now}] [" . getmypid() . "] Took one from queue [{$item['address']}] and seek children, " . count($queue) . " left...";

    $elementMatches = parseNextLevelElementsInPage($item['url'], $parseError);
    if ($elementMatches) {
        for ($elementIndex = 0; $elementIndex < count($elementMatches[0]); $elementIndex++) {
            $elementType = $elementMatches[1][$elementIndex];
            $elementSubURL = $item['url'] . '/../' . $elementMatches[2][$elementIndex];
            $elementCode = truncateRegionIdByType($elementMatches[3][$elementIndex], $elementType);
            $elementName = $elementMatches[4][$elementIndex];

            $elementAddress = $item['address'] . $elementName;

            if (!empty($elementMatches[2][$elementIndex]) && in_array($elementType, [
                    'country', 'province', 'city', 'county',
                    // 'town','village',
                ])) {
                $queue_item = [
                    'url' => $elementSubURL,
                    'code' => $elementCode,
                    'type' => $elementType,
                    'address' => $elementAddress,
                ];
                array_unshift($queue, $queue_item);
            }

            confirmRegionItem($elementCode, $item['code'], $elementName, $elementType, $elementAddress);
        }
        echo "done" . PHP_EOL;
    } else {
        echo "failed for URL: " . $item['url'] . PHP_EOL;
        echo $parseError . PHP_EOL;
    }
}

echo "PID OVER : " . getmypid() . PHP_EOL;