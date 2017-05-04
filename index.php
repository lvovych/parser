<?php

require __DIR__.'/vendor/autoload.php';

$products = [];
/**
 * @param $url
 * @return array
 */
function getPages ($url) {
    $result=[];
    array_push($result,$url);

    $html = file_get_html($url);
    $links = $html->find('div.links');
    $links = $links[0]->children;
    foreach ($links as $link) {
        if ( $link->attr['href'] && $link->nodes[0]->_[4] !== '&gt;' && $link->nodes[0]->_[4] !== '&gt;|' ) {
            array_push($result,$link->attr['href']);
        }
    }
    return $result;
}

/**
 * @param $products
 */
function setExcel ($products) {
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Назва');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Вхідна ціна');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Ціна роздрібна');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Ціна мінімальна');
    $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Тип ціни');
//    $objPHPExcel->getActiveSheet()->setCellValue('F1', 'Картинка');

    $i=2;
    foreach ($products as $product) {
        $objPHPExcel->getActiveSheet()->setCellValue("A$i", $product['name']);
        $objPHPExcel->getActiveSheet()->setCellValue("B$i", bcmul($product['price'],'0.76',2));
        $objPHPExcel->getActiveSheet()->setCellValue("C$i", $product['price']);
        $objPHPExcel->getActiveSheet()->setCellValue("D$i", bcmul($product['price'],'0.82',2));
        $objPHPExcel->getActiveSheet()->setCellValue("E$i", $product['price_type']);
//        $objPHPExcel->getActiveSheet()->setCellValue("F$i", $product['image']);
        $i++;
    }

    $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
    $objWriter->save("Kablex.xlsx");
}
$pages = getPages('http://kablex.net.ua/m-kableks');

foreach ($pages as $page) {
    $html = file_get_html($page);
    $articles = $html->find('div.product-list');
    $articles = $articles[0]->children;
    foreach ($articles as $article) {
        foreach ($article->children as $div) {
            switch ($div->attr['class']) {
                case 'image':
                    $product['image'] = $div->nodes[1]->nodes[0]->attr['src'];
                    break;
                case 'name':
                    $product['name'] = $div->nodes[0]->nodes[0]->_[4];
                    break;
                case 'price':
                    $product['price'] = trim(substr(preg_replace("/(\s|\t|\r|\n|грн)+/",'', $div->nodes[0]->_[4]),0,-2));
                    $product['price_type'] = trim($div->nodes[1]->nodes[0]->_[4]);
                    break;
            }
        }
        array_push($products,$product);
    }
}

setExcel($products);

