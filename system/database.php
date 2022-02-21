<?php
//設定時區
//date_default_timezone_set('Asia/Taipei'); 

//環境變數
$conf = array(	
	'db_host'=>'localhost',
	'db_user'=>'krystalale', //your DB account
	'db_pass'=>'welcome123', //your DB password
	'db_select'=>array('krystal'),
);
$db_connect = 'mysql:host='.$conf['db_host'].';dbname='.$conf['db_select'][0];

//PDO資料庫連線
try 
{
    //PDO::ATTR_PERSISTENT => true
    $db = new PDO($db_connect,$conf['db_user'],$conf['db_pass'],array(PDO::MYSQL_ATTR_INIT_COMMAND =>"SET NAMES UTF8"));

    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);//錯誤報告
    $db->setAttribute(PDO::ATTR_PERSISTENT, false);//長連接
    $db->setAttribute(PDO::ATTR_TIMEOUT, 1800);//指定超過的秒數
} 
catch(PDOException $e) 
{
    print "Error!: ".$e->getMessage()."<br/>";
    $db = null;
    die();
}
//pdo的錯誤訊息
//echo '<script>console.log("'.$db->errorCode().'")</script>';
?>