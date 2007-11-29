<?php
/*******************************************************************************
 * Copyright (c) 2007 Eclipse Foundation and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Eclipse Foundation - Initial API and implementation
*******************************************************************************/
include("global.php");
InitPage("login");

global $User;
if(!$User->is_committer) {
	exitTo("error.php?errNo=3214","error: 3214 - you must be an Eclipse committer to access this page.");
}

require(BABEL_BASE_DIR . "classes/file/file.class.php");


$pageTitle 		= "Babel - Import file";
$pageKeywords 	= "import,properties,translation,language,nlpack,pack,eclipse,babel";

$FILE_ID 	= $App->getHTTPParameter("file_id");
$PROJECT_ID = $App->getHTTPParameter("project_id");
$FULLPATH 	= $App->getHTTPParameter("fullpath");
$SUBMIT 	= $App->getHTTPParameter("submit");
$strings  	= "";

if($SUBMIT == "Import") {
# Scanned document details
		if(isset($_FILES) 
			&& $_FILES['name']['size'] > 0 
			&& $_FILES['name']['size'] < 16777216) {
			
			$File = new File();
			$File->project_id = $PROJECT_ID;
			$File->name = $FULLPATH;
			if(!$File->save()) {
				$GLOBALS['g_ERRSTRS'][0] = "An error occurred while saving the file.";
			}
			else {
				# Start importing the strings!
				$fh      = fopen($_FILES['name']['tmp_name'], 'r');
				$size 	 = filesize($_FILES['name']['tmp_name']);
			
				$content = fread($fh, $size);
				fclose($fh);
			
				$filename = $_FILES['name']['name'];
				if(stristr($filename, ".properties")) {
					$strings = $File->parseProperties($content);
					$aStrings = explode(",", $strings);
					$FULLPATH = "";
				}
			}
			$filename = $_FILES['name']['name'];
			$fullpath = $App->getHTTPParameter("fullpath", "POST");
			
			if(!get_magic_quotes_gpc()){
				$filename = addslashes($filename);
			}
			$content = addslashes($content);
		}
		else {
			$GLOBALS['g_ERRSTRS'][0] = "You must specify a file to import.";
		}
}

include("head.php");

include("content/en_import.php");

include("foot.php");  

?>