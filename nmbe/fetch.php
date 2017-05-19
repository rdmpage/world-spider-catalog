<?php

// Spiders

//--------------------------------------------------------------------------------------------------
/**
 * @brief Test whether HTTP code is valid
 *
 * HTTP codes 200 and 302 are OK.
 *
 * For JSTOR we also accept 403
 *
 * @param HTTP code
 *
 * @result True if HTTP code is valid
 */
function HttpCodeValid($http_code)
{
	if ( ($http_code == '200') || ($http_code == '302') || ($http_code == '403'))
	{
		return true;
	}
	else{
		return false;
	}
}


//--------------------------------------------------------------------------------------------------
/**
 * @brief GET a resource
 *
 * Make the HTTP GET call to retrieve the record pointed to by the URL. 
 *
 * @param url URL of resource
 *
 * @result Contents of resource
 */
function get($url, $userAgent = '', $timeout = 0)
{
	$data = '';
	
	$ch = curl_init(); 
	curl_setopt ($ch, CURLOPT_URL, $url); 
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt ($ch, CURLOPT_FOLLOWLOCATION,	1); 
	//curl_setopt ($ch, CURLOPT_HEADER,		  1);  

	//curl_setopt ($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
	
	if ($userAgent != '')
	{
		curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
	}	
	
	if ($timeout != 0)
	{
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	}
			
	$curl_result = curl_exec ($ch); 
	
	if (curl_errno ($ch) != 0 )
	{
		echo "CURL error: ", curl_errno ($ch), " ", curl_error($ch);
	}
	else
	{
		$info = curl_getinfo($ch);
		
		//$header = substr($curl_result, 0, $info['header_size']);
		//echo $header;
		
		
		$http_code = $info['http_code'];
		
		//echo "<p><b>HTTP code=$http_code</b></p>";
		
		if (HttpCodeValid ($http_code))
		{
			$data = $curl_result;
		}
	}
	return $data;
}

$base_dir = 'rdf';

$prefix = 'spidersp';
$prefix = 'spidergen'; // genera
$prefix = 'spiderfam'; // families

$start = 1;
//$start = 40918;
$stop  = 999999;
$padding = 6;

switch ($prefix)
{
	case 'spidersp':
		$stop = 999999;
		$padding = 6;
		break;
		
	case 'spidergen':
		$stop = 99999;
		$padding = 5;
		break;

	case 'spiderfam':
		$stop = 9999;
		$padding = 4;
		break;

	default:
		break;		
}

$count = 0;

for ($id = $start; $id <= $stop; $id++)
{
	$padded_id = str_pad($id, $padding, '0', STR_PAD_LEFT);
	$lsid = 'urn:lsid:nmbe.ch:' . $prefix . ':' . $padded_id;
	echo $lsid . "\n";
	
	$url = 'http://lsid.nmbe.ch:80/authority/metadata/?lsid=' . $lsid;
	
	$xml = get($url, 'Mozilla/5.0 (iPad; U; CPU OS 3_2_1 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Mobile/7B405');
	
	//echo $xml;
	
	if (substr($xml, 0, 5) != '<?xml')
	{
		echo "Not XML\n";
		//exit();
	}	
	else
	{			
		$dir = floor($id / 1000);
		
		$dir = dirname(__FILE__) . "/$base_dir/" . $dir;
		if (!file_exists($dir))
		{
			$oldumask = umask(0); 
			mkdir($dir, 0777);
			umask($oldumask);
		}
		
		$filename = $dir . '/' . $prefix . $id . '.xml';
		
		$file = fopen($filename, "w");
		fwrite($file, $xml);
		fclose($file);
	}
	
	if (($count++ % 10) == 0)
	{
		$rand = rand(2000000, 6000000);
    	echo '...sleeping for ' . round(($rand / 1000000),2) . ' seconds' . "\n";
    	usleep($rand);
    }
}



?>