<?php

function save_image($inPath,$outPath) {
    $result = false;
    $in= @fopen($inPath, "rb");
    if ($in) {
        $out=  fopen($outPath, "wb");
        while ($chunk = fread($in,8192))
        {
            if (!empty($chunk)){
                fwrite($out, $chunk, 8192);
                $result = true;
            }
        }
        fclose($in);
        fclose($out);
    }
    return $result;

}
function getSku($filePath) {
    $result = [];
    $inputFile = fopen($filePath, "rt");
    if ($inputFile) {
        while (!feof($inputFile)) {
            $line = fgets($inputFile,30);
            if ($line !== "\n"){
                array_push($result,substr($line, 0, -1));
            }
        }
    }
    return $result;
}

$params = $_SERVER['argv'];
unset($params[0]);
$url='';
$input='';
$output='';
foreach ($params as $param) {
    $param=explode('=',$param);
    switch ($param[0]){
        case 'url':
            $url = $param[1];
            break;
        case 'input':
            $input = $param[1];
            break;
        case 'output':
            $output = $param[1];
            break;
    }
}
unset($params);
unset($param);

$artikuls = getSku($input);
$imagesCount = 0;
foreach ($artikuls as $artikul) {

    if (save_image($url . $artikul . '.jpg', $output . $artikul . '.jpg')){
        $imagesCount++;
    };
}
echo 'Downloaded: ' . $imagesCount;