<?php
define('LOG_FILE_204','../../../../logs/204.8.'.date('Y-m-d').'.log');

require_once '../../../../init.php';

	$table_prefix = $GLOBALS['_MAX']['CONF']['table']['prefix'];
	$request_array = '';

function OX_Delivery_logMessage($message, $priority = 6)
{
$conf = $GLOBALS['_MAX']['CONF'];
if (empty($conf['deliveryLog']['enabled'])) return true;
$priorityLevel = is_numeric($conf['deliveryLog']['priority']) ? $conf['deliveryLog']['priority'] : 6;
if ($priority > $priorityLevel && empty($_REQUEST[$conf['var']['trace']])) { return true; }
//1error_log('[' . date('r') . "] {$conf['log']['ident']}-delivery-{$GLOBALS['_MAX']['thread_id']}: {$message}\n", 3, MAX_PATH . '/var/' . $conf['deliveryLog']['name']);
//OX_Delivery_Common_hook('logMessage', array($message, $priority));
return true;
}
	

function djax_getAd($adid,$id)
{
	//print_r($adid); exit;
		$cc="FIND_IN_SET(".$id.",c.dsp_portals)";
		
		$bid_floor=$GLOBALS['_MAX']['CONF']['request_info']['imp'][0]['bidfloor'];
		$width=	$GLOBALS['_MAX']['CONF']['request_info']['imp'][0]['banner']['w'];
		$height=$GLOBALS['_MAX']['CONF']['request_info']['imp'][0]['banner']['h'];
		$txt_bannertype=$GLOBALS['_MAX']['CONF']['request_info']['imp'][0]['banner']['mimes'];
		
		$reqaddomain=$GLOBALS['_MAX']['CONF']['request_info']['badv'];
		$reqcat=$GLOBALS['_MAX']['CONF']['request_info']['bcat'];	
		$btype=$GLOBALS['_MAX']['CONF']['request_info']['imp'][0]['banner']['btype'];
		$bid_id = $GLOBALS['_MAX']['CONF']['request_info']['id'];
		
		if((in_array('image/gif',$txt_bannertype) || in_array('image/jpeg',$txt_bannertype) || in_array('image/png',$txt_bannertype)) && !in_array('2',$btype))
		{
			$cond="web";
				
		}
		elseif((in_array('text/plain',$txt_bannertype) || in_array('text/html',$txt_bannertype) && empty($width) && empty($height)) && !in_array('1',$btype))
		{ 

			$width=0;
			$height=0;
			$cond="txt";
		}
		else
		{

			$cond="javascript";

		}
		
		$tableprefix = $GLOBALS['_MAX']['CONF']['table']['prefix'];

		
		$conf = $GLOBALS['_MAX']['CONF']['database'];
		$host=$conf['host'];
		$database=$conf['name'];	
		
		$dbh = new PDO("mysql:host=$host;port=3306;dbname=$database", $conf['username'],  $conf['password']) or die(mysql_error());
		if($dbh)
		{
			//error_log(date('Y-m-d H:i:s').' Woking ',3,"../../../../logs/pdo.log");
			//error_log(PHP_EOL,3,"../../../../logs/pdo.log");
		}
		else
		{
			//1error_log(date('Y-m-d H:i:s').' smatto faild ',3,"../../../../logs/pdo.log");
			//1error_log(PHP_EOL,3,"../../../../logs/pdo.log");
}	
		$sql = "call getAd('".$width."','".$height."','".$id."','".$cond."','".$reqaddomain."','".$reqcat."')";
		
		foreach ($dbh->query($sql) as $aAd) {
		
			if($aAd['revenue']>=0.01 && $aAd['revenue']<1)
			{
				
				$aRows[$aAd['ad_id']] = $aAd;
				
				
			}
			else
			{
			        error_log(date('Y-m-d H:i:s').'|'.$bid_id.'|'.$aAd['ad_id'].'|1||price is below requested price'.PHP_EOL,3,LOG_FILE_204);
					
			}
		} 
		
		$dbh = null;	
		return $aRows;
}


