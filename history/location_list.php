<?php
require_once('../system/function.php');
require_once('../system/database.php');

$list_num = 40;//每頁顯示數量
$cQmacaddr = '';
$sqle = '';
if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $cQmacaddr = $_POST['Qmacaddr'];
}
else
{
    $cQmacaddr = $_GET['Qmacaddr'];
}
if($cQmacaddr != '')
{
	$macnew = macaddr2sql($cQmacaddr);
	if($macnew != '') $sqle = ' and ale_location.sta_eth_mac in('.$macnew.') ';
}

//building_id
$sql = "SELECT building_id FROM krystal.ale_building where building_name = 'Library'";
$rs = $db->query($sql) or die("building_id");
list($dbuilding_id) = $rs->fetch(PDO::FETCH_NUM);
$rs = null;

//floor_id
$sql = "SELECT floor_id,floor_latitude,floor_longitude,floor_img_width,floor_img_length 
FROM krystal.ale_floor where building_id = '$dbuilding_id' and floor_name = 'floor2'";
$rs = $db->query($sql) or die("building_id");
list($dfloor_id,$dfloor_latitude,$dfloor_longitude,$dfloor_img_width,$dfloor_img_length) = $rs->fetch(PDO::FETCH_NUM);
$rs = null;

$sql = "SELECT ale_location.sta_eth_mac,sta_location_x,sta_location_y,longitude,latitude,associated,
ale_location.updatetime as updatetime_loc,ale_location.createtime as createtime_loc,
username,role,device_type,ap_name,ale_station.updatetime as updatetime_sta,ale_station.createtime as createtime_sta
FROM krystal.ale_location 
left join krystal.ale_station on ale_location.sta_eth_mac = ale_station.sta_eth_mac
where floor_id = '$dfloor_id' $sqle";
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
		report($text,$sqle,$start,$list_num,$dfloor_id);
    }
    elseif(strpos($text,"<!--pagination-->") !== FALSE)
	{
        $text = str_replace('<!--pagination-->','',$text);
        $url = "../history/location_list.php";
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

function report($txt,$sqle,$start,$list_num,$dfloor_id)
{
	global $db;
    
    //列表
    $sql = "SELECT ale_location.sta_eth_mac,sta_location_x,sta_location_y,longitude,latitude,associated,
    ale_location.updatetime as updatetime_loc,ale_location.createtime as createtime_loc,
    username,role,device_type,ap_name,ale_station.updatetime as updatetime_sta,ale_station.createtime as createtime_sta
    FROM krystal.ale_location 
    left join krystal.ale_station on ale_location.sta_eth_mac = ale_station.sta_eth_mac
    where floor_id = '$dfloor_id' $sqle
    order by createtime_loc desc,ale_location.sta_eth_mac
    LIMIT $start,$list_num";
    $rs = $db->query($sql) or die('列表發生錯誤');
    while($row = $rs->fetch(PDO::FETCH_ASSOC))
    {
        $dsta_eth_mac = $row['sta_eth_mac'];
        $dsta_location_x = $row['sta_location_x'];
        $dsta_location_y = $row['sta_location_y'];
        $dlongitude = $row['longitude'];
        $dlatitude = $row['latitude'];
        $dassociated = $row['associated'];
        $dupdatetime_loc = $row['updatetime_loc'];
        $dcreatetime_loc = $row['createtime_loc'];
        
        $dusername = $row['username'];
        $drole = $row['role'];
        $ddevice_type = $row['device_type'];
        $dap_name = $row['ap_name'];
        $dupdatetime_sta = $row['updatetime_sta'];
        $dcreatetime_sta = $row['createtime_sta'];

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
        $texts = str_replace('$sta_eth_mac',$dsta_eth_macN,$texts);
        $texts = str_replace('$sta_location_x',$dsta_location_x,$texts);
        $texts = str_replace('$sta_location_y',$dsta_location_y,$texts);
        $texts = str_replace('$longitude',$dlongitude,$texts);
        $texts = str_replace('$latitude',$dlatitude,$texts);
        $texts = str_replace('$associated',$dassociatedN,$texts);
        $texts = str_replace('$updatetime_loc',$dupdatetime_loc,$texts);
        $texts = str_replace('$createtime_loc',$dcreatetime_loc,$texts);
        
        $texts = str_replace('$username',$dusername,$texts);
        $texts = str_replace('$role',$drole,$texts);
        $texts = str_replace('$device_type',$ddevice_type,$texts);
        $texts = str_replace('$ap_name',$dap_name,$texts);
        $texts = str_replace('$updatetime_sta',$dupdatetime_sta,$texts);
        $texts = str_replace('$createtime_sta',$dcreatetime_sta,$texts);
	    echo $texts;
    }
    $rs = null;
}
?>