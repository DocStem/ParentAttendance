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
$table_settings = 'parentattendance_selfreported';




if($action == 'add'){


$syear = trim($_POST["syear"]);
$school_id = trim($_POST["schoolid"]);
$student_id = trim($_POST["student_id"]);
$parent_id = trim($_POST["parent_id"]);
$absenceDate = trim($_POST["absenceDate"]);
$absenceType = trim($_POST["absenceType"]);
$absenceReason = trim($_POST["absenceReason"]);
$explaination = filter_var(trim($_POST["explaination"]),FILTER_SANITIZE_STRING) ;
$explaination = RemoveSpecialChar($explaination);



//We can only record an insert if the record does not exist... Check and see if it exists.

$doesExist = DBGet("Select * 
					from " . $table_settings . "
					WHERE SCHOOL_ID = '" . $school_id . "'
					AND SYEAR = '" . $syear . "'
					AND STUDENT_ID = '" . $student_id . "'
					AND SCHOOL_DATE = '" . $absenceDate . "'");


	if(! Empty($doesExist)){
		// put a different result number to modify the message
		$result=61;
		
	}else{
		DBQuery("Insert INTO " . $table_settings . "
			(SCHOOL_ID, SYEAR, STUDENT_ID, PARENT_REPORTING, SCHOOL_DATE, ABSENT_TYPE, ABSENT_REASON, ABSENCE_NOTE)
			VALUES('" . $school_id . "','" . $syear . "','" . $student_id . "','" . $parent_id . "','". $absenceDate . "','". $absenceType . "','" . $absenceReason . "','"  . $explaination . "')");

		/* start by seeing if we can do a SELECT */

		$attendanceClasses = DBGET("SELECT s.COURSE_PERIOD_ID,s.MARKING_PERIOD_ID, cp.TEACHER_ID, cpsp.PERIOD_ID 
					FROM 
					schedule s,
					course_periods cp,
					course_period_school_periods cpsp
					where s.syear = '" . $syear . "'
					and s.student_id = '" . $student_id . "'
					and s.school_id = '" . $school_id . "'
					and s.course_period_id = cp.course_period_id
					AND s.course_period_id = cpsp.course_period_id
					and does_attendance is Not Null");

	

		/*Now we have to shove this into Attendance Period.
		You need the student_id, School_date, period_id, attendance_code (absenceType),
		attendace_teacher_code, attendance_reason(Parent: plus parent id + absenceReason),
		course_period_id,marking_period, */


		DBQuery("INSERT INTO attendance_period(
			student_id, school_date, period_id, attendance_code, attendance_teacher_code, attendance_reason, course_period_id, marking_period_id)
		VALUES (" . $student_id . ",'" . $absenceDate . "',". $attendanceClasses[1]['PERIOD_ID'] . "," . $absenceType . "," . $absenceType . ",'Parent Reported: " . $absenceReason . "'," .  $attendanceClasses[1]['COURSE_PERIOD_ID'] . "," .$attendanceClasses[1]['MARKING_PERIOD_ID'] .")"
		);

/*To finish this correctly, you need to  insert  into attendance_period
Need period_id course_period_id marking_period_id -- maybe even attendance_teacher_code

// Set date.
$date = RequestedDate( 'date', DBDate(), 'set' );
$qtr_id = GetCurrentMP( 'QTR', $date, false );
COURSE_PERIOD_ID='" . UserCoursePeriod()
STAFF_ID='" . User( 'STAFF_ID' ) 


*/
	$result=1;
		
	}		
}

//===== EDIT

if($action == 'edit'){

	$result = DBGET("SELECT * from " . $table_settings . " 
			  WHERE id = '" . $id . "'");

}

//=============================  similar to add.

if($action == 'update'){
	

$syear = trim($_POST["syear"]);
$school_id = trim($_POST["schoolid"]);
$student_id = trim($_POST["student_id"]);
$parent_id = trim($_POST["parent_id"]);
$absenceDate = trim($_POST["absenceDate"]);
$absenceType = trim($_POST["absenceType"]);
$absenceReason = trim($_POST["absenceReason"]);
$explaination = filter_var(trim($_POST["explaination"]),FILTER_SANITIZE_STRING) ;
$explaination = RemoveSpecialChar($explaination);
	


	DBQuery("UPDATE " . $table_settings . " 
			  SET SCHOOL_ID = '" . $school_id  . "',
			    SYEAR = '" . $syear . "', 
			    STUDENT_ID = '" . $student_id . "',
			    PARENT_REPORTING = '" . $parent_id . "',
			    SCHOOL_DATE = '" . $absenceDate . "',
			    ABSENT_TYPE = '" . $absenceType . "',
			    ABSENT_REASON = '" . $absenceReason . "',
			    ABSENCE_NOTE = '" . $explaination . "'
			  WHERE id = '" . $id . "'");


	/*Now you have to update the record in attendance_period

DBQuery("INSERT INTO attendance_period(
			student_id, school_date, period_id, attendance_code, attendance_teacher_code, attendance_reason, course_period_id, marking_period_id)
		VALUES (" . $student_id . ",'" . $absenceDate . "',". $attendanceClasses[1]['PERIOD_ID'] . "," . $absenceType . "," . $parent_id . ",'Parent Reported: " . $absenceReason . "'," .  $attendanceClasses[1]['COURSE_PERIOD_ID'] . "," .$attendanceClasses[1]['MARKING_PERIOD_ID'] .")"
		);
		*/

		DBQuery("Update attendance_period
			SET attendance_code = '" . $absenceType ."',
			attendance_teacher_code = '" . $absenceType ."',
			attendance_reason = 'Parent Reported: " . $absenceReason ."'
			WHERE student_id = '" . $student_id ."' 
			AND school_date = '" . $absenceDate . "'");

	$result=1;
}



if($action == 'delete'){
	 
	$currentRecord = DBGet("Select * from " . $table_settings . "
			 WHERE id = '" . $id . "'");


	 DBQuery("DELETE FROM " . $table_settings . "
	 			WHERE id = '" . $id . "'"
	 		);

	 /*You have to delete from the attendance_period table to clear out for the teacher
	 Which means to find the record we need the student ID and the absenceDate and the
	 school_id */

	 error_log('got id ?  ' . $currentRecord[1]['STUDENT_ID']);

	 DBQuery("Delete FROM attendance_period
	 where student_id = '" . $currentRecord[1]['STUDENT_ID'] ."'
	 AND  school_date = '" . $currentRecord[1]['SCHOOL_DATE'] . "'"
	);



	 $result=1;
}


//Return results below 

if($action == 'edit'){
	 echo json_encode($result);
}else{
	echo $result;
}


//==============================================  functions

// Function to remove the spacial 
function RemoveSpecialChar($str) {
      
    // Using str_replace() function 
    // to replace the word 
    $res = str_replace( array( '\'', '"',
    ',' , ';', '<', '>', 'INSERT ', 'DELETE ', 'SELECT ' ), ' ', $str);
      
    // Returning the result 
    return $res;
    }
