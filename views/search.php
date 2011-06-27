<h2>Search Your Library</h2>
<?php
//set default value for Worldcat API key
$key = isset($_GET['key']) ? trim(strip_tags(urlencode($_GET['key']))) : 'YOUR WORLDCAT API KEY HERE';
//set default value for query
$q = isset($_GET['q']) ? trim(strip_tags(urlencode($_GET['q']))) : null;
//set default value for latitude
$lat = isset($_GET['lat']) ? $_GET['lat'] : null;
//set default value for longitude
$lng = isset($_GET['lng']) ? $_GET['lng'] : null;
//set default value for library collection to search - list available at http://www.oclc.org/contacts/libraries/
//docs here - http://oclc.org/developer/documentation/worldcat-search-api/library-catalog-url 
$library = isset($_GET['library']) ? trim(strip_tags($_GET['library'])) : 'MZF';

//check if the starting row variable was passed
if (!isset($_GET['start']) or !is_numeric($_GET['start'])) {
	//we give the value of the starting row to 0 because nothing was found in URL
	$start = '1';
//otherwise we take the value from the URL
} else {
	$start = (int)$_GET['start'];
}
//check if the limit result set variable was passed
if (!isset($_GET['limit']) or !is_numeric($_GET['limit'])) {
	//we give the value of the starting row to 5 because nothing was found in URL
	$limit = '10';
//otherwise we take the value from the URL, escape it for mysql, and make sure it is an integer
} else {
	$limit = (int)$_GET['limit'];
}

//set base url for our opensearch request to Worldcat Search API
$base = 'http://www.worldcat.org/webservices/catalog/search/worldcat/opensearch?';

$params = array(
  'q' => $q,
  'format' => 'atom', //type of format to output
  'cformat' => 'mla', //append citation format
  'start' => $start, //starting number for results to return
  'count' => $limit, // optional argument supplies number of results to return
  'wskey' => $key, //Worldcat API key
  //all possible options are documented at http://worldcat.org/devnet/wiki/SearchAPIDetails
);

if (is_null($q)): //show form and allow the user to search
?>

<form id="searchBox" method="get" action="./index.php?view=search">
<input type="hidden" name="lat" id="lat" value="" />
<input type="hidden" name="lng" id="lng" value="" /> 
<fieldset>
<label for="q">Search</label> 
<input type="text" maxlength="200" name="q" id="q" tabindex="1" value="keyword, name, title..." onclick="if (this.value == 'keyword, name, title...') { this.value = ''; }" onblur="if (this.value == '') { this.value = 'keyword, name, title...'; }" /> 
<button type="submit" class="button">Search</button> 
</fieldset> 
</form> 

<?php
else: //if form has query, show form and process 
?>
        
<form id="searchBox" method="get" action="./index.php?view=search">
<input type="hidden" name="lat" id="lat" value="" />
<input type="hidden" name="lng" id="lng" value="" /> 
<fieldset> 
<label for="q">Search</label> 
<input type="text" maxlength="200" name="q" id="q" tabindex="1" value="keyword, name, title..." onclick="if (this.value == 'keyword, name, title...') { this.value = ''; }" onblur="if (this.value == '') { this.value = 'keyword, name, title...'; }" /> 
<button type="submit" class="button">Search</button> 
</fieldset> 
</form> 

<?php
//-hello REMOVE for production
echo $base.http_build_query($params);

//build request, encode entities (using http_build_query), and send to Worldcat Search API
$request = simplexml_load_file($base.http_build_query($params));

//create xml object(s) out of response from Worldcat Search API
$data = $request;

//prepare opensearch namespace for parsing
$opensearch = $data->children('http://a9.com/-/spec/opensearch/1.1/');

