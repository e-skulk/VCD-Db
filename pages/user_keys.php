<? 
	
	$numRecords = 25;	
	$batch = 1;
	$start = 0;
	
	global $ClassFactory;
	global $language;
	$VCDClass = $ClassFactory->getInstance('vcd_movie');
	$SETTINGSClass = $ClassFactory->getInstance('vcd_settings');
	
	$user_id = $_SESSION['user']->getUserID();
	if (isset($_POST['save'])) {
		// Loop through the posted values
		foreach ($_POST as $key => $value) {
			if (is_numeric($key) && (strcmp($value, "") != 0)) {
				// check for existing data
				$arr = $SETTINGSClass->getMetadata($key, $user_id, 'mediaindex');
				if (is_array($arr) && sizeof($arr) == 1) {
					// update the Obj
					$obj = $arr[0];
					$obj->setMetadataValue($value);
					$SETTINGSClass->updateMetadata($obj);
				} else {
					// create new Obj
					$obj = new metadataObj(array('',$key, $user_id, 'mediaindex', $value));
					$SETTINGSClass->addMetadata($obj);
				}
			} elseif (strcmp($value, "") == 0) {
				// Check if a existing value has been set to empty
				$arr = $SETTINGSClass->getMetadata($key, $user_id, 'mediaindex');
				if (is_array($arr) && sizeof($arr) == 1) {
					// update the Obj
					$obj = $arr[0];
					$obj->setMetadataValue($value);
					$SETTINGSClass->updateMetadata($obj);
				}
			}
		}
	}
	
	
	if (isset($_GET['batch'])) {
		$batch = $_GET['batch'];
		$end = $numRecords*$batch;
		$start = $end - $numRecords;
	} else {
		$end = $numRecords;
	}
		
	$batch++;
	
	
?>

<form name="customkeys" method="post" action="./?page=private&o=movies&do=customkeys&batch=<?=$batch?>">
<table cellpadding="1" cellspacing="1" border="0" width="100%" class="tblsmall">
<tr>
	<td class="bold" width="65%"><?=$language->show('M_TITLE')?></td>
	<td class="bold"><?=$language->show('M_MEDIA')?></td>
	<td class="bold" width="5%"><?=$language->show('X_KEY')?></td>
</tr>
<? 


$arrMovies = $VCDClass->getAllVcdByUserId($user_id, true);
$currRecordCount = 0;
if ($end > sizeof($arrMovies)) {
	$end = sizeof($arrMovies);
}


if ($start > $end) {
	redirect('?page=private&o=movies&do=customkeys');
}

for ($j = $start; $j < $end; $j++) {
	$obj = $arrMovies[$j];
	$currRecordCount++;
	$arr = $SETTINGSClass->getMetadata($obj->getID(), $user_id, 'mediaindex');
	$cusID = "";
	if (is_array($arr) && sizeof($arr) == 1) {
		$cusID = $arr[0]->getMetadataValue();
	}
	
	print "<tr>";
	print "<td>".$obj->getTitle()."</td>";
	print "<td nowrap=\"nowrap\">".$obj->showMediaTypes()."</td>";
	print "<td align=\"right\"><input type=\"text\" size=\"3\" value=\"".$cusID."\" class=\"inp\" name=\"".$obj->getID()."\"/></td>";
	print "</tr>";
	
	
}

	print "<tr><td>";
	print "<select name=\"sellist\" onchange=\"showPage(this, false)\">";
	
	$from = 1;
	$to = $numRecords;
	do {
		
		$key = ((($from-1)/$numRecords)+1);
		if ($batch == 1) {
			print "<option value=\"./?page=private&amp;o=movies&amp;do=customkeys&amp;batch=".$key."\" selected=\"selected\">".$from. " - " . $to."</option>\n";
		} else {
			if ($key == ($batch-1) && ($batch-1) != 1) {
				print "<option value=\"./?page=private&amp;o=movies&amp;do=customkeys&amp;batch=".$key."\" selected=\"selected\">".$from. " - " . $to."</option>\n";
			} else {
				print "<option value=\"./?page=private&amp;o=movies&amp;do=customkeys&amp;batch=".$key."\">".$from. " - " . $to."</option>\n";
			}
		}
		$from += $numRecords;
		$to += $numRecords;
		
	} while ($to < sizeof($arrMovies));
	
	print "<select>";
	
	if ($currRecordCount < $numRecords) {
		$savetext = $language->show('X_SAVE');
	} else {
		$savetext = $language->show('X_SAVENEXT'). " " . $numRecords;
	}
	
	
	print "</td><td colspan=\"2\" align=\"right\"><input type=\"submit\" class=\"inp\" name=\"save\" value=\"".$savetext."\"></td></tr>";



?>


</table>
</form>