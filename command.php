<?php
$botToken = /*토큰*/;
$website = "https://api.telegram.org/bot".$botToken;
$update = file_get_contents('php://input');
$update = json_decode($update, TRUE);
$chatId = $update["message"]["chat"]["id"];
$message = $update["message"]["text"];
if(isset($_GET["chat_id"]) && isset($_GET["message"])) {
    $chatId = $_GET["chat_id"];
    $message = $_GET["message"];
}
function sendMessage($chatId, $message) {
    $url = $GLOBALS["website"]."/sendMessage?chat_id=".$chatId."&text=".urlencode($message);
    file_get_contents($url);
}
function sendPhoto($chatId, $message) {
    $url = $GLOBALS["website"]."/sendPhoto?chat_id=".$chatId."&photo=".urlencode($message);
    file_get_contents($url);
}
function sendSticker($chatId, $message) {
    $url = $GLOBALS["website"]."/sendSticker?chat_id=".$chatId."&sticker=".urlencode($message);
    file_get_contents($url);
}
function sendAnimation($chatId, $message) {
    $url = $GLOBALS["website"]."/sendAnimation?chat_id=".$chatId."&animation=".urlencode($message);
    file_get_contents($url);
}
function sendMediaGroup($chatId, $message) {
    $url = $GLOBALS["website"]."/sendMediaGroup";
    $context  = stream_context_create(array('http' => array(
        'method'=>'POST',
        'header' => "Content-Type:application/x-www-form-urlencoded\r\n",
        'content' => http_build_query(array(
            'chat_id' => $chatId,
            'media' => $message
        ))
    )));
    $url = file_get_contents($url, false, $context);
    if($url == false) {
        sendSticker($chatId, "CAACAgIAAxkBAAOfXojQmWzPwDj2eQ23wFtqeslcZAYAArwAA_a8fgP4BNITBEk8kxgE");
        sendMessage($chatId, "업로드 실패");
    }
}
function readstatususer($value) {
    $xml = simplexml_load_file('status.xml');
    $value = "id".$value;
    return $xml->$value->status;
}
function addstatususer($chatId, $value1, $value2) {
    $xml = simplexml_load_file('status.xml');
    $id = "id".$chatId;
    $xml->$id->$value1 = $value2;
    $xml->asXML('status.xml');
}
function weather() {
    $url = "http://www.weather.go.kr/weather/forecast/mid-term-rss3.jsp?stnId=108";
    $result = simplexml_load_file($url);
    $date = $result->channel->item->title;
    $text = $result->channel->item->description->header->wf;
    $text = str_replace("<br />", "\n", $text);
    return $date."\n\n".$text;
}
function microweather($name) {
    $context  = stream_context_create(array('http' => array('header' => 'Accept: application/xml')));
    $url = "http://openapi.airkorea.or.kr/openapi/services/rest/ArpltnInforInqireSvc/getMsrstnAcctoRltmMesureDnsty?stationName=$name&dataTerm=DAILY&pageNo=1&numOfRows=1&ServiceKey=/*토큰*/&ver=1.3";


    $xml = file_get_contents($url, false, $context);
    $xml = simplexml_load_string($xml);
    if(!($xml->body->items->item->count())) {
        $text = "위치가 틀려요!";
    }
    else {
    	$time = $xml->body->items->item->dataTime->__toString();
    	$text = $time."에 측정된 자료에요!\n\n";

		$value = $xml->body->items->item->khaiValue->__toString();
        $text = $text."통합대기환경수치는 '".$value."'으로 ";
        $value = (int) $value;
        if($value <= 50) {
            $text = $text."'좋음' 이에요!\n'대기오염 관련 질환자군에서도 영향이 유발되지 않을 수준' 이에요!\n\n";
        }
        else if($value <= 100) {
            $text = $text."'보통' 이에요!\n'환자군에게 만성 노출시 경미한 영향이 유발될 수 있는 수준' 이에요!\n\n";
        }
        else if($value <= 250) {
            $text = $text."'나쁨' 이에요!\n'환자군 및 민감군(어린이, 노약자 등)에게 유해한 영향 유발, 일반인도 건강상 불쾌감을 경험할 수 있는 수준' 이에요!\n\n";
        }
        else {
            $text = $text."'매우나쁨' 이에요!\n'환자군 및 민감군에게 급성 노출시 심각한 영향 유발, 일반인도 약한 영향이 유발될 수 있는 수준' 이에요!\n\n";
        }

        $value = $xml->body->items->item->pm10Value24->__toString();
        $text = $text."미세먼지는 '".$value."'으로 ";
        $value = (int) $value;
        if($value <= 30) {
            $text = $text."'좋음'";
        }
        else if($value <= 80) {
            $text = $text."'보통'";
        }
        else if($value <= 150) {
            $text = $text."'나쁨'";
        }
        else {
            $text = $text."'매우나쁨'";
        }
        $text = $text." 이에요!\n";

        $value = $xml->body->items->item->pm25Value24->__toString();
        $text = $text."초미세먼지는 '".$value."'으로 ";
        $value = (int) $value;
        if($value <= 15) {
            $text = $text."'좋음'";
        }
        else if($value <= 35) {
            $text = $text."'보통'";
        }
        else if($value <= 75) {
            $text = $text."'나쁨'";
        }
        else {
            $text = $text."'매우나쁨'";
        }
        $text = $text." 이에요!\n";
    }
    return $text;
}
function getnews($query) {
    $query = urlencode($query);
    $context  = stream_context_create(array('http' => array(
        'header' => "Accept: application/xml\r\n" .
                    "X-Naver-Client-Id: /*토큰*/\r\n" .
                    "X-Naver-Client-Secret: /*토큰*/\r\n"
    )));
    $url = "https://openapi.naver.com/v1/search/news.xml?query=".$query."&display=3&start=1&sort=sim";

    $xml = file_get_contents($url, false, $context);
    $xml = simplexml_load_string($xml);

    if(!($xml->channel->display->__toString())) {
        $text = "검색결과가 없어요!";
    }
    else {
        $text = "- ".$xml->channel->item[0]->title->__toString();
        $text = $text." [".$xml->channel->item[0]->pubDate->__toString()."]\n";
        $text = $text.$xml->channel->item[0]->originallink->__toString()."\n\n";
        $text = $text."- ".$xml->channel->item[1]->title->__toString();
        $text = $text." [".$xml->channel->item[1]->pubDate->__toString()."]\n";
        $text = $text.$xml->channel->item[1]->originallink->__toString()."\n\n";
        $text = $text."- ".$xml->channel->item[2]->title->__toString();
        $text = $text." [".$xml->channel->item[2]->pubDate->__toString()."]\n";
        $text = $text.$xml->channel->item[2]->originallink->__toString()."\n\n";

        $text = str_replace("<b>", '"', $text);
        $text = str_replace("</b>", '"', $text);
        $text = str_replace("&quot;", '"', $text);
    }
    return $text;
}
function getencyc($query) {
    $query = urlencode($query);
    $context  = stream_context_create(array('http' => array(
        'header' => "Accept: application/xml\r\n" .
                    "X-Naver-Client-Id: /*토큰*/\r\n" .
                    "X-Naver-Client-Secret: /*토큰*/\r\n"
    )));
    $url = "https://openapi.naver.com/v1/search/encyc.xml?query=$query&display=3&start=1&sort=sim";
    $xml = file_get_contents($url, false, $context);
    $xml = simplexml_load_string($xml);
    if($xml->channel->total->__toString() == "0") {
        $text = "검색결과가 없어요!";
    }
    else {
        $text = "";
        $text = $text.$xml->channel->item[0]->title->__toString()."\n";
        $text = $text.$xml->channel->item[0]->description->__toString()."\n";
        $text = $text.$xml->channel->item[0]->link->__toString()."\n\n";
        $text = $text.$xml->channel->item[1]->title->__toString()."\n";
        $text = $text.$xml->channel->item[1]->description->__toString()."\n";
        $text = $text.$xml->channel->item[1]->link->__toString()."\n\n";
        $text = $text.$xml->channel->item[2]->title->__toString()."\n";
        $text = $text.$xml->channel->item[2]->description->__toString()."\n";
        $text = $text.$xml->channel->item[2]->link->__toString()."\n\n";
        $text = str_replace("<b>", '"', $text);
        $text = str_replace("</b>", '"', $text);
        $text = str_replace("&quot;", '"', $text);
    }
    return $text;
}
function viewweather() {
    $xml = simplexml_load_file('weather.xml');
    $text = "";
    foreach ($xml as $key => $node) {
        $text = $text.$node->name;
        $text = $text." : ";
        $text = $text.$node->x;
        $text = $text.", ";
        $text = $text.$node->y;
        $text = $text."\n";
    }
    return $text;
}
function addweather($value, $xnum, $ynum) {
    $xml = simplexml_load_file('weather.xml');
    if($xml->$value == "") {
        $xml->$value->name = $value;
        $xml->$value->x = $xnum;
        $xml->$value->y = $ynum;
        $xml->asXML('weather.xml');
        return $value."을(를) 저장!\n끝내시려면 /종료 해주세요.";
    }
    else {
        return "위치 정보가 이미 있어요.";
    }
}
function txtlog($value, $value2) {
    if($value2 == "add") {
        $file_handle = fopen($value, "r");
        (int)$linetext = fgets($file_handle);
        ++$linetext;
        fclose($file_handle);
        $fh = fopen($value, 'w') or die("can't open file");
        fwrite($fh, $linetext);
        fclose($fh); 
    }
    if($value2 == "reset") {
        fclose($file_handle);
        $fh = fopen($value, 'w') or die("can't open file");
        fwrite($fh, "0");
        fclose($fh);
    }
    if($value2 == "read") {
        $file_handle = fopen($value, "r");
        $linetext = fgets($file_handle);
        fclose($file_handle);
    }
    return $linetext;
}
function startnumgame($value, $randomnum) {
    $xml = simplexml_load_file('status.xml');
    $value = "id".$value;
    $xml->$value->numgame->randomNum = mt_rand(1, $randomnum);
    $xml->$value->numgame->num = 10;
    $xml->asXML('status.xml');
}
function numgame($name, $num) {
    $xml = simplexml_load_file('status.xml');
    $value = "id".$name;
    $i = (int)$xml->$value->numgame->randomNum;
    $xml->$value->numgame->inputnum = $num;
    $lastnum = (int)$xml->$value->numgame->num;

    if((int)$i < (int)$num) {
        sendMessage($name, "다운이에요! 남은 횟수는 ".$lastnum."회!");
    }
    if((int)$i > (int)$num) {
        sendMessage($name, "업이에요! 남은 횟수는 ".$lastnum."회!");
    }
    if((int)$i == (int)$num) {
        $xml->$value->status = "normal";
        $chance = 11 - $xml->$value->numgame->num;
        sendMessage($name, "정답!! 추카추카! 시도횟수 : ".$chance);
        sendMessage($name, "게임종료!");
    }
    else {
        $xml->$value->numgame->num = $xml->$value->numgame->num - 1;
        if((int)$lastnum <= 0) {
            $xml->$value->status = "normal";
            sendMessage($name, "게임오버!");
        }
    }
    $xml->asXML('status.xml');
}
function starttactgame($value, $value2) {
    $xml = simplexml_load_file('status.xml');
    $value = "id".$value;
    $xml->$value->tactgame->lastnum = 0;
    $xml->$value->tactgame->max = $value2;
    $xml->asXML('status.xml');
}
function tactgame($name, $num) {
    $xml = simplexml_load_file('status.xml');
    $value = "id".$name;
    $max = $xml->$value->tactgame->max;
    if($xml->$value->tactgame->lastnum == $num) {
        sendMessage($name, "땡! 종료!");
        $xml->$value->status = "normal";
        $xml->asXML('status.xml');
    }
    else if(($xml->$value->tactgame->lastnum + 1) == $num) {
        $xml->$value->tactgame->lastnum = $num;
        $xml->asXML('status.xml');
    }
    else {
        sendMessage($name, "숫자가 틀려요!");
    }
    if((int)$xml->$value->tactgame->lastnum >= (int)$max) {
        sendMessage($name, "승리!");
        $xml->$value->status = "normal";
        $xml->asXML('status.xml');
    }
}
function konachansearch($chatId, $address) {
    $xml = simplexml_load_file('status.xml');
    $id = "id".$chatId;
    $h = $xml->$id->konachan;
    $address = urlencode($address);
    getimgG($chatId, "site:https://konachan.$h/ $address");
}
function namuwiki($address) {
    include_once('simple_html_dom.php');
    $html = file_get_html("https://namu.wiki/w/".urlencode($address));
    if(!count($html->find('div[class=wiki-heading-content]'))) {
        return "문서가 없습니다.";
    }
    $text = $address." : 개요\n\n";
    $text = $text.strip_tags($html->find('div[class=wiki-heading-content]')[0]);
    $text = str_replace('&#39;', "", $text);
    return $text;
}
function getimgG($chatId, $address) {
    $context  = stream_context_create(array('http' => array('header' => 'Accept: application/xml')));
    $address = urlencode($address);
    $url = "https://www.googleapis.com/customsearch/v1?key=/*토큰*/&cx=013704670230599665518:65iqzz7hmok&searchType=image&num=5&q=$address";

    $json = file_get_contents($url, false, $context);
    if($json == false) {
        sendSticker($chatId, "CAACAgIAAxkBAAOfXojQmWzPwDj2eQ23wFtqeslcZAYAArwAA_a8fgP4BNITBEk8kxgE");
        sendMessage($chatId, "연결 실패");
    }
    else {
        $json = json_decode($json, TRUE);
        $photolist = array();
        for ($i=0; $i < 5; $i++) {
            if(mb_substr(get_headers($json['items'][$i]['link'], 1)["Content-Type"], 0, 5) == "image") {
                $array = array(
                    'type' => "photo", 
                    'media' => $json['items'][$i]['link'],
                    'caption' => $json['items'][$i]['title']."\n".$json['items'][$i]['image']['contextLink']
                );
                array_push($photolist, $array);
            }
        }
        $photolist = json_encode($photolist);
        $photolist = iconv("UTF-8", "ISO-8859-1", $photolist);
        sendMediaGroup($chatId, $photolist);
    }
}
function getimgN($chatId, $query) {
    $query = urlencode($query);
    $context  = stream_context_create(array('http' => array(
        'header' => "Accept: application/xml\r\n" .
                    "X-Naver-Client-Id: /*토큰*/\r\n" .
                    "X-Naver-Client-Secret: /*토큰*/\r\n"
    )));
    $url = "https://openapi.naver.com/v1/search/image.xml?query=".$query."&display=5&start=1&sort=sim";

    $xml = file_get_contents($url, false, $context);
    if($xml == false) {
        sendSticker($chatId, "CAACAgIAAxkBAAOfXojQmWzPwDj2eQ23wFtqeslcZAYAArwAA_a8fgP4BNITBEk8kxgE");
        sendMessage($chatId, "연결 실패");
    }
    else {
        $xml = simplexml_load_string($xml);
        $photolist = array();
        for ($i=0; $i < 5; $i++) {
            if(mb_substr(get_headers($json['items'][$i]['link'], 1)["Content-Type"], 0, 5) == "image") {
                $array = array(
                    'type' => "photo", 
                    'media' => $json['items'][$i]['link'],
                    'caption' => $json['items'][$i]['title']."\n".$json['items'][$i]['image']['contextLink']
                );
                array_push($photolist, $array);
            }
        }
        $photolist = json_encode($photolist);
        $photolist = iconv("UTF-8", "ISO-8859-1", $photolist);
        sendMediaGroup($chatId, $photolist);
    }
}

