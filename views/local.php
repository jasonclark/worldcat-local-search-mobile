<?php
//set default value for oclc number
$id = isset($_GET['id']) ? strip_tags((int)$_GET['id']) : null;
//set default value for library collection to search - list available at http://www.oclc.org/contacts/libraries/
$library = isset($_GET['lib']) ? trim(strip_tags($_GET['lib'])) : 'MZF';
//set default value for title to search locally
$title = isset($_GET['title']) ? trim(strip_tags(urldecode($_GET['title']))) : null;
//set default value for back button to navigate history
$history = isset($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : null;

$backHistory = htmlspecialchars($_SERVER['HTTP_REFERER']);
//set base url for our opensearch request to Worldcat Search API
$base = 'http://www.worldcat.org/webservices/catalog/content/libraries/'.$id.'?';

$params = array(
	'oclcsymbol' => $library,
	'wskey' => 'YOUR-WORLDCAT-API-KEY-HERE', //Worldcat API key
	//all possible options are documented at http://oclc.org/developer/documentation/worldcat-search-api/library-catalog-url 
);

//REMOVE for production - prints out raw API call
//echo $base.http_build_query($params);

//build request, encode entities (using http_build_query), and send to Worldcat Search API
$data = simplexml_load_file($base.http_build_query($params));

//check for results, parse xml elements, and display as html
if (!empty($data->holding)):
?>
	<h2 class="result">Local Catalog Search for <strong>"<?php echo $title; ?>"</strong></h2>
	<ul class="result">
<?php foreach ($data->holding as $item) { ?>
		<li>
        <h3><?php echo $item->physicalLocation; ?></h3>
		<p><?php if (strlen($item->physicalAddress->text) > 3) { echo '<em>'.$item->physicalAddress->text.'</em><br />'."\n"; }
				elseif (strlen($item->physicalAddress->text) < 3) { echo '<em>'.$item->physicalAddress->text.'</em><br />'."\n"; } ?>
			<a class="citation" href="<?php echo $item->electronicAddress->text; ?>">Citation (Local catalog record)</a>
		</p>        
		</li>		
<?php } 
		
?>
	</ul>
	<a class="bck" href="<?php echo $history; ?>">back</a>
<?php
else: //if query is empty 
?>
<h2 class="result">No local search results for <strong>"<?php echo $title; ?></strong>". Try a <a href="./index.php?view=search">new search</a>.</h2>
<a class="bck" href="<?php echo $history; ?>">back</a>

<?php
//end submit isset if statement on line 35
endif;
?>