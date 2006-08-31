<?
$cd_id = $_GET['vcd_id'];

?>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="displist">
<tr>
	<td width="75%" class="header"><?=language::translate('M_MOVIE')?></td>
	<td width="25%" class="header"><?=language::translate('M_INFO')?></td>
</tr>
<tr>
	<td valign="top">
	<!-- Info table -->
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td valign="top" width="10%">
		<?
		$coverObj = $movie->getCover("thumbnail");
		if (!is_null($coverObj))
			$coverObj->showImage();
		?>
		</td>
		<td valign="top">
			<table cellspacing="0" cellpadding="0" border="0" width="100%">
			<tr>
				<td colspan="2"><strong><?= $movie->getTitle() ?></strong></td>
			</tr>
			<tr>
				<td width="30%"><?=language::translate('M_CATEGORY')?>:</td>
				<td><?
				$mObj = $movie->getCategory();
				if (!is_null($mObj)) {
					print "<a href=\"./?page=category&amp;category_id=".$mObj->getID()."\">".$mObj->getName()."</a>";
				}
				?></td>
			</tr>
			<tr>
				<td nowrap="nowrap"><?=language::translate('M_YEAR')?>:</td>
				<td><?= $movie->getYear() ?></td>
			</tr>
			<tr>
				<td><?=language::translate('M_COPIES')?>:</td>
				<td><?= $movie->getNumCopies() ?></td>
			</tr>
			<tr>
				<td><?=language::translate('M_SCREENSHOTS')?></td>
				<td>
				<?
					if (isset($_GET['screens'])) {
						print "<a href=\"./?page=cd&amp;vcd_id=".$movie->getID()."\">".language::translate('M_HIDE')."</a>";
					}

					elseif ($VCDClass->getScreenshots($movie->getID())) {
						print "<a href=\"./?page=cd&amp;vcd_id=".$movie->getID()."&amp;screens=on\">".language::translate('M_SHOW')."</a>";
					} else {
						print language::translate('M_NOSCREENS');
					}
				?>
				</td>
			</tr>
			<?
				if (VCDUtils::isLoggedIn()) {
					if ($SETTINGSClass->isOnWishList($movie->getID())) {
						?><tr><td>&nbsp;</td><td><a href="./?page=private&amp;o=wishlist">(<?= language::translate('W_ONLIST')?>)</a></td></tr><?
					} else {
						?><tr><td>&nbsp;</td><td><a href="#" onclick="addtowishlist(<?=$movie->getID()?>)"><?= language::translate('W_ADD')?></a></td></tr><?
					}

				}
			?>
			<?
				if (VCDUtils::hasPermissionToChange($movie)) {
					?>
						<tr><td>&nbsp;</td><td><a href="#" onclick="loadManager(<?=$movie->getID()?>)"><?=language::translate('M_CHANGE')?></a></td></tr>
					<?
				}
				// Display seen box if activated
				if (VCDUtils::isLoggedIn() && VCDUtils::isOwner($movie) && $_SESSION['user']->getPropertyByKey('SEEN_LIST')) {
   					$arrList = $SETTINGSClass->getMetadata($movie->getID(), VCDUtils::getUserID(), 'seenlist');
   					print "<tr><td>&nbsp;</td><td>";
   					if (sizeof($arrList) == 1 && ($arrList[0]->getMetadataValue() == 1)) {
				   		print "<a href=\"#\"><img src=\"images/mark_seen.gif\" alt=\"".language::translate('S_NOTSEENITCLICK')."\" border=\"0\" style=\"padding-right:5px\" onclick=\"markSeen(".$movie->getID().", 0)\"/></a>";
				    	print language::translate('S_SEENIT');
				    } else {
				    	print "<a href=\"#\"><img src=\"images/mark_unseen.gif\" alt=\"".language::translate('S_SEENITCLICK')."\" border=\"0\" style=\"padding-right:5px\" onclick=\"markSeen(".$movie->getID().", 1)\"/></a>";
				    	print language::translate('S_NOTSEENIT');
				    }
				    print "</td></tr>";
				}



				// Display Play button
				if (VCDUtils::isLoggedIn() && VCDUtils::isOwner($movie)) {
					$command = "";
					if (getPlayCommand($movie, VCDUtils::getUserID(), $command)) {
						print "<tr><td>&nbsp;</td><td>";
					    print "<a href=\"javascript:void(0)\"><img src=\"images/play.gif\" border=\"0\" onclick=\"playFile('".$command."')\"/></a>";
				    	print "</td></tr>";
					}

				}


			?>
			<tr>
				<td colspan="2"><?= drawSourceSiteLogo($movie->getSourceSiteID(), $movie->getExternalID()); ?></td>
			</tr>
			</table>


		</td>
	</tr>
	</table>

	<? if (isset($_GET['screens']) && $VCDClass->getScreenshots($movie->getID())) {
		?>
		<h2>Screenshots</h2>
		<iframe id="screenshots" width="100%" height="470" src="screens.php?s_id=<?=$movie->getID()?>"
					frameborder="0" scrolling="no"></iframe>
		<?
	}
	?>



	<h2><?=language::translate('M_ACTORS')?></h2>
	<div id="actorimages" style="padding-left:10px;">
	<?
		$PORNClass = VCDClassFactory::getInstance("vcd_pornstar");
		$arr = $PORNClass->getPornstarsByMovieID($movie->getID());

		foreach ($arr as $pornstar) {
			$pornstar->showImage() ;
		}
	?></div>
	<p></p>


	<div id="copies">
	<h2><?= language::translate('M_AVAILABLE')?>:</h2>
	<? 
		$allMeta = $SETTINGSClass->getMetadata($cd_id, null, null, null);
		drawDVDLayers($movie, $allMeta);
		$movie->displayCopies($allMeta) 
	?>
	</div>

	<p></p>

	<h2><?=language::translate('M_COVERS')?></h2>
	<?
		foreach ($movie->getCovers() as $cover) {
			if (!$cover->isThumbnail()) {

				// Only show the front and back covers
				 if ((strpos($cover->getCoverTypeName(), 'Front') > 0) || (strpos($cover->getCoverTypeName(), 'Back') > 0)) {
				 	$cover->showImage();
				 }
			}
		}
	?>



	</td>
	<td valign="top" style="background-color:white">
	<h2>Studio</h2>
	<ul>
		<?
			if (is_numeric($movie->getStudioID())) {
				$studio = $PORNClass->getStudioByID($movie->getStudioID());
			} else {
				$studio = $PORNClass->getStudioByMovieID($movie->getID());
			}


			if ($studio instanceof studioObj) {
				print "<li><a href=\"./?page=adultcategory&amp;studio_id=".$studio->getID()."\">".htmlspecialchars($studio->getName())."</a></li>";
			} else {
				print "<li>No Studio information</li>";
			}

		?>
	</ul>
	<br/><br/>

	<h2><?=language::translate('EM_SUBCAT')?></h2>
	<ul>
	<?
		$subcats = $PORNClass->getSubCategoriesByMovieID($movie->getID());
		foreach ($subcats as $categoryObj) {
			print "<li><a href=\"./?page=adultcategory&amp;category_id=".$categoryObj->getId()."\">".$categoryObj->getName(true) . "</a></li>";
		}

	?>
	</ul>
	<br/><br/>

	<h2><?=language::translate('M_COVERS')?></h2>
	<?
		print "<ul>";
		foreach ($movie->getCovers() as $cover) {

			if (!$cover->isThumbnail()) {
				 if ((strpos($cover->getCoverTypeName(), 'Front') > 0) || (strpos($cover->getCoverTypeName(), 'Back') > 0)) {
				 	print "<li><a title=\"".human_file_size($cover->getFilesize())."\" href=\"#".$cover->getCoverTypeName()."\">" . $cover->getCoverTypeName() . "</a></li>";
				 } else {
					print "<li><a href=\"#\" title=\"".human_file_size($cover->getFilesize())."\" onclick=\"showcover('".$cover->getFilename()."',".(int)$cover->isInDB().",".$cover->getImageID().")\">".$cover->getCoverTypeName() . "</a></li>";
				 }

			}
		}
		print "</ul>";
	?>


	<br/><br/>
	<h2><?=language::translate('M_ACTORS')?></h2>
	<div id="actorlist"><ul>
	<?

		foreach ($arr as $pornstar) {

			print "<li><a href=\"./?page=pornstar&amp;pornstar_id=".$pornstar->getID()."\">".$pornstar->getName() . "</a></li>";
		}
		unset($arr)
	?>
	</ul></div>
	<br/>
	<div id="similar">
	<?
		$simArr = $VCDClass->getSimilarMovies($movie->getID());
		if (is_array($simArr) && sizeof($simArr) > 0) {

			print "<h2>".language::translate('M_SIMILAR')."</h2>";
			print "<form name=\"sim\" action=\"get\"><select name=\"similar\" size=\"1\" onchange=\"goSimilar(this.form)\">";
			evalDropdown($simArr, 0, true, language::translate('X_SELECT'));
			print "</form><br/>";
		}

	?>
	</div>
	<div id="comments">
	<h2><?= language::translate('C_COMMENTS')?> (<a href="javascript:show('newcomment')"><?= language::translate('X_NEW')?></a>)</h2>
	</div>
	<div id="newcomment" style="left:665px;visibility:hidden;display:none;position:absolute">
		<?  if(!VCDUtils::isLoggedIn()) {
				print "<span class=\"bold\">". language::translate('C_ERROR')."</span>";
			} else { ?>

		<form name="newcomment" method="POST" action="exec_form.php?action=addcomment">
		<input type="hidden" name="vcd_id" value="<?=$movie->getID()?>"/>
		<table cellpadding="0" cellspacing="0" border="0" class="plain">
			<tr>
				<td colspan="2"><textarea name="comment" rows="4" cols="20"></textarea></td>
			</tr>
			<tr>
				<td><?= language::translate('M_PRIVATE')?>:</td>
				<td><input type="checkbox" name="private" class="nof" value="private"/></td>
			</tr>
			<tr>
				<td colspan="2"><input type="submit" value="<?=language::translate('C_POST')?>"/></td>
			</tr>
		</table>
		</form>
		<? } ?>
		<br/>
	</div>

	<?
		$commArr = $SETTINGSClass->getAllCommentsByVCD($movie->getID());
		if (empty($commArr)) {
			print "<ul><li>".language::translate('C_NONE')."</li></ul>";
		} else {
			print "<ul>";
			foreach ($commArr as $commObj) {

				if ($commObj->isPrivate()) {
					if (VCDUtils::isLoggedIn() && $commObj->getOwnerID() == VCDUtils::getUserID())	{
						print "<li>".$commObj->getOwnerName()." (<i>Private</i>) <a href=\"#\" onclick=\"location.href='exec_query.php?action=delComment&amp;cid=".$commObj->getID()."'\"><img src=\"images/icon_del.gif\" alt=\"Delete comment\" align=\"absmiddle\" border=\"0\"/></a>
					   <br/><i style=\"display:block\">".$commObj->getComment(true)."</i></li>";
					}
				} else {
					print "<li>".$commObj->getOwnerName()."  <a href=\"#\" onclick=\"location.href='exec_query.php?action=delComment&amp;cid=".$commObj->getID()."'\"><img src=\"images/icon_del.gif\" alt=\"Delete comment\" align=\"absmiddle\" border=\"0\"/></a>
					   <br/><i style=\"display:block\">".$commObj->getComment(true)."</i></li>";
				}


			}
			print "</ul>";
		}

	?>

	</td>
</tr>
</table>