function dsp_adprocessing($requestparams,$id)
{
	
	$table_prefix = $GLOBALS['_MAX']['CONF']['table']['prefix'];
        $limitedcampaigns=array();
	$banner=array();
	$request=array();
	$request_array=unserialize($requestparams);
	$GLOBALS['_MAX']['CONF']['request_info']=$request_array;
	$djax_allads=djax_getAd($request_array,$id);

			
	$limitedcampaigns=_adSelectCheckCriteria($djax_allads);
	
	$finallimit=@array_intersect_key($djax_allads,$limitedcampaigns);
	$results =	$finallimit[array_rand($finallimit,1)];
	


if($results['ad_id']!=0)
{
		$table_prefix = $GLOBALS['_MAX']['CONF']['table']['prefix'];
		$deliverypath=$GLOBALS['_MAX']['CONF']['webpath']['delivery'];
		$imagepath=$GLOBALS['_MAX']['CONF']['webpath']['images'];
		$adminpath=$GLOBALS['_MAX']['CONF']['webpath']['admin'];

		$clickurl='http://'.$deliverypath."/ck.php?oaparams=2__bannerid=".$results['ad_id']."__zoneid=0__cb={random}__oadest=";

		$beaconurl='http://'.$deliverypath."/lg.php?bannerid=".$results['ad_id']."&amp;campaignid=".$results['placement_id']."&amp;zoneid=0&amp;cb={random}";

		$imageurl='http://'.$imagepath."/".$results['filename'];			

		$cur_date  = 	date('Y-m-d H:i:s');
		$req	=$request_array['id'];		
		//$request_query 	= 	OA_Dal_Delivery_query("SELECT id FROM {$table_prefix}dj_adacts_bid_request WHERE bid_request_id='".$req."'") ;
		//$request_row 	= 	OA_Dal_Delivery_fetchAssoc($request_query);
		//$requset_id 	= 	$request_row['id'];					
				OA_Dal_Delivery_query("INSERT INTO  
								`{$table_prefix}dj_adacts_response` (
									`datetime`,
									`requset_id`,
									`id` ,
									`imp_id` ,
									`imp_width` ,
									`imp_height` ,
									`seat` ,
									`floor_price` ,
									`advertiser_bid_price` ,
									`smaato_bid_price` ,
									`admin_rev` ,
									`adid` ,
									`bannerid` ,
									`campaign_id` ,
									`type`
								)
								VALUES (
									 '".$cur_date."',
									 '".$requset_id."',
									 '".$request_array['id']."',
									 '".$request_array['imp'][0]['id']."', 
									 '".$results['width']."', 
									 '".$results['height']."', 
									 '8a809449012f2f0744180791edfc0003', 
									 '".$GLOBALS['_MAX']['CONF']['request_info']['imp'][0]['bidfloor']."', 
									 '".$results['revenue']."',
									 '0',
									 '0', 
									 '".$results['ad_id']."', 
									 '".$results['ad_id']."', 
									 '".$results['placement_id']."', 
									 '".$request_array['device']['devicetype']."'
								)"); 
			

		$response_array= array(
			"id"=>$request_array['id'],
			"bid"=>"368986290101875502942021904441292",
			"impid"=>$request_array['imp'][0]['id'],
			"price"=>$results['revenue'],
			"adid"=>$results['ad_id'],
			"nurl"=> 'http://'.$adminpath.'/plugins/DSP/winnotice.adacts.php?auctionId=${AUCTION_ID}&bidid=${AUCTION_BID_ID}&price=${AUCTION_PRICE}&impid=${AUCTION_IMP_ID}&seatid=${AUCTION_SEAT_ID}&adid=${AUCTION_AD_ID}&cur=${AUCTION_CURRENCY}',
			"click_url"=>$clickurl,
			"image_url"=>$imageurl,
			"additional_text"=>$results['bannertext'],
			"beacon_url"=>$beaconurl,
			"adomain"=>$_SERVER['HTTP_HOST'],
			"iurl"=>"",
			"cid"=>$results['placement_id'],
			"crid"=>$results['ad_id'],
			"attr"=>"",
			"ext"=>"",
			"tooltip"=>"",
			"seat"=>"8a809449012f2f0744180791edfc0003",
			"group"=>0,
			"bidid"=>$request_array['id'],
			"cur"=>"USD",
			"customdata"=>"",
			"ext"=>"",
			"width"=>$results['width'],
			"height"=>$results['height'],
			"adtype"=>$results['type']
		);
	

return $response_array;

}

}



function _adSelectCheckCriteria($ads)
{
	
	$djax_response=array();
	if(count($ads)>0)
	{
		$ip_limit			=	0;
		$compiledlimitation	=	0;
			foreach($ads as $key => $value)
			{
				$ip_limit	=  $value['placement_id'];
				$iprange=iprange($value['placement_id']);
				
				if(empty($iprange))
				{
					unset($ads[$key]);
				}		
						
				if($ads[$key] && !empty($value['compiledlimitation']))
				{
					
					@eval('$result = (' . $value['compiledlimitation'] . ');');
					
					
					if(empty($result))
					{
						$result=0;
						unset($ads[$key]);
					}

				}
			
			/*	else // sep29
				{
					$djax_response[$key]=1;
				} */
				
			if($ads[$key])
				{
					$djax_response[$key]=1;
				}
			}
			if(count($djax_response)==0)
			{
				$re_id	=	$GLOBALS['_MAX']['CONF']['request_info']['id'];	
				$request_ip=	$GLOBALS['_MAX']['CONF']['request_info']['device']['ip'];
				error_log(date('Y-m-d H:i:s').'|'.$GLOBALS['_MAX']['CONF']['request_info']['id'].'|'.$request_ip.'|2|'.$ip_limit.'|IP Pool'.PHP_EOL,3,LOG_FILE_204);
			}
	
	}
return $djax_response;

}

function iprange($placement_id)
{
	$ip		=	$GLOBALS['_MAX']['CONF']['request_info']['device']['ip'];

	$qryCount	=	OA_Dal_Delivery_query("SELECT ipranges as cc FROM rv_campaign_iprange WHERE campaignid = '".$placement_id."' ");
	$rowCount	=	mysql_fetch_row($qryCount);
	if(preg_replace('/\s+/', '', $rowCount[0])!="")
	{
		
		$temp	=	$rowCount[0];
		$flag	=	"0";
		$qry1	=	OA_Dal_Delivery_query("select ipaddress from djax_iprange  WHERE (INET_ATON('$ip') BETWEEN INET_ATON(`hostmin`) AND INET_ATON(`hostmax`)) and locid in($temp)");
		$rowCounting	=	mysql_fetch_row($qry1);
		
		return ($rowCounting)?true:false;
		
	}
		else 
		{
			return true;
		}
		
}


function country($limitation, $op)
{
$paramName=$GLOBALS['_MAX']['CONF']['request_info']['device']['geo']['country'];
$fetchrows=OA_Dal_Delivery_fetchAssoc(OA_Dal_Delivery_query("SELECT value FROM `djax_targ_country` where iso_countycode_alpha3='".$paramName."'"));

$data = explode(',',$limitation);
$finaldetails=array_map('strtolower',$data);

if( $op=='=~' && in_array(strtolower($fetchrows['value']),$finaldetails))
{
	return true;
}
elseif( $op=='!~' && !(in_array(strtolower($fetchrows['value']),$finaldetails)))
{
	return true;
}
else
{	
	error_log(date('Y-m-d H:i:s').'|'.$GLOBALS['_MAX']['CONF']['request_info']['id'].'|'.$paramName.'|3||Country'.PHP_EOL,3,LOG_FILE_204);
	return false;
}
}


function os($limitation, $op)
{
$data = explode(',',$limitation);
$finaldetails=array_map('strtolower',$data);
$req_os	=	$GLOBALS['_MAX']['CONF']['request_info']['device']['os'];

if( $op=='=~' && in_array(strtolower($GLOBALS['_MAX']['CONF']['request_info']['device']['os']),$finaldetails))
{
	return true;
}
elseif( $op=='!~' && !(in_array(strtolower($GLOBALS['_MAX']['CONF']['request_info']['device']['os']),$finaldetails)))
{
	return true;
}
else
{
	error_log(date('Y-m-d H:i:s').'|'.$GLOBALS['_MAX']['CONF']['request_info']['id'].'|'.$req_os.'|4||OS'.PHP_EOL,3,LOG_FILE_204);
	return false;
}

}

function model($limitation, $op)
{
$data = explode(',',$limitation);
$finaldetails=array_map('strtolower',$data);
$req_model	=	$GLOBALS['_MAX']['CONF']['request_info']['device']['model'];
if( $op=='=~' && in_array(strtolower($GLOBALS['_MAX']['CONF']['request_info']['device']['model']),$finaldetails))
{
	return true;
}
elseif( $op=='!~' && !(in_array(strtolower($GLOBALS['_MAX']['CONF']['request_info']['device']['model']),$finaldetails)))
{
	return true;
}
else
{
	
	error_log(date('Y-m-d H:i:s').'|'.$GLOBALS['_MAX']['CONF']['request_info']['id'].'|'.$req_model.'|5||DeviceModel'.PHP_EOL,3,LOG_FILE_204);
	return false;
}
}

function handset($limitation, $op, $aParams = array())
{

$data = explode(',',$limitation);
$finaldetails=array_map('strtolower',$data);
$make	= $GLOBALS['_MAX']['CONF']['request_info']['device']['make'];

if( $op=='=~' && in_array(strtolower($GLOBALS['_MAX']['CONF']['request_info']['device']['make']),$finaldetails))
{
	return true;
}
	elseif( $op=='!~' && !(in_array(strtolower($GLOBALS['_MAX']['CONF']['request_info']['device']['make']),$finaldetails)))
{
	return true;
}
else
{	error_log(date('Y-m-d H:i:s').'|'.$GLOBALS['_MAX']['CONF']['request_info']['id'].'|'.$make.'|6||DeviceMake'.PHP_EOL,3,LOG_FILE_204);
	return false;
}
	
}


