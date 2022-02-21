<?php
/**
 * 排程-抓取api資料匯到資料庫
 */
require_once('../system/database.php');//本機用
//require_once('/var/www/html/apum/system/database.php');

//抓取資料 //timeout=5->30
function curl_https($url,$username,$password,$timeout=30,$debug=false)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//跳過驗證檢查
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);//從證書中檢查SSL加密演算法是否存在
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//返回字串，不要直接输出
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    //curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    //curl_setopt($ch, CURLOPT_POST, true);
    //curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));//組合附加在網址後面的參數
    //curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    //curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:38.0) Gecko/20100101 Firefox/38.0');
    //curl_setopt($ch, CURLOPT_COOKIE, 'JSESSIONID=39B2B7837FFEE26E1B83CEC662D5DF08');

    $response = curl_exec($ch);
    if($error = curl_error($ch))
    {
        die($error);
    }

    //輸出錯誤訊息
    if($debug) 
    {
       echo '-info-'."\r\n";
       print_r( curl_getinfo($ch) );

       echo '-error-'."\r\n";
       print_r( curl_error($ch) );

       echo '-$respons-'."\r\n";
       print_r( $response );
    }
    curl_close($ch);

    return $response;
}

//API連線設定
$url = 'https://127.0.0.1/api/v1/'; //API url
//ALE帳密
$username = 'admin'; //API login account
$password = 'welcome123';//API login password

$response = '';
//building
$sendurl = $url.'building';
$response = curl_https($sendurl,$username,$password);
//echo $response;
if($response != '')
{
    //判斷是否有更動building_id
    $sql = "SELECT count(building_id) FROM krystal.ale_building where building_name = 'Library'";
	$rs = $db->query($sql) or die('building-驗證失敗');
	list($cnt_Library) = $rs->fetch(PDO::FETCH_NUM);
    $rs = null;

    if($cnt_Library > 1)
    {
        //清除資料
        $sql = "TRUNCATE TABLE krystal.ale_building";
        $db->query($sql) or die('building-清除失敗');
        echo date("Y-m-d H:i:s").'...building-已清除<br>'.PHP_EOL;
    }

    $i = 0;
    $data = json_decode($response,true);
    foreach($data['Building_result'] as $k => $v)
    {
        $building_id = $v['msg']['building_id'];
        $building_name = $v['msg']['building_name'];
        $campus_id = $v['msg']['campus_id'];
        $ts = $v['ts'];

        if($ts != '')
        {
            $updatetime = date("Y-m-d H:i:s",$ts);
        }
        else
        {
            $updatetime = NULL;
        }

        //新增不重複
        $sql = "INSERT INTO krystal.ale_building(building_id,building_name,campus_id,ts,updatetime,createtime) ";
        $sql .= " select '$building_id','$building_name','$campus_id','$ts','$updatetime',now() from dual ";
        $sql .= " where not exists( select 1 from krystal.ale_building where building_id = '$building_id') ";
        $db->query($sql) or die('building-新增失敗');

        $i++;
    }
    echo date("Y-m-d H:i:s").'...building-新增或更新'.$i.'筆資料<br>'.PHP_EOL;
}

//floor
$sendurl = $url.'floor';
$response = curl_https($sendurl,$username,$password);
//echo $response;
if($response != '')
{
    //判斷是否有更動building_id
    $cnt_floor = 0;
    $sql = "SELECT floor_latitude,floor_longitude,floor_name,count(floor_id) as floor_id_cnt 
    FROM krystal.ale_floor 
    group by floor_latitude,floor_longitude,floor_name
    having floor_id_cnt > 1";
	$rs = $db->query($sql) or die('building-驗證失敗');
    while($row = $rs->fetch(PDO::FETCH_ASSOC))
    {
        $cnt_floor++;
    }
    $rs = null;

    if($cnt_floor > 0)
    {
        //清除資料
        $sql = "TRUNCATE TABLE krystal.ale_floor";
        $db->query($sql) or die('floor-清除失敗');
        echo date("Y-m-d H:i:s").'...floor-已清除<br>'.PHP_EOL;
    }

    $i = 0;
    $data = json_decode($response,true);
    foreach($data['Floor_result'] as $k => $v)
    {
        $floor_id = $v['msg']['floor_id'];
        $floor_name = $v['msg']['floor_name'];
        $floor_latitude = $v['msg']['floor_latitude'];
        $floor_longitude = $v['msg']['floor_longitude'];
        $floor_img_path = $v['msg']['floor_img_path'];
        $floor_img_width = $v['msg']['floor_img_width'];
        $floor_img_length = $v['msg']['floor_img_length'];
        $building_id = $v['msg']['building_id'];
        $floor_level = $v['msg']['floor_level'];
        $units = $v['msg']['units'];
        $ts = $v['ts'];

        if($ts != '')
        {
            $updatetime = date("Y-m-d H:i:s",$ts);
        }
        else
        {
            $updatetime = NULL;
        }

        //新增不重複
        $sql = "INSERT INTO krystal.ale_floor(floor_id,floor_name,floor_latitude,floor_longitude,floor_img_path,
        floor_img_width,floor_img_length,building_id,floor_level,units,ts,updatetime,createtime) ";
        $sql .= " select '$floor_id','$floor_name','$floor_latitude','$floor_longitude','$floor_img_path',
        '$floor_img_width','$floor_img_length','$building_id','$floor_level','$units','$ts','$updatetime',now() from dual ";
        $sql .= " where not exists( select 1 from krystal.ale_floor where building_id = '$building_id' and floor_id = '$floor_id') ";
        $db->query($sql) or die('floor-新增失敗');

        $i++;
    }
    echo date("Y-m-d H:i:s").'...floor-新增或更新'.$i.'筆資料<br>'.PHP_EOL;
}

