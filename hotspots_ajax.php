<?php
//共用AJAX
require_once('system/function.php');
require_once('system/database.php');

$csearching = $_POST['searching'];
$cmac = $_POST['mac'];

$sqle = '';
$result = '';

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

//hotspot data example
/*
data: [
    {"x":32.975781250000004,"y":30.447309176672384,"Title":"Delhi","Message":"test1"},
    {"x":17.57578125,"y":61.15056818181818,"Title":"Mumbai","Message":"test2"},
    {"x":22,"y":22,"Title":"Mumbai2","Message":"test3"}
], 
*/
//綠點
if($csearching == 'green')
{
    if($cmac != '')
    {
        $macnew = macaddr2sql($cmac);
        if($macnew != '') $sqle = ' and ale_location.sta_eth_mac in('.$macnew.') ';
    }

    $i = 0;
    $greenarr = array();
    $sql = "SELECT ale_location.sta_eth_mac,sta_location_x,sta_location_y,error_level,associated,
    longitude,latitude,ale_location.updatetime as updateloc,username,role,device_type,sta_ip_address_addr,
    ap_name,ale_station.updatetime as updatesta,ale_location.createtime
    FROM krystal.ale_location 
    join krystal.ale_station on ale_location.sta_eth_mac = ale_station.sta_eth_mac
    where floor_id = '$dfloor_id' and associated = 1 and ap_name like 'LIB-2%' $sqle";
    $rs = $db->query($sql) or die('green dot');
    while($row = $rs->fetch(PDO::FETCH_ASSOC))
    {
        $dsta_eth_mac = $row['sta_eth_mac'];
        $dsta_location_x = $row['sta_location_x'];
        $dsta_location_y = $row['sta_location_y'];
        $derror_level = $row['error_level'];
        $dassociated = $row['associated'];
        $dlongitude = $row['longitude'];
        $dlatitude = $row['latitude'];
        $dupdateloc = $row['updateloc'];
        $dusername = $row['username'];
        $drole = $row['role'];
        $ddevice_type = $row['device_type'];
        $dsta_ip_address_addr = $row['sta_ip_address_addr'];
        $dap_name = $row['ap_name'];
        $dupdatesta = $row['updatesta'];
        $dcreatetime = $row['createtime'];

        //初始化
        $green_x = '';
        $green_y = '';
        $green_Title = '';
        $green_Message = '';

        //計算及塞入資料
        $green_x = $dsta_location_x / $dfloor_img_width * 100;
        $green_y = $dsta_location_y / $dfloor_img_length * 100;

        if($dassociated == 1)
        {
            $dassociatedN = 'Associated';
        }
        else
        {
            $dassociatedN = 'Unassociated';
        }

        $green_Title = macaddrcomma($dsta_eth_mac);
    
        if($dusername != '') $green_Message .= '使用者名稱:<br>'.$dusername.'<br>';
        if($ddevice_type != '') $green_Message .= '連線裝置:<br>'.$ddevice_type.'<br>';
        if($dap_name != '') $green_Message .= '連線AP:<br>'.$dap_name.'<br>';
        if($dassociatedN != '') $green_Message .= '連線狀況:<br>'.$dassociatedN.'<br>';
        if($drole != '') $green_Message .= 'role:<br>'.$drole.'<br>';
        if($dupdateloc != '') $green_Message .= 'location更新時間:<br>'.$dupdateloc.'<br>';
        if($dupdateloc != '') $green_Message .= 'station更新時間:<br>'.$dupdateloc.'<br>';
        if($dcreatetime != '') $green_Message .= '資料更新時間:<br>'.$dcreatetime;

        $greenarr[] = array(
            'x' => $green_x,
            'y' => $green_y,
            'Title' => $green_Title,
            'Message' => $green_Message
        );

        $i++;
    }
    $rs = null;

    //回傳JSON
    if(count($greenarr) > 0)
    {
        $result = json_encode($greenarr);
    }
    else
    {
        $result = '';
    }
    echo $result;
}

