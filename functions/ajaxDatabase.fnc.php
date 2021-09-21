<?php
if ( ! include_once '../../../config.inc.php' )
{
	die( 'config.inc.php file not found. Please read the installation directions.' );
}

require_once '../../../database.inc.php';

$functions = glob( '../../../functions/*.php' );

foreach ( $functions as $function )
{
	require_once $function;
}
//============================  Code below / Setup Above ====================

$title  = trim($_POST["product"]);
$shortname = trim($_POST["price"]);
$category = trim($_POST["category"]);
$schoolid = trim($_POST["schoolid"]);
$id = trim($_POST["id"]); //record id index
$action = trim($_POST["action"]);  // identifies the action needed

//console.log('My school ' . $schoolid);

$result = 4;
if($action == 'add'){
	DBQuery("Insert INTO PARENTATTENDANCE_REASONCODES 
			(SCHOOL_ID, TITLE,SHORT_NAME,TYPE)
			VALUES(" . $schoolid . ",'" . $title . "','" . $shortname . "','" . $category . "')");
$result = 5;
	
	//$result=1;
}

if($action == 'edit'){
	
	$result = DBGET("SELECT * from PARENTATTENDANCE_REASONCODES 
			  WHERE id = '" . $id . "'");
}

if($action == 'update'){
	


	DBQuery("UPDATE PARENTATTENDANCE_REASONCODES 
			  SET TITLE = '" . $title . "',
			  SHORT_NAME = '" . $shortname . "',
			  TYPE = '" . $category . "' 
			  WHERE id = '" . $id . "'");

	$result=1;
}



if($action == 'delete'){
	 DBQuery("DELETE FROM PARENTATTENDANCE_REASONCODES
	 			WHERE id = '" . $id . "'"
	 		);
	 $result=1;
}


//Return results below 

if($action == 'edit'){
	 echo json_encode($result);
}else{
	echo $result;
}



