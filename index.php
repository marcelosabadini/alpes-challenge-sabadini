<?php
/**
 * mod_rewrite is enable and you have to run like that:
 *    http://yourserver/veiculo/carro/marca/Citroen/modelo/67/ano1/2009/ano2/2010/usuario/todos
 * 
 * The params are exaclly the same from seminovosbh.com.br.
 * 
 * @todo
 *  - Implements using OOP
 *  - Do a loop and get results from all pages(pagination)
 *  - To get a car detail
 */
 
 //http://simplehtmldom.sourceforge.net/
include 'simple_html_dom.php';

$action = $_GET['action'];

if(!isset($action) || empty($action)){
    die('Ups...you have to tell us what you wanna search.');
}

$url_base = 'https://www.seminovosbh.com.br';
$url_curl = "$url_base/resultadobusca/index/$action";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url_curl);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$server_output = curl_exec ($ch);
curl_close ($ch);

$html = str_get_html($server_output);

$result = array();
foreach($html->find('dl.bg-busca') as $dl) {
    
    $more = array();
    foreach($dl->find('[class^=plano]') as $m){
        $text = preg_replace('/\s{2,}/', ', ', trim($m->plaintext));
        if(!empty($text))
            $more[] = $text;
    }

    $result[] = array(
        'title' => $dl->find('a img', 0)->alt,    
        'price' => $dl->find('.preco_busca', 0)->plaintext,    
        'year'  => preg_replace('/.*(\d{4}\/\d{4}).*/', '$1', implode(' ', $more)),    
        'accept_change'  => (preg_match('/Aceita Troca/', end($more)) ? 0 : 1),    
        'url'   => $url_base . $dl->find('a', 0)->href,    
        'thumb' => $dl->find('a img', 0)->src,    
        'plan'  => preg_replace('/.*plano(\w*)/', '$1', $dl->class),    
        'more_information'  => $more,
    );
}

echo json_encode($result);

?>