<?
	;
	$SETTINGSClass = VCDClassFactory::getInstance('vcd_settings');
	$PORNClass = VCDClassFactory::getInstance('vcd_pornstar');
?>

<h2><?=language::translate('EM_INFO')?></h2>

<?
	$picname = "";
	if (is_null($fetchedObj->getImage())) { 
		$pic = 'images/noimage.gif'; 
	} else { 
		$pic = TEMP_FOLDER.$fetchedObj->getImage(); 
		$picname = $fetchedObj->getImage();
	}
	$picture = "<img src=\"{$pic}\" border=\"0\" class=\"imgx\">";


?>


<form name="empire_comfirm" action="exec_form.php?action=adultmoviefetch" method="post">
<input type="hidden" name="thumbnail" value="<?=$picname?>"/>
<table cellspacing="1" cellpadding="1" border="0" width="100%" class="list">
<tr>
	<td valign="top" width="16%"><h2>Thumbnail</h2><? echo $picture ?></td>
	<td valign="top" width="42%"><h2><?=language::translate('EM_DESC')?></h2>

		<table cellspacing="1" cellpadding="1" border="0" width="100%">
			<tr>
				<td class="tblb"><?=language::translate('M_TITLE')?>:</td>
				<td><input type="text" value="<?=$fetchedObj->getTitle() ?>" name="title" class="input" size="30"/></td>
			</tr>
			<tr>
				<td class="tblb">Studio:</td>
				<td><select name="studio" class="plain" size="1">
					<?
						$studio_fetchID = "";
						$studio = $PORNClass->getStudioByName($fetchedObj->getStudio());
						if ($studio instanceof studioObj ) {
							$studio_fetchID = $studio->getId();
						}

						evalDropdown($PORNClass->getAllStudios(), $studio_fetchID);
					?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="tblb"><?=language::translate('M_YEAR')?>:</td>
				<td><input type="text" value="<?= $fetchedObj->getYear() ?>" name="year" class="input"/></td>
			</tr>
			<tr>
				<td class="tblb" nowrap="nowrap">ID:</td>
				<td><input type="text" value="<?= $fetchedObj->getObjectID() ?>" name="id" class="input" readonly/></td>
			</tr>
			<tr>
				<td colspan="2"></td>
			</tr>
			<tr>
				<td colspan="2">
				<span class="tblb"><?=language::translate('M_COMMENT')?></span>
				<textarea name="comment" cols="26" rows="4" class="input"></textarea>
				<br/>
				<?=language::translate('M_PRIVATE')?>: <input type="checkbox" class="nof" value="private" name="private"/>

				</td>
			</tr>
		</table>

	 </td>
	<td valign="top" width="42%"><h2><?=language::translate('M_DETAILS')?></h2>
		<table>
		<tr>
			<td class="tblb"><?=language::translate('M_CATEGORY')?>:</td>
			<td><select name="category" class="plain">
				<?
					$adult_id = $SETTINGSClass->getCategoryIDByName('adult');
					evalDropdown($SETTINGSClass->getAllMovieCategories(), $adult_id);

				?>
			</select>

			</td>
		</tr>
		<tr>
			<td class="tblb"><?=language::translate('M_MEDIATYPE')?>:</td>
			<td>
			<?
			print "<select name=\"mediatype\" size=\"1\" class=\"plain\">";
			print "<option value=\"null\">".language::translate('X_SELECT')."</option>";
			foreach ($SETTINGSClass->getAllMediatypes() as $mediaTypeObj) {
				print "<option value=\"".$mediaTypeObj->getmediaTypeID()."\">".$mediaTypeObj->getDetailedName()."</option>";
				if ($mediaTypeObj->getChildrenCount() > 0) {
					foreach ($mediaTypeObj->getChildren() as $childObj) {
						print "<option value=\"".$childObj->getmediaTypeID()."\">&nbsp;&nbsp;".$childObj->getDetailedName()."</option>";
					}
				}

			}
			print "</select>"; ?>
			</td>
		</tr>
		<tr>
			<td class="tblb"><?=language::translate('M_NUM')?>:</td>
			<td>
			<select name="cds" class="plain"><option value="null"><?=language::translate('X_SELECT')?></option>
			<? for($i=1;$i<7;$i++){print "<option value=\"$i\">$i</option>";} ?>
			</select>
			</td>
		</tr>
		<tr>
			<td colspan="2" class="tblb"><?=language::translate('EM_SUBCAT')?>:<br/>
			<input type="hidden" name="id_list" id="id_list"/>
			<table cellspacing="0" cellpadding="2" border="0">
			<tr>
				<td>
					<select name="available" id="available" size=8 style="width:110px;" onDblClick="moveOver(this.form, 'available', 'choiceBox');" class="plain">
					<?
						evalDropdown($PORNClass->getSubCategories(), -1, false);
					?>
					</select>
				</td>
				<td>
					<input type="button" value="&gt;&gt;" onclick="moveOver(this.form, 'available', 'choiceBox');" class="input" style="margin-bottom:5px;"/><br/>
					<input type="button" value="<<" onclick="removeMe(this.form, 'available', 'choiceBox');" class="input"/>
				</td>
				<td>
					<select multiple name="choiceBox" id="choiceBox" style="width:110px;" size="8" class="plain">
					<?
						$valid_categories = $PORNClass->getValidCategories($fetchedObj->getCategories());
						evalDropdown($valid_categories, -1, false);
					?>
					</select>
				</td>
			</tr>
			</table>




			</td>
		</tr>
		</table>


	</td>
