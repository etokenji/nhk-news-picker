<?php
const newKiji = "https://www3.nhk.or.jp/news/json16/new_001.json";
const baseUrl = "https://www3.nhk.or.jp/news/";

// 新着記事情報取得
$jsonObj = file_get_contents(newKiji);

if($jsonObj == false){
  error_log("http exception." . $e);
  return;
}

$array = json_decode($jsonObj, true);
$items = $array["channel"]["item"];
$dateList[] = null;

// ソート基準の日付を全て取得
foreach ($items as $item => $value){
  $convDate = strtotime($value["pubDate"]);
  if($convDate == -1)
  {
      error_log("convert datetime exception. value['pubDate']:" . $value["pubDate"]);
      return;
  }
  
  $convDate = new DateTime(date(DateTimeInterface::ATOM , $convDate));
  $convDate= $convDate->setTimezone(new DateTimeZone('Asia/Tokyo'));
  $dateList[$item] = $convDate;
}
unset($value);

// 日付で降順に
array_multisort( $dateList, SORT_DESC, $items);

// メール本文作成
$message = "";
foreach ($items as $item => $value){
   $message .= "【" . $value["title"] . "】\n" 
              . "URL : " . baseUrl . $value["link"] . "\n"
              . "投稿日時 : " . $dateList[$item]->format("Y/m/d H:i:s") . "\n\n" ;
 }

// メール送信
$now = date("Y/m/d H:i:s");
$to = "sample@gmail.com";
$subject = "NHK NEWS [" . $now . "]";
$headers = "From:sample@gmail.com";
 
mail($to, $subject, $message, $headers);

