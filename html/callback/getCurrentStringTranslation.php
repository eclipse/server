<?php
/*******************************************************************************
 * Copyright (c) 2007 Eclipse Foundation and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Paul Colton (Aptana)- initial API and implementation
 *    Eclipse Foundation
*******************************************************************************/

require_once("cb_global.php");


$string_id = $App->getHTTPParameter("string_id", "POST");

$language = $_SESSION['language'];
$version = $_SESSION['version'];
$project_id = $_SESSION['project'];

$query = "select 
			strings.string_id,
			strings.value as string_value,
			translations.value as translation_value,
			files.name,
			strings.name as token,
			max(translations.version)
		  from
		  	files,
		  	strings
		  	left join translations on
		  		(strings.string_id = translations.string_id 
		  		 and 
		  		 translations.is_active != 0 
		  		 and 
		  		 translations.language_id = '".addslashes($language)."')
		  where
		  	strings.is_active != 0
		  and
			  strings.string_id = '".addslashes($string_id)."'
		  and
		  	  strings.file_id = files.file_id
		  and
		  	  files.version = '".addslashes($version)."'
		  group by translations.version
		  order by translations.version desc
		  limit 1
			";

//print $query;

$res = mysql_query($query,$dbh);

$line = mysql_fetch_array($res, MYSQL_ASSOC);

//print_r($line);

$trans = "";

if($line['translation_value']){
	$trans = " AND translations.value = '".addslashes($line['translation_value'])."'  			
				AND 
			  translations.is_active = 1
	";
}else{
//	$trans = "translations.value is NULL ";
}

$query = "select 
				strings.string_id, strings.value, strings.name max(translations.translation_id)
			FROM 
				files,
				strings								
			left join 
				translations 
			on 
				translations.string_id = strings.string_id 
			where
				files.file_id = strings.file_id 
			AND			
				files.project_id = '".addslashes($project_id)."' 
			AND 
				strings.value = '".addslashes($line['string_value'])."'

				$trans
			AND
				files.is_active = 1
				group by translations.string_id
				";
//			AND 
//				files.name = (SELECT files.name FROM files as F where F.project_id = '".addslashes($project_id)."')
				
$query = "SELECT 
			S.*
		  FROM 
		  	strings AS S 
		  inner join files AS F on F.file_id = S.file_id 
		  inner join translations AS T on T.string_id = S.string_id 
		  where 
		  	F.project_id = '".addslashes($project_id)."' 
		  AND 
		  	F.file_id in (SELECT files.file_id FROM files where files.project_id = '".addslashes($project_id)."') 
		  AND 
		  	S.value = '".addslashes($line['string_value'])."'
		  and 
		  	T.value = '".addslashes($line['translation_value'])."' 
		  AND 
		  	T.is_active = 1
		  	";

//INSERT INTO translations SELECT S.string_id, 2, "Some Enhanced Text", other fields.....  FROM strings AS S inner join files AS F on F.file_id = S.file_id inner join translations AS T on T.string_id = S.string_id where F.project_id = "eclipse" AND F.name=(SELECT files.name FROM files where file_id = 7) AND S.name="pluginName" and T.value = "Some Old Text" AND T.is_active = 1				
				
//print $query;


/*
$res = mysql_query($query,$dbh);
while($same_trans = mysql_fetch_array($res, MYSQL_ASSOC)){
	print "<pre>--";
	print_r($same_trans);
	print "</pre>";
}
*/
?>

<form id='translation-form'>
	<input type="hidden" name="string_id" value="<?=$line['string_id'];?>">

	<div id="english-area" class="side-component">
		<h4>English String</h4>
		<div style='margin-bottom: .5em;'>
			<b><?= nl2br($line['string_value']);?></b>
		</div>
		<h4>File From</h4>
		<div style='margin-bottom: .5em;'>
			<?= htmlspecialchars_decode(nl2br($line['name']));?>
		</div>
		<h4>Externalized Token</h4>
		<div>
		<?= htmlspecialchars_decode(nl2br($line['token']));?>
		</div>
		
	</div>
	
	<div id="translation-textarea" class="side-component">
		<h4>Current Translation</h4>
		<textarea style='display: inline; width: 320px; height: 150px;' name="translation"><?=stripslashes(($line['translation_value']));?></textarea>
		<br>
		<input type="submit" name="translateAction" value="All Versions" nClick="translationSumbit(this.form,this);">
		<input type="submit" name="translateAction" value="Only Version <?=$_SESSION['version']?>" nClick="translationSumbit(this.form,this);">
	</div>
	
	<div id="translation-history" class="side-component">
		<h4>History of Translations</h4>
		<div id="translation-history">Coming soon!</div>
	</div>
	
</form>
