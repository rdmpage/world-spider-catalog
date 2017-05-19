<?php

// Extract stuff

$basedir = 'rdf';

$files1 = scandir(dirname(__FILE__) . '/' . $basedir);

foreach ($files1 as $directory)
{
	//echo $directory . "\n";
	if (preg_match('/^\d+$/', $directory))
	{	
		//echo $directory . "\n";
		
		$files2 = scandir(dirname(__FILE__) . '/' . $basedir . '/' . $directory);

		foreach ($files2 as $filename)
		{
			//echo $filename . "\n";
			if (preg_match('/spidergen\d+\.xml$/', $filename))
			{	

				$xml = file_get_contents(dirname(__FILE__) . '/' . $basedir . '/' . $directory . '/' . $filename);
	

				$dom= new DOMDocument;
				$dom->loadXML($xml);
				$xpath = new DOMXPath($dom);
	
				$xpath->registerNamespace('tn',      'http://rs.tdwg.org/ontology/voc/TaxonName#');
				$xpath->registerNamespace('nmbe',    'urn:lsid:nmbe.ch:predicates:');
				$xpath->registerNamespace('tc',      'http://rs.tdwg.org/ontology/voc/TaxonConcept#');
				$xpath->registerNamespace('dwc',     'http://purl.org/dc/elements/1.1/');
				$xpath->registerNamespace('dc',      'http://rs.tdwg.org/dwc/terms/');
				$xpath->registerNamespace('rdfs',    'http://www.w3.org/2000/01/rdf-schema#');
				$xpath->registerNamespace('rdf',     'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
	
			/*
			<?xml version="1.0" encoding="utf-8"?>
			<rdf:RDF xmlns:tn="http://rs.tdwg.org/ontology/voc/TaxonName#" xmlns:nmbe="urn:lsid:nmbe.ch:predicates:" xmlns:tc="http://rs.tdwg.org/ontology/voc/TaxonConcept#" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:dwc="http://rs.tdwg.org/dwc/terms/" xmlns:dc="http://purl.org/dc/elements/1.1/">
			  <rdf:Description rdf:about="urn:lsid:nmbe.ch:spidersp:039798">
				<dc:date>2017-05-04</dc:date>
				<dc:type>Scientific Name</dc:type>
				<dc:creator rdf:resource="http://www.wsc.nmbe.ch"/>
				<dc:identifier>urn:lsid:nmbe.ch:spidersp:039798</dc:identifier>
				<dc:title>Ariamnes melekalikimaka</dc:title>
				<dc:subject>Ariamnes melekalikimaka Gillespie &amp; Rivera, 2007</dc:subject>
				<tn:nameComplete>Ariamnes melekalikimaka</tn:nameComplete>
				<tn:genusPart>Ariamnes</tn:genusPart>
				<tn:specificEpithet>melekalikimaka</tn:specificEpithet>
				<tn:infraspecificEpithet/>
				<tn:authorship>Gillespie &amp; Rivera</tn:authorship>
				<tn:year>2007</tn:year>
				<dwc:nomenclaturalCode>ICZN</dwc:nomenclaturalCode>
				<nmbe:statusString>VALID</nmbe:statusString>
				<dwc:taxonRank>species</dwc:taxonRank>
				<tc:hasInformation rdf:resource="http://wsc.nmbe.ch/species/38233"/>
				<dwc:namePublishedIn>Gillespie, R. G. &amp; Rivera, M. A. J. (2007). Free-living spiders of the genus Ariamnes (Araneae, Theridiidae) in Hawaii. Journal of Arachnology 35: 11-37.</dwc:namePublishedIn>
				<nmbe:publishedOnPage>23</nmbe:publishedOnPage>
			  </rdf:Description>
			</rdf:RDF>
			*/	
		
				$obj = new stdclass;
	
				// Identifier
				$nodeCollection = $xpath->query ('//dc:identifier');
				foreach ($nodeCollection as $node)
				{
					$obj->id = $node->firstChild->nodeValue;
				}

				// Name
				$nodeCollection = $xpath->query ('//tn:nameComplete');
				foreach ($nodeCollection as $node)
				{
					$obj->nameComplete = $node->firstChild->nodeValue;
				}


				$nodeCollection = $xpath->query ('//dc:subject');
				foreach ($nodeCollection as $node)
				{
					$originalString = $node->firstChild->nodeValue;
					
					$obj->taxonAuthor = trim(str_replace($obj->nameComplete, '', $originalString));
				}
				
	
				$nodeCollection = $xpath->query ('//tn:genusPart');
				foreach ($nodeCollection as $node)
				{
					$obj->genusPart = $node->firstChild->nodeValue;
				}
	
				$nodeCollection = $xpath->query ('//tn:specificEpithet');
				foreach ($nodeCollection as $node)
				{
					$obj->specificEpithet = $node->firstChild->nodeValue;
				}

				$nodeCollection = $xpath->query ('//tn:infraspecificEpithet');
				foreach ($nodeCollection as $node)
				{
					$obj->infraspecificEpithet = $node->firstChild->nodeValue;
				}
	
	
				$nodeCollection = $xpath->query ('//tn:authorship');
				foreach ($nodeCollection as $node)
				{
					$obj->authorship = $node->firstChild->nodeValue;
				}

				$nodeCollection = $xpath->query ('//tn:year');
				foreach ($nodeCollection as $node)
				{
					$obj->year = $node->firstChild->nodeValue;
				}
	
				$nodeCollection = $xpath->query ('//dwc:taxonRank');
				foreach ($nodeCollection as $node)
				{
					$obj->taxonRank = $node->firstChild->nodeValue;
				}
	
				$nodeCollection = $xpath->query ('//tc:hasInformation/@rdf:resource');
				foreach ($nodeCollection as $node)
				{
					$obj->url = $node->firstChild->nodeValue;
				}

				$nodeCollection = $xpath->query ('//dwc:namePublishedIn');
				foreach ($nodeCollection as $node)
				{
					$obj->namePublishedIn = $node->firstChild->nodeValue;
				}

				$nodeCollection = $xpath->query ('//nmbe:publishedOnPage');
				foreach ($nodeCollection as $node)
				{
					$obj->publishedOnPage = $node->firstChild->nodeValue;
				}

				$keys = array();
				$values = array();
	
	
				foreach ($obj as $k => $v)
				{
					if ($v != '')
					{
						$keys[] = $k;
						$values[] = "'" . addslashes($v) . "'";
					}
				}
	
				if ($chapter)
				{
					$keys[] = 'isPartOf';
					$values[] = "'Y'";
				}
	
	
				$sql = 'REPLACE INTO `names` ('
					. join(",", $keys) . ') VALUES ('
					. join(",", $values) . ');';
		
				echo $sql . "\n";
			}
		}
	}
}	

?>
