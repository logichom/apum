<?php
require_once('system/function.php');
require_once('system/database.php');

$cQmacaddr = $_POST['Qmacaddr'];

$htmlarr = readhtml('');
foreach($htmlarr as $text)
{
	if(strpos($text,'<!--headercss-->')!== FALSE)
	{
		$text = str_replace('<!--headercss-->','',$text);
		include("./global/headercss.php");	
	}
	elseif(strpos($text,"<!--footerjs-->") !== FALSE)
	{
		$text = str_replace('<!--footerjs-->','',$text);
		include("./global/footerjs.php");	
	}
	elseif(strpos($text,"<!--copyright-->") !== FALSE)
	{
		$text = str_replace('<!--copyright-->','',$text);
		include("./global/copyright.php");	
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
	else
	{
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