if(readstatususer($chatId) == null) {
    addstatususer($chatId, "status", "normal");
}
if(readstatususer($chatId) == "normal") {
    $cutm = mb_substr($message, 1, 4, 'UTF-8');
    if($cutm == "눈치게임") {
        $num = mb_substr($message, 6, 4, 'UTF-8');
        if((int)$num == 0) {
            sendMessage($chatId, "숫자를 적어 주세요!\n예시) 눈치게임 5 (5회 실행)");
        }
        else {
            addstatususer($chatId, "status", "tactgame");
            starttactgame($chatId, (int)$num);
            sendMessage($chatId, "눈치게임을 시작 합니당!");
        }
    }
    if($cutm == "숫자게임") {
        $num = mb_substr($message, 6, 4, 'UTF-8');
        if((int)$num == 0) {
            addstatususer($chatId, "status", "numgame");
            startnumgame($chatId, 100);
            sendMessage($chatId, "숫자게임을 시작 합니당!\n0 ~ 100 숫자중 정답을 맞춰보세요!");
        }
        else {
            addstatususer($chatId, "status", "numgame");
            startnumgame($chatId, $num);
            sendMessage($chatId, "숫자게임을 시작 합니당!\n0 ~ ".$num." 숫자중 정답을 맞춰보세요!");
        }
    }
    if($cutm == "미세먼지") {
        $locationm = mb_substr($message, 6, 99, 'UTF-8');
        if($locationm == "") {
            sendSticker($chatId, "CAACAgIAAxkBAAOfXojQmWzPwDj2eQ23wFtqeslcZAYAArwAA_a8fgP4BNITBEk8kxgE");
            sendMessage($chatId, "지역을 말해주세요!");
        }
        else {
            sendMessage($chatId, microweather($locationm));
        }
    }
    if($cutm == "나무위키") {
        $locationm = mb_substr($message, 6, 99, 'UTF-8');
        if($locationm == "") {
            sendSticker($chatId, "CAACAgIAAxkBAAOfXojQmWzPwDj2eQ23wFtqeslcZAYAArwAA_a8fgP4BNITBEk8kxgE");
            sendMessage($chatId, "검색어를 말해주세요!");
        }
        else {
            sendMessage($chatId, namuwiki($locationm));
        }
    }
    $cutm = mb_substr($message, 1, 3, 'UTF-8');
    if($cutm == "코나쟝") {
        $value = mb_substr($message, 5, 99, 'UTF-8');
        if($value == "net") {
            addstatususer($chatId, "konachan", "net");
            sendMessage($chatId, "net으로 설정 됬습니다.");
            return;
        } else if($value == "com") {
            addstatususer($chatId, "konachan", "com");
            sendMessage($chatId, "com으로 설정 됬습니다.");
            return;
        }else if($value == "") {
            sendSticker($chatId, "CAACAgIAAxkBAAOfXojQmWzPwDj2eQ23wFtqeslcZAYAArwAA_a8fgP4BNITBEk8kxgE");
            sendMessage($chatId, "검색어를 말해주세요!");
        }else {
            konachansearch($chatId, $value);
        }
    }
    if($cutm == "네이버") {
        $value = mb_substr($message, 4, 99, 'UTF-8');
        if($value == "") {
            sendSticker($chatId, "CAACAgIAAxkBAAOfXojQmWzPwDj2eQ23wFtqeslcZAYAArwAA_a8fgP4BNITBEk8kxgE");
            sendMessage($chatId, "검색어를 말해주세요!");
        }
        else {
            getimgN($chatId, $value);
        }
    }
    $cutm = mb_substr($message, 1, 2, 'UTF-8');
    if($cutm == "뉴스") {
        $locationm = mb_substr($message, 4, 99, 'UTF-8');
        if($locationm == "") {
            sendSticker($chatId, "CAACAgIAAxkBAAOfXojQmWzPwDj2eQ23wFtqeslcZAYAArwAA_a8fgP4BNITBEk8kxgE");
            sendMessage($chatId, "검색어를 말해주세요!");
        }
        else {
            sendMessage($chatId, getnews($locationm));
        }
    }
    if($cutm == "사진") {
        $value = mb_substr($message, 4, 99, 'UTF-8');
        if($value == "") {
            sendSticker($chatId, "CAACAgIAAxkBAAOfXojQmWzPwDj2eQ23wFtqeslcZAYAArwAA_a8fgP4BNITBEk8kxgE");
            sendMessage($chatId, "검색어를 말해주세요!");
        }
        else {
            getimgG($chatId, $value);
        }
    }
    if($cutm == "사전") {
        $value = mb_substr($message, 4, 99, 'UTF-8');
        if($value == "") {
            sendSticker($chatId, "CAACAgIAAxkBAAOfXojQmWzPwDj2eQ23wFtqeslcZAYAArwAA_a8fgP4BNITBEk8kxgE");
            sendMessage($chatId, "검색어를 말해주세요!");
        }
        else {
        	sendMessage($chatId, getencyc($value));
        }
    }
    if($cutm == "공기") {
        $locationm = mb_substr($message, 4, 99, 'UTF-8');
        if($locationm == "") {
            sendSticker($chatId, "CAACAgIAAxkBAAOfXojQmWzPwDj2eQ23wFtqeslcZAYAArwAA_a8fgP4BNITBEk8kxgE");
            sendMessage($chatId, "지역을 말해주세요!");
        }
        else {
            sendMessage($chatId, microweather($locationm));
        }
    }
    $cutm = mb_substr($message, 1, 1, 'UTF-8');
    if($cutm == "i") {
        $value = mb_substr($message, 3, 99, 'UTF-8');
        if($value == "") {
            sendSticker($chatId, "CAACAgIAAxkBAAOfXojQmWzPwDj2eQ23wFtqeslcZAYAArwAA_a8fgP4BNITBEk8kxgE");
            sendMessage($chatId, "검색어를 말해주세요!");
        }
        else {
            getimgG($chatId, $value);
        }
    }

    switch($message) {
        case "/help":
                sendMessage($chatId, "/델쟝 머해? : 상태를 출력합니다.\n/델쟝 귀여워! : 델쟝을 커여워 합니다.\n/날씨 : 종합예보를 출력합니다.\n/미세먼지 [지역] : 지역 미세먼지를 출력합니다.\n/숫자게임 [nnnn] : 0 ~ nnnn 숫자 사이의 숫자게임을 시작합니다. 기본값은 100입니다.\n/눈치게임 : 눈치게임을 시작합니다.\n/코나쟝 [query] : konachan에서 이미지를 검색해 출력합니다.\n/나무위키 [query] : 해당 문서의 개요를 출력합니다.\n/뉴스 [query] : 검색어의 뉴스를 출력합니다.\n/i [query] : 구글 이미지 검색을 하여 출력합니다.\n/사진 [query] : 구글 이미지 검색을 하여 출력합니다.\n/사전 [query] : 네이버 백과사전 검색 결과를 출력합니다.");
                break;
        case "/ver":
        		$file = 'command.php';
				$lastmod = date("Y.m.d H:i:s", filemtime($file));
                sendMessage($chatId, $lastmod." 업데이트");
                break;
        case "/start":
                sendMessage($chatId, "안녕하세요! 취미로 만드는 bot이에요!\n/help 를 입력하면 명령어 목록을 볼수 있어요!");
                sendAnimation($chatId, "CgACAgIAAxkBAAOdXojKkKYJC3GGFbwU9W--dDjDYG8AAiEHAAIJYkBI3_jsEj6-xPoYBA");
                break;
        case "/델쟝":
                sendMessage($chatId, "넹!");
                break;
        case "/델쟝 귀여워!":
                $text = txtlog("kawaiilog.txt", "add");
                sendMessage($chatId, $text." 번 칭찬 받았당!! >_<");
                break;
        case "/칭찬 초기화":
                $text = txtlog("kawaiilog.txt", "reset");
                sendMessage($chatId, "초기화 완료!");
                break;
        case "/나 핸드폰 떨궜어":
                $text = txtlog("droplog.txt", "add");
                sendMessage($chatId, $text."번 떨궜어요. ㅠ_ㅠ");
                break;
        case "/나 핸드폰 몇번 떨궜어?":
                $text = txtlog("droplog.txt", "read");
                sendMessage($chatId, $text."번이요. ㅠ_ㅠ");
                break;
        case "/핸드폰 초기화":
                $text = txtlog("droplog.txt", "reset");
                sendMessage($chatId, "초기화 완료!");
                break;
        case "/델쟝 머해?":
                sendMessage($chatId, "상태 : ".readstatususer($chatId));
                break;
        case "/니애미":
                sendSticker($chatId, "CAADAgADtwAD9rx-A0HZgaS62mgsAg");
                break;
        case "/히끅":
                sendSticker($chatId, "CAADAgADuwAD9rx-A3dQC8qy5s9PAg");
                break;
        case "/시발":
                sendSticker($chatId, "CAADAgADugAD9rx-A08sgEyNET4rAg");
                break;
        case "/일해라":
                sendSticker($chatId, "CAADAgADuQAD9rx-AylKRvZZ_r3CAg");
                break;
        case "/눙물":
                sendSticker($chatId, "CAADAgADuAAD9rx-A6JQa69vkbePAg");
                break;
        case "/좋아":
                sendSticker($chatId, "CAADBQADOAAD30osEYvNxYbjVczwAg");
                break;
        case "/띠용":
                sendSticker($chatId, "CAACAgIAAxkBAAOfXojQmWzPwDj2eQ23wFtqeslcZAYAArwAA_a8fgP4BNITBEk8kxgE");
                break;
        case "/날씨":
                sendMessage($chatId, weather());
                break;
        case "/백합":
                getimgG($chatId, "anime yuri");
                break;
        case "/이히히":
                sendAnimation($chatId, "CgACAgIAAxkBAAOdXojKkKYJC3GGFbwU9W--dDjDYG8AAiEHAAIJYkBI3_jsEj6-xPoYBA");
                break;
        case "/시간":
                sendMessage($chatId, date("Y-m-d h:i:s"));
                break;
        default:
    }
}
if(readstatususer($chatId) == "numgame") {
    $messagenum = substr($message, 1, 4);
    if(is_numeric($messagenum)) {
        numgame($chatId, $messagenum);
    }
    else {
        switch($message) {
            case "/help":
                    sendMessage($chatId, "/종료");
                    break;
            case "/델쟝 머해?":
                    sendMessage($chatId, "상태 : ".readstatususer($chatId));
                    break;
            case "/종료":
                    addstatususer($chatId, "status", "normal");
                    sendMessage($chatId, "게임종료!");
                    break;
            default:
        }
    }
}
if(readstatususer($chatId) == "tactgame") {
    $messagenum = substr($message, 1, 2);
    if(is_numeric($messagenum)) {
        tactgame($chatId, $messagenum);
    }
    else {
        switch($message) {
            case "/help":
                    sendMessage($chatId, "/종료");
                    break;
            case "/델쟝 머해?":
                    sendMessage($chatId, "상태 : ".readstatususer($chatId));
                    break;
            case "/종료":
                    addstatususer($chatId, "status", "normal");
                    sendMessage($chatId, "게임종료!");
                    break;
            default:
        }
    }
}
if(readstatususer($chatId) == "weatheredit") {
    if(mb_strlen($message, 'UTF-8') == 11) {
        $value = mb_substr($message, 1, 2, 'UTF-8');
        $xnum = mb_substr($message, 5, 2, 'UTF-8');
        $ynum = mb_substr($message, 8, 3, 'UTF-8');
        sendMessage($chatId, addweather($value, $xnum, $ynum));
    }
    else {
       switch($message) {
        case "/help":
                sendMessage($chatId, "- /종료\n- /위치(3)x좌표(2)y좌표:000 00 000");
                break;
        case "/델쟝 머해?":
                sendMessage($chatId, "상태 : ".readstatususer($chatId));
                break;
        case "/종료":
                addstatususer($chatId, "status", "normal");
                sendMessage($chatId, "종료!");
                break;
        default:
        } 
    }
}
?>