//access_point
$sendurl = $url.'access_point';
$response = curl_https($sendurl,$username,$password);
//echo $response;
if($response != '')
{
    $i = 0;
    $datas = array();
    $data = json_decode($response,true);
    foreach($data['Access_point_result'] as $k => $v)
    {
        $ap_eth_mac = $v['msg']['ap_eth_mac']['addr'];
        $ap_name = $v['msg']['ap_name'];
        $ap_group = $v['msg']['ap_group'];
        $ap_model = $v['msg']['ap_model'];
        $depl_mode = $v['msg']['depl_mode'];
        $ap_ip_address_af = $v['msg']['ap_ip_address']['af'];
        $ap_ip_address_addr = $v['msg']['ap_ip_address']['addr'];
        $managed_by_af = $v['msg']['managed_by']['af'];
        $managed_by_addr = $v['msg']['managed_by']['addr'];
        $managed_by_key = $v['msg']['managed_by_key'];
        $ts = $v['ts'];
        $createtime = date("Y-m-d H:i:s");

        if($ts != '')
        {
            $updatetime = date("Y-m-d H:i:s",$ts);
        }
        else
        {
            $updatetime = NULL;
        }

        $datas[] = array($ap_eth_mac,$ap_name,$ap_group,$ap_model,$depl_mode,$ap_ip_address_af,
        $ap_ip_address_addr,$managed_by_af,$managed_by_addr,$managed_by_key,
        $ts,$updatetime,$createtime);
 
        $i++;
    }

    if($i > 0 && count($datas) > 0)
    {
        //清除資料
        $sql = "TRUNCATE TABLE krystal.ale_ap";
        $db->query($sql) or die('access_point-清除失敗');

        //新增資料
        $sql = "INSERT INTO krystal.ale_ap(ap_eth_mac,ap_name,ap_group,ap_model,depl_mode,ap_ip_address_af,
        ap_ip_address_addr,managed_by_af,managed_by_addr,managed_by_key,ts,updatetime,createtime) VALUES 
        (?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $rs = $db->prepare($sql);
        try
        {
            $db->beginTransaction();
            foreach($datas as $row)
            {
                $rs->execute($row);
            }
            $db->commit();
        }
        catch(Exception $e)
        {
            $db->rollBack();
            throw $e;
        }
        $rs = null;
        echo date("Y-m-d H:i:s").'...access_point-新增'.$i.'筆資料<br>'.PHP_EOL;
    }
}

