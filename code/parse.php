<?php

require_once(dirname(dirname(dirname(__FILE__))) . '/utils.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/simplehtmldom_1_5/simple_html_dom.php');

require_once('crossref.php');
require_once(dirname(__FILE__) . '/lcs.php');

//----------------------------------------------------------------------------------------
function search($citation)
{
	$result = crossref_search($citation);
	
	//print_r($result);
	
	$double_check = true;
	$theshhold = 0.8;
	
	if ($double_check)
	{
		// get metadata 
		$query = explode('&', html_entity_decode($result->coins));
		$params = array();
		foreach( $query as $param )
		{
		  list($key, $value) = explode('=', $param);
		  
		  $key = preg_replace('/^\?/', '', urldecode($key));
		  $params[$key][] = trim(urldecode($value));
		}
		
		//print_r($params);
		
		$hit = '';
		if (isset($params['rft.au']))
		{
			$hit = join(",", $params['rft.au']);
		}
		  
		$hit .= ' ' . $params['rft.atitle'][0] 
			. '. ' . $params['rft.jtitle'][0]
			. ' ' . $params['rft.volume'][0]
			. ': ' .  $params['rft.spage'][0];

		$v1 = $citation;
		$v2 = $hit;
		
		//echo "-- $hit\n";
		
		//echo "v1: $v1\n";
		//echo "v2: $v2\n";
		

		$v1 = finger_print($v1);
		$v2 = finger_print($v2);					

		if (($v1 != '') && ($v2 != ''))
		{
			//echo "v1: $v1\n";
			//echo "v2: $v2\n";

			$lcs = new LongestCommonSequence($v1, $v2);
			$d = $lcs->score();

			// echo $d;

			$score = min($d / strlen($v1), $d / strlen($v2));

			//echo "score=$score\n";
			
			if ($score > $theshhold)
			{
			
			}
			else
			{
				unset ($result);
			}
		}
	}
	
	return $result;
}


//----------------------------------------------------------------------------------------


$failed = array();

$dois = array();
$add_doi = true;
//$add_doi = false;

$journals_to_skip = array();

$count = 0;

$basedir = dirname(__FILE__) . '/html';

$files = scandir($basedir);

$files=array('2015-2016.html');
//$files=array('2010-2014.html');
//$files=array('2005-2009.html');
//$files=array('2000-2004.html');
//$files=array('1996-1999.html');
//$files=array('1992-1995.html');
//$files=array('1988-1991.html');
//$files=array('1981-1987.html');
//$files=array('1970-1980.html');
//$files=array('1960-1969.html');
//$files=array('1935-1959.html');
//$files=array('1898-1934.html');
//$files=array('1757-1897.html');

//$files=array('failed.html');

//$files=array('1992-1995.html');

//print_r($files);



