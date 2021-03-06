<?php
/*******************************************************************************
 * Copyright (c) 2007-2008 Eclipse Foundation and others.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the Eclipse Public License v1.0
 * which accompanies this distribution, and is available at
 * http://www.eclipse.org/legal/epl-v10.html
 *
 * Contributors:
 *    Paul Colton (Aptana)- initial API and implementation
 *    Eclipse Foundation
*******************************************************************************/
//require("global.php");
//InitPage("");

ini_set('memory_limit', '512M');

require(dirname(__FILE__) . "/../system/dbconnection.class.php");
$dbc = new DBConnection();
$dbh = $dbc->connect();

print "fetching translation to heal\n";
$query = "select translation_id,string_id,language_id,created_on,value from translations group by string_id,language_id order by created_on desc";
$res = mysqli_query($dbh, $query);

print "starting to heal the translations\n";

while($row = mysqli_fetch_assoc($res)){
	$string_id = $row['string_id'];
	$language_id = $row['language_id'];
	
	$query = "select translation_id from translations where string_id = $string_id and language_id = $language_id and is_active = 1";
	
	$looking = mysqli_query($dbh, $query);
	if(mysqli_num_rows($looking) == 0){
//		print "found 0 ".$row['translation_id']."\n";
	}elseif(mysqli_num_rows($looking) > 1){
//		print "found == ".mysqli_num_rows($looking)." --  translation_id ".$row['translation_id']." string_id ---  ".$row['string_id']."  -- date : ".$row['created_on']."\n".$row['value']."\n";
		
		$query = "select max(version) as max from translations where string_id = $string_id and language_id = $language_id ";
		$max = mysqli_fetch_assoc(mysqli_query($dbh, $query));
		$max = $max['max'];
		$query = "update translations set is_active = 0 where string_id = $string_id and language_id = $language_id and version != $max";
		mysqli_query($dbh, $query);			
		
		$query =  "update translations set is_active = 1 where string_id = $string_id and language_id = $language_id and version = $max";
		mysqli_query($dbh, $query);
	}
}

print "deleting file_progress table data\n";
//drop all the old calced file progress
$query = "delete from file_progress";
mysqli_query($dbh, $query);

print "getting all the file ids and language ids\n";
//get all the files
$query = "select file_id from files";
$res = mysqli_query($dbh, $query);
while($row = mysqli_fetch_assoc($res)){
	$file_ids[] = $row['file_id'];
}
//get all the langs
$query = "select language_id from languages";
$res = mysqli_query($dbh, $query);
while($row = mysqli_fetch_assoc($res)){
	$lang_ids[] = $row['language_id'];
}

print "cleaning up the file progress of all 0 completed!\n";
//clean up all the pct_complete == 0
$query = "delete from file_progress where pct_complete = 0";
mysqli_query($dbh, $query);

 
print "Removing all files affected by bug 233305\n";
print "This may take a while\n";

# find lowest version
$file_count = 0;
$query = "select min(file_id) as file_id, project_id, version, name from files where version='unspecified' group by project_id, version, name";
$res = mysqli_query($dbh, $query);
while($row = mysqli_fetch_assoc($res)){
	$query = "select file_id from files 
	where project_id = '" . $row['project_id'] . "' 
	and version = 'unspecified' 
	and name = '" . $row['name'] . "'
	and file_id <> " . $row['file_id'];

	$res_f = mysqli_query($dbh, $query);
	while($row_f = mysqli_fetch_assoc($res_f)){
		# find strings
		$file_count++;
		$query = "delete from translations where string_id in (select string_id from strings where file_id = '" . $row_f['file_id'] . "')";
		print $query . "... ";
		mysqli_query($dbh, $query);
		print mysqli_affected_rows($dbh) . " rows deleted\n";
		
		# delete strings
		$query = "delete from strings where file_id = '" . $row_f['file_id'] . "'";
		print $query . "... ";
		mysqli_query($dbh, $query);
		print mysqli_affected_rows($dbh) . " rows deleted\n";

		# delete strings
		$query = "delete from files where file_id = '" . $row_f['file_id'] . "'";
		print $query . "... ";
		mysqli_query($dbh, $query);
		print mysqli_affected_rows($dbh) . " rows deleted\n";
		
	}
	
}

print $file_count;
print "done!\n";

/*
 *  OLD CODE
 * 
 * foreach($found as $string_id => $v){
	foreach($v as $language_id => $langs){
		$found_active = 0;
		foreach($langs as $foo => $trans){
			if($trans['is_active'] == 1){
				$found_active++;		
			}
		}
		if(	$found_active == 0){
//			print "0 - $string_id - $language_id<br>\n";
			$query = "select max(version) as max from translations where string_id = $string_id and language_id = $language_id ";
			$max = mysqli_fetch_assoc(mysqli_query($dbh, $query));
			$max = $max['max'];
			$query = "update translations set is_active = 1 where string_id = $string_id and language_id = $language_id and version = $max";			
			print $query."\n";
//			mysqli_query($dbh, $query);			
			print mysqli_error($dbh);
			
		}elseif($found_active > 1){
			$query = "select max(version) as max from translations where string_id = $string_id and language_id = $language_id ";
			$max = mysqli_fetch_assoc(mysqli_query($dbh, $query));
			$max = $max['max'];
			$query = "update translations set is_active = 0 where string_id = $string_id and language_id = $language_id and version != $max";
			print $query."\n";
//			mysqli_query($dbh, $query);			
			print mysqli_error($dbh);
			
			$query =  "update translations set is_active = 1 where string_id = $string_id and language_id = $language_id and version = $max";
			print $query."\n";
//			mysqli_query($dbh, $query);
			print mysqli_error($dbh);
			
		}
	}
}
 */


?>