//location
$sendurl = $url.'location';
$response = curl_https($sendurl,$username,$password);
//echo $response;
if($response != '')
{
    //取得最新Library floor_id
    $sql = "SELECT building_id FROM krystal.ale_building where building_name = 'Library'";
	$rs = $db->query($sql) or die('location-building-抓取失敗');
	list($latest_building_id) = $rs->fetch(PDO::FETCH_NUM);
    $rs = null;

    if($latest_building_id != '')
    {
        $sql = "SELECT floor_id FROM krystal.ale_floor 
        where building_id = '$latest_building_id' and floor_name = 'floor2'";
        $rs = $db->query($sql) or die('location-floor-抓取失敗');
        list($latest_floor_id) = $rs->fetch(PDO::FETCH_NUM);
        $rs = null;
    }

    $i = 0;
    $j = 0;
    $datas = array();
    $datas2 = array();
    $data = json_decode($response,true);
    foreach($data['Location_result'] as $k => $v)
    {
        $sta_eth_mac = $v['msg']['sta_eth_mac']['addr'];
        $sta_location_x = $v['msg']['sta_location_x'];
        $sta_location_y = $v['msg']['sta_location_y'];
        $error_level = $v['msg']['error_level'];
        $associated = $v['msg']['associated'];
        $campus_id = $v['msg']['campus_id'];
        $building_id = $v['msg']['building_id'];
        $floor_id = $v['msg']['floor_id'];
        $loc_algorithm = $v['msg']['loc_algorithm'];
        $longitude = $v['msg']['longitude'];
        $latitude = $v['msg']['latitude'];
        $altitude = $v['msg']['altitude'];
        $target_type = $v['msg']['target_type'];
        $ts = $v['ts'];
        $createtime = date("Y-m-d H:i:s");
        $hashed_sta_eth_mac = $v['msg']['hashed_sta_eth_mac'];

        if($associated == '') $associated = 0;
        if($ts != '')
        {
            $updatetime = date("Y-m-d H:i:s",$ts);
        }
        else
        {
            $updatetime = NULL;
        }

        $datas[] = array($sta_eth_mac,$sta_location_x,$sta_location_y,$error_level,$associated,
        $campus_id,$building_id,$floor_id,$loc_algorithm,$longitude,$latitude,$altitude,$target_type,
        $ts,$updatetime,$createtime);

        //額外寫入log
        if($latest_floor_id != '' && $floor_id == $latest_floor_id)
        {
            $datas2[] = array($sta_eth_mac,$sta_location_x,$sta_location_y,$associated,$ts,$updatetime,$createtime);
            $j++;
        }
 
        $i++;
    }

    if($i > 0 && count($datas) > 0)
    {
        //清除資料
        $sql = "TRUNCATE TABLE krystal.ale_location";
        $db->query($sql) or die('location-清除失敗');

        //新增資料
        $sql = "INSERT INTO krystal.ale_location(sta_eth_mac,sta_location_x,sta_location_y,error_level,associated,
        campus_id,building_id,floor_id,loc_algorithm,longitude,latitude,altitude,target_type,ts,updatetime,createtime) VALUES 
        (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $rs = $db->prepare($sql);
        try
        {
            $db->beginTransaction();
            foreach($datas as $row)
            {
                $rs->execute($row);
            }
            $db->commit();
        }
        catch(Exception $e)
        {
            $db->rollBack();
            throw $e;
        }
        $rs = null;
        echo date("Y-m-d H:i:s").'...location-新增'.$i.'筆資料<br>'.PHP_EOL;
    }

    //額外寫入log
    if($j > 0 && count($datas2) > 0)
    {
        //刪除資料
        $agotime = date("Y-m-d H:i:s",strtotime("-1 hour"));//1小時前
        $sql = "delete from krystal.ale_location_log where createtime <= ?";
        $rs = $db->prepare($sql);
        $rs->execute(array($agotime));

        //新增資料
        $sql = "INSERT INTO krystal.ale_location_log(sta_eth_mac,sta_location_x,sta_location_y,associated,
        ts,updatetime,createtime) VALUES 
        (?,?,?,?,?,?,?)";
        $rs = $db->prepare($sql);
        try
        {
            $db->beginTransaction();
            foreach($datas2 as $row)
            {
                $rs->execute($row);
            }
            $db->commit();
        }
        catch(Exception $e)
        {
            $db->rollBack();
            throw $e;
        }
        $rs = null;
        echo date("Y-m-d H:i:s").'...location_log-新增'.$j.'筆資料<br>'.PHP_EOL;
    }
}

//station
$sendurl = $url.'station';
$response = curl_https($sendurl,$username,$password);
//echo $response;
if($response != '')
{
    $i = 0;
    $datas = array();
    $data = json_decode($response,true);
    foreach($data['Station_result'] as $k => $v)
    {
        $sta_eth_mac = $v['msg']['sta_eth_mac']['addr'];
        $username = $v['msg']['username'];
        $role = $v['msg']['role'];
        $bssid = $v['msg']['bssid']['addr'];
        $device_type = $v['msg']['device_type'];
        $sta_ip_address_af = $v['msg']['sta_ip_address']['af'];
        $sta_ip_address_addr = $v['msg']['sta_ip_address']['addr'];
        $ap_name = $v['msg']['ap_name'];
        $ts = $v['ts'];
        $createtime = date("Y-m-d H:i:s");
        $hashed_sta_eth_mac = $v['msg']['hashed_sta_eth_mac'];

        if($ts != '')
        {
            $updatetime = date("Y-m-d H:i:s",$ts);
        }
        else
        {
            $updatetime = NULL;
        }

        $datas[] = array($sta_eth_mac,$username,$role,$bssid,$device_type,$sta_ip_address_af,$sta_ip_address_addr,$ap_name,
        $ts,$updatetime,$createtime);

        $i++;
    }

    if($i > 0 && count($datas) > 0)
    {
        //清除資料
        $sql = "TRUNCATE TABLE krystal.ale_station";
        $db->query($sql) or die('station-清除失敗');

        //新增資料
        $sql = "INSERT INTO krystal.ale_station(sta_eth_mac,username,role,bssid,device_type,sta_ip_address_af,
        sta_ip_address_addr,ap_name,ts,updatetime,createtime) VALUES 
        (?,?,?,?,?,?,?,?,?,?,?)";
        $rs = $db->prepare($sql);
        try
        {
            $db->beginTransaction();
            foreach($datas as $row)
            {
                $rs->execute($row);
            }
            $db->commit();
        }
        catch(Exception $e)
        {
            $db->rollBack();
            throw $e;
        }
        $rs = null;
        echo date("Y-m-d H:i:s").'...station-新增'.$i.'筆資料<br>'.PHP_EOL;
    }
}
?>