foreach ($files as $filename)
{
	if (preg_match('/\.html$/', $filename))
	{	
		//echo $filename . "\n";
		
		//$output_filename = str_replace('.html', '.tsv', $filename);

		$html = file_get_contents($basedir . '/' . $filename);
		
		//echo $html;

		$dom = str_get_html($html);

		$ps = $dom->find('p');
		foreach ($ps as $p)
		{
			$reference = new stdclass;
			$reference->notes = $p->plaintext;
			
			$reference->notes = preg_replace('/\s+[-]+\s+Show included taxa/u', '', $reference->notes);
	
	
			//echo $p->plaintext . "\n";
			
			// Need a library of patterns to match references
			
			$patterns = array(
// Hu, Y. J. & Wang, J. F. (1981). [Notes on the spider family Prodidomidae (Araneae) new to China]. Journal of Hunan Normal University (nat. Sci.) 1981(2): 51-52.			'/(?<authorstring>.*)\s+\((?<year>[0-9]{4})[a-z]?\)\.\s+(?<title>\[.*\])\.\s+(?<journal>[A-Z]\w+.*)\s+(?<volume>\d+([\.|-|\/]\d+)?)(\((?<issue>.*)\))?:\s+(?<spage>\d+)(-(?<epage>\d+))?\b/u',

// Zonstein, S. L., Marusik, Y. M. & Omelko, M. M. (2016b). Redescription of the type species of Diaphorocellus Simon, 1893 (Araneae, Palpimanidae, Chediminae). African Invertebrates 57(2): 93-103	
		'/(?<authorstring>.*)\s+\((?<year>[0-9]{4})[a-z]?\)\.\s+(?<title>.*)\.\s+(?<journal>\p{Lu}\p{Ll}+.*)\s+(?<volume>\d+([\.|-|-|\/]\d+)?)(\((?<issue>.*)\))?[:|,]\s+(?<spage>\d+)(-(?<epage>\d+))?\b/u',

// Fedoriak, M. M. (2015). Scientific heritage of Aleksandru Roshka as а basis for retrospective analysis of araneofauna of Bukovyna. Edited by Prof., Dr. S. S. Rudenko. Druk Art, Chernivtsi, 1-175.
'/(?<authorstring>.*)\s+\((?<year>[0-9]{4})[a-z]?\)\.\s+(?<title>.*)\.\s+(?<journal>\p{Lu}\p{Ll}+.*)\s+(?<volume>\d+([\.|-|-|\/]\d+)?)(\((?<issue>.*)\))?[:|,]\s+(?<spage>\d+)(-(?<epage>\d+))?\b/u',

// Book
'/(?<authorstring>.*)\s+\((?<year>[0-9]{4})[a-z]?\)\.\s+(?<title>.*)\.\s+(?<publisher>\w+(\s+[\w|-|&|\.|\']+)*),(\s+(?<publoc>\w+(,\s+\w+(\s+\w+)?)?))?(,\s+(?<pages>\d+)\s+pp)?.?/u',

// Marusik, Y. M. (2015). Araneae (Spiders). In: Böcher, J., Kristensen, N. P., Pape, T. & Vilhelmsen, L. (eds.) The Greenland Entomofauna. An identification manual of insects, spiders and their allies. Fauna Entomologica Scandinavica. Brill, Leiden, vol. 44, pp. 666-703.
'/(?<authorstring>.*)\s+\((?<year>[0-9]{4})[a-z]?\)\.\s+(?<title>.*)\.\s+In:(\s*(?<editorstring>.*)\s+\(ed[s]?\.\))?\s+(?<book>.*)\s+pp.\s+(?<spage>\d+)(-(?<epage>\d+))?\b/u',

// Kuntner, M. & Coddington, J. A. (2009). Discovery of the largest orbweaving spider species: the evolution of gigantism in Nephila. PLoS One 4(10): e7516. doi:10.1371/journal.pone.0007516
'/(?<authorstring>.*)\s+\((?<year>[0-9]{4})[a-z]?\)\.\s+(?<title>.*)\.\s+(?<journal>.*)\s+(?<volume>\d+)(\((?<issue>.*)\))?:\s+(?<spage>e\d+)[.|:|,](.*)\s+doi:(?<doi>.*)/u',


'/(?<authorstring>.*)\s+\((?<year>[0-9]{4})[a-zA-Z]?\)\.\s+(?<title>.*)\.\s+(?<journal>.*)\s+(?<volume>\d+([\.|-|-|\/]\d+)?)(\((?<issue>.*)\))?:\s+(?<spage>[0-9ePD]+)-[-]?(?<epage>[0-9ePD]+)/u',

'/(?<authorstring>.*)\s+\((?<year>[0-9]{4})[a-zA-Z]?\)\.\s+(?<title>.*)\.\s+(?<journal>.*)\s+\((?<series>\d+)\)\s+(?<volume>\d+(\[=\d+\])?):\s+(?<spage>[0-9]+)-[-]?(?<epage>[0-9]+)/u',


			);
			
			$matched = false;
			
			foreach ($patterns as $pattern)
			{
				if (preg_match($pattern, $p->plaintext, $m))
				{
					reference_from_matches($m, $reference);
					$matched = true;
					break;
				}
			}
			
			$as = $p->find('a');
			foreach ($as as $a)
			{
				if (preg_match('/http:\/\/dx.doi.org\/(?<doi>.*)/', $a->href, $m))
				{
					$reference->doi = $m['doi'];
				}
				if (preg_match('/\/user\/login\?refId=(?<id>\d+)/', $a->href, $m))
				{
					$reference->id = $m['id'];
				}
			}
			
			
			if (isset($reference->id))
			{
				//print_r($reference);
				
				$count++;
				
				$reference->url = 'http://www.wsc.nmbe.ch/reference/' . $reference->id;
				
				if ($add_doi)
				{
					if (!isset($reference->doi))
					{
						//attempt to have some simpe rules for what journals to skip
						if (!in_array($reference->journal, $journals_to_skip))
						{
					
							$result = search($reference->notes);
	
							if ($result)
							{
								$reference->doi = $result->doi;
							
								$dois[] = $reference->doi;
							}
							else
							{
								$journals_to_skip[] = $reference->journal;
							}
						}
						else
						{
							//echo "Skip " . $reference->journal . "\n";
						}
					}	
				}
				
				// fixes
				if (isset($reference->publoc))
				{
					if (preg_match('/(?<publoc>.*),\s+(?<pages>\d+)\s+pp/', $reference->publoc, $m))
					{
						$reference->publoc = $m['publoc'];
						$reference->spage = $m['pages'];
					}
				}
				
				//echo reference_to_ris($reference);
				
				echo join("\t", reference_to_tsv($reference)) . "\n";
				
				
				// if ($count == 50){ break; }
			}
			
			if (!isset($reference->title))
			{
				//echo " *** not parsed *** \n";
				
				echo join("\t", reference_to_tsv($reference)) . "\n";
				
				$failed[$reference->id] = $reference->notes;
				
				//exit();
			}
		}
	}
}

echo "Failed\n";
print_r($failed);

print_r($dois);
echo "DOIs added " . count($dois) . "\n";

echo "Skipped journals\n";
print_r($journals_to_skip);


?>
		
