<?php
//取得執行程式的頁面名稱,自動抓取該程式檔名
function takeFileName()
{
	$filearr = explode('/',$_SERVER['PHP_SELF']);
	$filelong = count($filearr);
	$filearr = explode('.',$filearr[$filelong-1]);
	$filename = $filearr[0];
	$returnval = $filename.'.shtm';
	return $returnval;
}

//讀取頁面資料+追綜碼
function readhtml($temps)
{
    if(!$temps)
    {
        $filename = takeFileName();
    }
    else
    {
        $filename = $temps;
	}
	$cTxt = file_get_contents($filename) or die("開啟檔案錯誤");
	$cTxt = str_replace('../','http://'.$_SERVER['HTTP_HOST'].'/',$cTxt);
	$cTxt = str_replace(array('<!--Tag.Start-->','<!--Tag.End-->'),'<split>',$cTxt);
	$htmlText = explode('<split>',$cTxt);
	$cTxt = '';
	return $htmlText;
}

//MAC位址處理-轉成sql可查詢的資料
function macaddr2sql($mac)
{
	$macaddrnew = '';
	$macaddrN = '';
	$mac = trim($mac);
	if(strlen($mac) >= 12)
	{
		$macaddr = strtoupper($mac);
		$semicolon = strpos($macaddr,':');
		$dash = strpos($macaddr,'-');
		if($semicolon > 0)
		{
			$macaddrN = str_replace(':','',$macaddr);
		}
		elseif($dash > 0)
		{
			$macaddrN = str_replace('-','',$macaddr);
		}
		else
		{
			$macaddrN = $macaddr;
		}

		if($macaddrN != '')
		{
			$comma = strpos($macaddrN,',');
			if($comma > 0)
			{
				//多組
				$macaddrarr = explode(',',$macaddrN);
				$macaddr2arr = array();
				foreach($macaddrarr as $mactemp)
				{
					$macaddr2arr[] = "'".$mactemp."'";
				}
				$macaddrnew = implode(',',$macaddr2arr);
			}
			else
			{
				//一組
				$macaddrnew = "'".$macaddrN."'";
			}
		}
	}
	return $macaddrnew;
}

//MAC位址處理-加上分號
function macaddrcomma($mac)
{
	$macnew = '';
	if(strlen($mac) == 12)
	{
		$macarr = str_split($mac,2);
		$macnew = implode(':',$macarr);
	}
	return $macnew;
}

//分頁
function pagination($url,$page,$pages)
{
	$i = 0;
	$html = '<ul class="pagination pagination-sm no-margin ">';//<!-- pull-right -->
	//自定義分頁搜尋字串，要以GET方式
	if($page > 1)
	{
		$html .= '<li><a href="'.$url.'&page='.($page-1).'">&laquo;</a></li>'; 
	}
	//echo "第 ";
	for($i=1;$i<=$pages;$i++) 
	{
		if($page - 5 < $i && $i < $page + 5) 
		{
			if($page == $i)
			{
				$html .= '<li><a href="#">'.$i.'</a></li>';
			}
			else
			{
				$html .= '<li><a href="'.$url.'&page='.$i.'">'.$i.'</a></li>';
			}
		}
	} 
	//分頁頁碼
	//if($page > $pages){echo "1";}
	//echo " 頁";
	if($pages > $page)
	{
		$html .= '<li><a href="'.$url.'&page='.($page+1).'">&raquo;</a></li>';
	}
	$html .= '</ul>';

	return $html;
}
?>