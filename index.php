<?php
declare(strict_types = 1);
error_reporting(E_ALL);

function sendRequest(string $url, bool $isPost = false, array $params = [], $headers = []){
    if(!$headers) {
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
        ];
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_HEADER, 0);

    if ($isPost) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $curlResponse = curl_exec($ch);

    $curlResponseCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    if ($curlResponseCode !== 200 && !curl_errno($ch)){
        throw new RuntimeException('Error of cURL request. Response code: ' . $curlResponseCode);
    }

    $curlResponseDecode = json_decode($curlResponse, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException('Error of decode response. Error message: ' . json_last_error_msg());
    }

    return $curlResponseDecode;
}

function setFirstLetterToUppercase(string $variableWord): string {
    $variableWordLowercase = mb_strtolower($variableWord);

    $firstLetter = mb_substr($variableWordLowercase, 0, 1);
    $firstLetterUppercase = mb_strtoupper($firstLetter);
    $otherLetters = mb_substr($variableWordLowercase, 1);

    return $firstLetterUppercase . $otherLetters;
}

$apiLink = 'https://api.boxberry.ru/json.php?token=d6f33e419c16131e5325cbd84d5d6000&method=ListPoints&prepaid=1';
$response = sendRequest($apiLink);
$numberOfPoints = [];

foreach ($response as $points) {
    if (!isset($numberOfPoints[$points['CountryCode']])) {
        $numberOfPoints[$points['CountryCode']] = [
            'country_name' => $points['Country'],
            'number_of_points' => 0,
        ];
    }

    ++$numberOfPoints[$points['CountryCode']]['number_of_points'];
}

if(!$numberOfPoints) {
    return 'Данные не найдены';
}

$startMessage = 'Колличество ПВЗ в ';
$resultMessage = '';

foreach ($numberOfPoints as $countryCode => $countryData) {
    $resultMessage .= $startMessage . (setFirstLetterToUppercase($countryData['country_name'])) . ': ' . $countryData['number_of_points'] . '<br>';
}

echo $resultMessage;