</tr>
<tr>
	<td colspan="3"><h2><?=language::translate('EM_DETAILS')?></h2></td>
</tr>
<tr>
	<td colspan="3">
		<table cellspacing="1" cellpadding="1" border="0" width="100%">
		<tr>
			<td valign="top" width="50%"><h2><?=language::translate('EM_STARS')?></h2>
			<ul>
			<?


			foreach ($fetchedObj->getActors() as $value => $key) {
 			  $pornstar = $PORNClass->getPornstarByName($key);

			  if ($pornstar instanceof pornstarObj && $pornstar->getName() != '') {
			   	echo "<li class=\"green\">";
				echo "<input type=\"checkbox\" name=\"pornstars[]\" value=\"".$pornstar->getID()."\" checked class=\"nof\"/>&nbsp;";
			  	echo "<a href=\"./?page=pornstar&pornstar_id=".$pornstar->getID()."\" target=\"_new\">".$pornstar->getName()."</a></li>";

			  } else {
			  	echo "<li class=\"red\">";
				echo "<input type=\"checkbox\" name=\"pornstars_new[]\" value=\"".$key."\" class=\"nof\"/>&nbsp;";
			  	echo $key . "</li>";
			  }


			}
			?>
			</ul>
			</td>
			<td valign="top" width="25%">
			<h2><?=language::translate('X_ATTENTION')?></h2>
			<?=language::translate('EM_NOTICE')?>
			</td>
			<td valign="top" width="25%"><h2><?=language::translate('EM_FETCH')?></h2>
			<input type="checkbox" name="imagefetch[]" value="VCD Front Cover" checked="checked" class="nof"/>Front Cover<br/>
			<input type="checkbox" name="imagefetch[]" value="VCD Back Cover" checked="checked" class="nof"/>Back Cover<br/>
			<?
				$screens = $fetchedObj->getScreenShotCount();
				if ($screens > 0) {
					print "<input type=\"checkbox\" name=\"imagefetch[]\" value=\"screenshots\" checked class=\"nof\"/>Screenshots (".$screens.")<br/>";
					print "<input type=\"hidden\" name=\"screenshotcount\" value=\"".$screens."\"/>";
				} else {
					print "<input type=\"checkbox\" name=\"imagefetch[]\" value=\"screenshots\" disabled class=\"nof\"/>Screenshots<br/>";
				}
			?>


			</td>
		</tr>
		</table>

	</td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td><input type="submit" value="<?=language::translate('X_CONFIRM')?>" class="buttontext" onclick="return val_Empire(this.form)"/></td>
</tr>
</table>
</form>