//Taggd data example
/*
var data = [
    Taggd.Tag.createFromObject({
        position: { x: 0.19, y: 0.4 },
        text: 'This is a tree',
    }),
    Taggd.Tag.createFromObject({
        position: { x: 0.5, y: 0.3 },
        text: 'Pretty sure this is also a tree',
    }),
    Taggd.Tag.createFromObject({
        position: { x: 0.775, y: 0.5 },
        text: 'Can you guess this one?',
    }),
];
*/
//紅點
if($csearching == 'red')
{
    if($cmac != '')
    {
        $macnew = macaddr2sql($cmac);
        if($macnew != '') $sqle = ' and ale_location.sta_eth_mac in('.$macnew.') ';
    }

    $i = 0;
    $redarr = array();
    $sql = "SELECT ale_location.sta_eth_mac,sta_location_x,sta_location_y,error_level,associated,
    longitude,latitude,ale_location.updatetime as updateloc,username,role,device_type,sta_ip_address_addr,
    ap_name,ale_station.updatetime as updatesta,ale_location.createtime
    FROM krystal.ale_location 
    left join krystal.ale_station on ale_location.sta_eth_mac = ale_station.sta_eth_mac
    where floor_id = '$dfloor_id' and associated = 0 $sqle";
    $rs = $db->query($sql) or die('red dot');
    while($row = $rs->fetch(PDO::FETCH_ASSOC))
    {
        $dsta_eth_mac = $row['sta_eth_mac'];
        $dsta_location_x = $row['sta_location_x'];
        $dsta_location_y = $row['sta_location_y'];
        $derror_level = $row['error_level'];
        $dassociated = $row['associated'];
        $dlongitude = $row['longitude'];
        $dlatitude = $row['latitude'];
        $dupdateloc = $row['updateloc'];
        $dusername = $row['username'];
        $drole = $row['role'];
        $ddevice_type = $row['device_type'];
        $dsta_ip_address_addr = $row['sta_ip_address_addr'];
        $dap_name = $row['ap_name'];
        $dupdatesta = $row['updatesta'];
        $dcreatetime = $row['createtime'];

        //初始化
        $red_x = '';
        $red_y = '';
        $red_text = '';

        //計算及塞入資料
        $red_x = $dsta_location_x / $dfloor_img_width;
        $red_y = $dsta_location_y / $dfloor_img_length;

        if($dassociated == 1)
        {
            $dassociatedN = 'Associated';
        }
        else
        {
            $dassociatedN = 'Unassociated';
        }

        $red_text .= 'MAC位址: '.macaddrcomma($dsta_eth_mac).'<br>';
        if($dusername != '') $red_text .= '使用者名稱: '.$dusername.'<br>';
        if($ddevice_type != '') $red_text .= '連線裝置: '.$ddevice_type.'<br>';
        if($dap_name != '') $red_text .= '連線AP: '.$dap_name.'<br>';
        if($dassociatedN != '') $red_text .= '連線狀況: '.$dassociatedN.'<br>';
        if($drole != '') $red_text .= 'role: '.$drole.'<br>';
        if($dupdateloc != '') $red_text .= 'location更新時間: '.$dupdateloc.'<br>';
        if($dupdateloc != '') $red_text .= 'station更新時間: '.$dupdateloc.'<br>';
        if($dcreatetime != '') $red_text .= '資料更新時間: '.$dcreatetime;

        $redarr[] = array(
            'x' => $red_x,
            'y' => $red_y,
            'text' => $red_text
        );

        $i++;
    }
    $rs = null;

    //回傳JSON
    $result = json_encode($redarr);
    echo $result;
}

//目前樓層裝置數量
if($csearching == 'countdot')
{
    if($cmac != '')
    {
        $macnew = macaddr2sql($cmac);
        if($macnew != '') $sqle = ' and ale_location.sta_eth_mac in('.$macnew.') ';
    }

    $cnt_total = 0;
	$cnt_green = 0;
	$cnt_red = 0;
	
	$sql = "SELECT count(ale_location.sta_eth_mac)
    FROM krystal.ale_location 
    join krystal.ale_station on ale_location.sta_eth_mac = ale_station.sta_eth_mac
    where floor_id = '$dfloor_id' and associated = 1 and ap_name like 'LIB-2%' $sqle";//left join->join
    //echo $sql;
    $rs = $db->query($sql) or die('green dot');
	list($cnt_green) = $rs->fetch(PDO::FETCH_NUM);
	$rs = null;

	$sql = "SELECT sta_location_x,sta_location_y,count(ale_location.sta_eth_mac)
    FROM krystal.ale_location 
    left join krystal.ale_station on ale_location.sta_eth_mac = ale_station.sta_eth_mac
	where floor_id = '$dfloor_id' and associated = 0 $sqle
	group by sta_location_x,sta_location_y";
	$rs = $db->query($sql) or die('red dot');
	$cnt_red = $rs->rowCount();
	$rs = null;

    $cnt_total = $cnt_green + $cnt_red;
    
    $result = "$cnt_total (Associated點位數量: $cnt_green , Unassociated點位數量: $cnt_red)";
    echo $result;
}
?>