//check for results, parse xml elements, and display as html
if (!empty($data->entry)) {
	//-hello echo '<p class="getFeed"><a href="feed.php?q='.urlencode($q).'&amp;count=25">Worldcat XML Feed for <strong>"'.$q.'"</strong></a></p>'."\n";
	echo '<h2 class="result">'.$opensearch->totalResults.' matches for your query <strong>"'.$q.'"</strong></h2>'."\n";
	//display links to more items if there are more than 5 items - top of results page
	if ($start == 1) {
		$next = $start + 10;
		echo '<a class="fwd" href="./index.php?view=search&q='.$q.'&lat='.$lat.'&lng='.$lng.'&start='.$next.'&limit='.$limit.'">Next</a>'."\n";
	} elseif ($start > 1) { 
		$next = $start + 10;
		$previous = $start - 10;
		echo '<a class="bck" href="./index.php?view=search&q='.$q.'&lat='.$lat.'&lng='.$lng.'&start='.$previous.'&limit='.$limit.'">Previous</a>'."\n";
		echo '<a class="fwd" href="./index.php?view=search&q='.$q.'&lat='.$lat.'&lng='.$lng.'&start='.$next.'&limit='.$limit.'">Next</a>'."\n";
	} elseif ($start + 10 >= $opensearch->totalResults) { 
		$previous = $start - 10; 
		echo '<a class="bck" href="./index.php?view=search&q='.$q.'&lat='.$lat.'&lng='.$lng.'&start='.$previous.'&limit='.$limit.'">Previous</a>'."\n";
	}
	echo '<ul class="result">'."\n";
		foreach ($data->entry as $result) {
			//prepare opensearch namespace for parsing
			$oclc = $result->children('http://purl.org/oclc/terms/');
			//prepare opensearch namespace for parsing
			$dc = $result->children('http://purl.org/dc/elements/1.1/');
			//display the info to user
			echo '<li>'."\n";
			echo '<h3>'.$result->title.'</h3>'."\n";
			echo '<p>'."\n";
			echo '<em>'.$result->id.'</em><br />'."\n";
			if (strlen($result->author->name) > 3) { echo '<strong>Author(s):</strong> '.$result->author->name.'<br />'."\n"; }
				elseif (strlen($result->author->name) < 3) { echo '<strong>Author(s):</strong> Unknown<br />'."\n"; }
			if (strlen($result->summary) > 3) { echo '<strong>Summary:</strong> '.$result->summary.'<br />'."\n"; }
				elseif (strlen($result->summary) < 3) { echo '<strong>Summary:</strong> Not available<br />'."\n"; }
			echo '<strong>OCLC ID:</strong> '.$oclc->recordIdentifier.'<br />'."\n";
			echo '<strong>ID:</strong> '.$dc->identifier[0].'<br />'."\n";
			//echo '<strong>Cite This: </strong>'.$result->content.'<br />'."\n";
			echo '<a class="citation" href="'.$result->id.'">Citation (Worldcat full site)</a>'."\n";
			echo '<a class="expand" href="./index.php?view=where&id='.$oclc->recordIdentifier.'&lat='.$lat.'&lng='.$lng.'&title='.urlencode($result->title).'">Find local libraries</a>'."\n";
			echo '<a class="download" href="./index.php?view=local&id='.$oclc->recordIdentifier.'&library='.$library.'&title='.urlencode($result->title).'">Search local catalog</a>'."\n";
			echo '</p>'."\n";        
			echo '</li>'."\n";		
		}
	echo '</ul>'."\n";
	//display links to more items if there are more than 5 items - bottom of results page
	if ($start == 1) {
		$next = $start + 10;
		echo '<a class="fwd" href="./index.php?view=search&q='.$q.'&lat='.$lat.'&lng='.$lng.'&start='.$next.'&limit='.$limit.'">Next</a>'."\n";
	} elseif ($start > 1) { 
		$next = $start + 10;
		$previous = $start - 10;
		echo '<a class="bck" href="./index.php?view=search&q='.$q.'&lat='.$lat.'&lng='.$lng.'&start='.$previous.'&limit='.$limit.'">Previous</a>'."\n";
		echo '<a class="fwd" href="./index.php?view=search&q='.$q.'&lat='.$lat.'&lng='.$lng.'&start='.$next.'&limit='.$limit.'">Next</a>'."\n";
	} elseif ($start + 10 >= $opensearch->totalResults) { 
		$previous = $start - 10; 
		echo '<a class="bck" href="./index.php?view=search&q='.$q.'&lat='.$lat.'&lng='.$lng.'&start='.$previous.'&limit='.$limit.'">Previous</a>'."\n";
	}
} else {
	echo '<h2 class="result">No results for your query <strong>"'.$q.'"</strong>. Try a <a href="./index.php?view=search">new search</a>.</h2>'."\n";
}

//end submit isset if statement on line 35
endif;
?>
