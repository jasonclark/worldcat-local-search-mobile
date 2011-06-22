<h2>Explore Your Library ...</h2>
<h3>Recent Searches</h3>
<p class="terms">
                <?php
            //reads the number of last lines from file that you specify
            $file = array_reverse(file("search-log.txt"));
            //remove repeated terms
	    $file = array_unique($file);
		$limit = 15;
                for ($i = 0; $i < $limit; $i++ ) {
                    //strip comma from end of term string
                    $term = substr("$file[$i]",0,-2);
                    echo '<a href="./index.php?view=search&q='.urlencode($term).'">'.$term.'</a>'."\n";
                }
            /*
            //reads all lines from text file
            $handle = fopen("searchLog.txt", "r");
            while (list($term) = fgetcsv($handle, 1024, ",")) {
                echo '<li><a href="./index.php?view=search&q='.urlencode($term).'">'.$term.'</a></li>'."\n";
            }
            fclose($handle);
            */
            ?>
</p>
<h3>Featured Keywords</h3>
<p class="terms">
    <?php
	//pass database parameters and connect to database
	include_once '../meta/assets/dbconnect.inc';

        //get keywords that start with the current variable
        $query = "SELECT mods_extension FROM mods WHERE mods_status != 'i' GROUP BY mods_extension ORDER BY RAND() LIMIT 3";
        $getKeywords = mysql_query($query);
        $countKeywords = mysql_num_rows($getKeywords);
        //set array values and begin while loop for mysql query
        $i = 0;
        $keyArray = array();
        while($i < $countKeywords) {
                $keyword = mysql_result($getKeywords, $i, 'mods_extension');
                $tempArray = explode(',', $keyword);

                foreach($tempArray as $var) {
                        //next three lines of code format the string so that all strings in the keyArray have the same format   
                        $temp = trim($var);
                        $temp = strtolower($temp);
                        $bIn = false;

                        foreach($keyArray as $var) {
                                if($temp == $var || strstr($var, $temp)) {
                                        $bIn = true;
                                        break;
                                }
                        }
                        if(!$bIn) {
                                if($temp != "")
                                $keyArray[] = $temp;
                        }
                }
                $i++;
        }

        //performs a case insensitive "natural order" sort on the keyword array 
        natcasesort($keyArray);

        foreach($keyArray as $var) {
                $temp = strtolower($var);
                $temp = trim($temp);
                echo '<a href="./index.php?view=search&q='.urlencode($temp).'">'.$temp.'</a> ';
        }
        ?>
</p>
