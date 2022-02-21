<?php
require_once('../system/function.php');
require_once('../system/database.php');

$cQmacaddr = '';
if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $cQmacaddr = $_POST['Qmacaddr'];
}
else
{
    $cQmacaddr = $_GET['Qmacaddr'];
}

$sqle = '';
if($cQmacaddr != '')
{
	$macnew = macaddr2sql($cQmacaddr);
	if($macnew != '') $sqle = ' and sta_eth_mac in('.$macnew.') ';
}

$list_num = 40;//每頁顯示數量
$sql = "SELECT sta_eth_mac
FROM krystal.ale_location_log 
where 1=1 $sqle";
$rs = $db->query($sql) or die('系統發生錯誤');
$cnt2 = $rs->rowCount();
$rs = null;
$pages = ceil($cnt2 / $list_num); //取得大於指定數的最小整數值，就是總頁數啦
if(!isset($_GET["page"]))
{ 
    $page = 1;//設定起始頁 
} 
else 
{ 
    $page = intval($_GET["page"]); //確認頁數只能夠是數值資料 
    $page = ($page > 0) ? $page : 1; //確認頁數大於零 
    $page = ($pages > $page) ? $page : $pages; //確認使用者沒有輸入太神奇的數字 
}
$start = ($page - 1) * $list_num; //每頁起始資料序號
if($start < 0) $start = 0;//防止沒資料出錯

$htmlarr = readhtml('');
foreach($htmlarr as $text)
{
	if(strpos($text,'<!--headercss-->')!== FALSE)
	{
		$text = str_replace('<!--headercss-->','',$text);
		include("../global/headercss.php");	
	}
	elseif(strpos($text,"<!--footerjs-->") !== FALSE)
	{
		$text = str_replace('<!--footerjs-->','',$text);
		include("../global/footerjs.php");	
	}
	elseif(strpos($text,"<!--copyright-->") !== FALSE)
	{
		$text = str_replace('<!--copyright-->','',$text);
		include("../global/copyright.php");	
	}
	elseif(strpos($text,"<!--userinfo-->") !== FALSE)
	{
		$text = str_replace('<!--userinfo-->','',$text);
		userinfo($text);
	}
	elseif(strpos($text,"<!--sidebaruser-->") !== FALSE)
	{
		$text = str_replace('<!--sidebaruser-->','',$text);
		sidebaruser($text);
	}
	elseif(strpos($text,"<!--sidebarmenu-->") !== FALSE)
	{
		$text = str_replace('<!--sidebarmenu-->','',$text);
		sidebarmenu($text);
	}
	elseif(strpos($text,"<!--contantheader-->") !== FALSE)
	{
		$text = str_replace('<!--contantheader-->','',$text);
		contantheader($text);
	}
	elseif(strpos($text,"<!--contantbreadcrumb-->") !== FALSE)
	{
		$text = str_replace('<!--contantbreadcrumb-->','',$text);
		contantbreadcrumb($text);
	}
	elseif(strpos($text,"<!--search-->") !== FALSE)
	{
		$text = str_replace('<!--search-->','',$text);
		search($text);
	}
	elseif(strpos($text,"<!--report-->") !== FALSE)
	{
		$text = str_replace('<!--report-->','',$text);
		report($text,$sqle,$start,$list_num);
    }
    elseif(strpos($text,"<!--pagination-->") !== FALSE)
	{
        $text = str_replace('<!--pagination-->','',$text);
        $url = "../history/history_list.php";
        $url .= "?Qmacaddr=".$cQmacaddr;
        $html = pagination($url,$page,$pages);
        echo $html;
	}
	else
	{
        $text = str_replace('$cnt1',$list_num,$text);
        $text = str_replace('$cnt2',$cnt2,$text);
		echo $text;
	}
}

function userinfo($txt)
{
	echo $txt;
}

function sidebaruser($txt)
{
	echo $txt;
}

function sidebarmenu($txt)
{
	echo $txt;
}

function contantheader($txt)
{
	echo $txt;
}

function contantbreadcrumb($txt)
{
	echo $txt;
}

function search($txt)
{
	global $cQmacaddr;

	$txt = str_replace('$Qmacaddr',$cQmacaddr,$txt);
	echo $txt;
}

function report($txt,$sqle,$start,$list_num)
{
	global $db;
    
    //列表
    $sql = "SELECT sta_eth_mac,sta_location_x,sta_location_y,associated,updatetime,createtime
    FROM krystal.ale_location_log 
    where 1=1 $sqle
    order by createtime desc,sta_eth_mac
    LIMIT $start,$list_num";
    $rs = $db->query($sql) or die('列表發生錯誤');
    while($row = $rs->fetch(PDO::FETCH_ASSOC))
    {
        $dsta_eth_mac = $row['sta_eth_mac'];
        $dsta_location_x = $row['sta_location_x'];
        $dsta_location_y = $row['sta_location_y'];
        $dassociated = $row['associated'];
        $dupdatetime = $row['updatetime'];
        $dcreatetime = $row['createtime'];

        $dsta_eth_macN = macaddrcomma($dsta_eth_mac);
        if($dassociated == 1)
        {
            $dassociatedN = 'Associated';
        }
        else
        {
            $dassociatedN = 'Unassociated';
        }

        $texts = $txt;
        $texts = str_replace('$createtime',$dcreatetime,$texts);
        $texts = str_replace('$updatetime',$dupdatetime,$texts);
        $texts = str_replace('$sta_eth_mac',$dsta_eth_macN,$texts);
        $texts = str_replace('$sta_location_x',$dsta_location_x,$texts);
        $texts = str_replace('$sta_location_y',$dsta_location_y,$texts);
        $texts = str_replace('$associated',$dassociatedN,$texts);
	    echo $texts;
    }
    $rs = null;
}
?>