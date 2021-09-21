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


$id = trim($_POST["id"]); //record id index
$action = trim($_POST["action"]);  // identifies the action needed

//console.log('My school ' . $schoolid);
$table_settings = 'parentattendance_setupoptions';
$listCodes ='';



if($action == 'add'){


$absentCodes[]  = $_POST["absentCodes"];
$syear = trim($_POST["syear"]);
$cutoff_time = trim($_POST["cutoff_time"]);
$schoolid = trim($_POST["schoolid"]);
	

//Special case for array to string.
foreach($absentCodes as $absentCode){
	foreach($absentCode as $code){

		$listCodes .= $code . "::" ;
	}
}

	DBQuery("Insert INTO " . $table_settings . "
			(SCHOOL_ID, SYEAR, CUTOFF_TIME,PARENTABSENCE_CODES_PERMITED)
			VALUES(" . $schoolid . ",'" . $syear . "','" . $cutoff_time . "','"  . $listCodes . "')");
	
	$result=1;
}

if($action == 'edit'){



	$result = DBGET("SELECT * from " . $table_settings . " 
			  WHERE id = '" . $id . "'");

	
	error_log(print_r($result,true));
}



if($action == 'update'){
	
$absentCodes[]  = $_POST["absentCodes"];
$syear = trim($_POST["syear"]);
$cutoff_time = trim($_POST["cutoff_time"]);
$schoolid = trim($_POST["schoolid"]);
	

	//Special case for array to string.
	foreach($absentCodes as $absentCode){
		foreach($absentCode as $code){

			$listCodes .= $code . "::" ;

			error_log('a code  ' . $code . ' ====== ' . $listCodes);
		}
	}

	error_log('lists' . $listCodes);
	error_log('cutofffs  ' . $cutoff_time);
	error_log('id  ' . $id);

//,
//			  PARENTABSENCE_CODES_PERMITED = '" . $listCodes . "' 

	DBQuery("UPDATE " . $table_settings . " 
			  SET CUTOFF_TIME = '" . $cutoff_time . "',
			    PARENTABSENCE_CODES_PERMITED = '" . $listCodes . "' 
			  WHERE id = '" . $id . "'");

	$result=1;
}



if($action == 'delete'){
	 DBQuery("DELETE FROM " . $table_settings . "
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



