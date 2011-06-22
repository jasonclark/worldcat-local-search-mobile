<?php
//set default value for oclc number
$id = isset($_GET['id']) ? strip_tags((int)$_GET['id']) : null;
//set default value for latitude
$lat = isset($_GET['lat']) ? strip_tags((int)$_GET['lat']) : null;
//set default value for longitude
$lat = isset($_GET['lng']) ? strip_tags((int)$_GET['lng']) : null;
//set default value for type of library
$type = isset($_GET['type']) ? strip_tags((int)$_GET['type']) : null;
//set default value for title to search local library holdings
$title = isset($_GET['title']) ? trim(strip_tags(urldecode($_GET['title']))) : null;
//set default value for back button to navigate history
$history = isset($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : null;

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

//set base url for our library location request to Worldcat Search API
$base = 'http://www.worldcat.org/webservices/catalog/content/libraries/'.$id.'?';

$params = array(
	'lat' => $lat, //latitude bounding area
	'lon' => $lng, //longitude bounding area
	'startLibrary' => $start, //optional argument supplies where to place cursor for results to return
	'maximumLibraries' => $limit, //optional argument supplies number of results to return 
	'libtype' => $type, //type of library to search where 1 = academic, 2 = public, 3 = government, and 4 = other
	'format' => 'json', //append output format
	//'callback' => 'libLocations',
	'wskey' => 'B3F6fY0fdaYyWFaU2a5a25QD28BsxH6H8wZnViTESKxZZBR7Fg71nC0V6IeXa78EKAYsGzhMAyYyEihv', //Worldcat API key
	//all possible options are documented at http://oclc.org/developer/documentation/worldcat-search-api/library-locations
);

$url = $base.http_build_query($params);
//-hello echo $url;

//build request and send to Google Ajax Search API
$request = file_get_contents($url);

//decode json object(s) out of response from Google Ajax Search API
$data = json_decode($request,true); 

if (!empty($data['library'])):
?>
<h2 class="result">Libraries Near You with <strong>"<?php echo $data['title']; ?>"</strong></h2>
<a class="bck" href="<?php echo $history; ?>">back</a>
<ul class="result">
<?php foreach ($data['library'] as $result) { ?>
	<li>
		<h3><?php echo $result['institutionName']; ?></h3>
		<p>
			address: <?php echo $result['streetAddress1'].' '.$result['streetAddress2'].' '.$result['city'].', '.$result['state'] ?><br />
			postal code: <?php echo $result['postalCode']; ?><br />
			country: <?php echo $result['country']; ?><br />
			id: <?php echo $result['oclcSymbol']; ?><br />
			<!--<a class="expand" href="<?php //echo rawurldecode($result['opacUrl']); ?>">Catalog link</a><br />-->
			<?php
			//parse opacUrl value from worldcat, remove worldcat frame, get direct link to item in local catalog
			$query = parse_url($result['opacUrl'], PHP_URL_QUERY);
			parse_str($query);
			?>
			<a class="expand" href="<?php echo $url; ?>">Catalog link</a><br />
			distance: <?php echo $result['distance']; ?> mile(s)
		</p>
	</li>
<?php } ?>
</ul>
<?php
else: //if query is empty
?>
<h2 class="result">No local library results for <strong>"<?php echo $title; ?>"</strong>. Try a <a href="./index.php?view=search">new search</a>.</h2>
<?php
//end submit isset if statement on line 35
endif;
?>