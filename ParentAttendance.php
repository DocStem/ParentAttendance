<?php

//NEED a BIG FAT IF to say if your not a parent this wont work for you.

if(User( 'PROFILE' ) === 'parent'){
	//This is a parent ONLY Module.....
$students_RET = DBGet( "SELECT sju.STUDENT_ID,
				" . DisplayNameSQL( 's' ) . " AS FULL_NAME,se.SCHOOL_ID
				FROM STUDENTS s,STUDENTS_JOIN_USERS sju,STUDENT_ENROLLMENT se,SCHOOLS sch
				WHERE s.STUDENT_ID=sju.STUDENT_ID
				AND sju.STAFF_ID='" . User( 'STAFF_ID' ) . "'
				AND se.SYEAR='" . UserSyear() . "'
				AND se.STUDENT_ID=sju.STUDENT_ID
				AND se.STUDENT_ID ='" . UserStudentID() . "'
				AND sch.ID=se.SCHOOL_ID
				AND sch.SYEAR=se.SYEAR
				AND ('" . DBDate() . "'>=se.START_DATE
					AND ('" . DBDate() . "'<=se.END_DATE
						OR se.END_DATE IS NULL ) )" );

	


DrawHeader( ProgramTitle() . ' -- ' . $students_RET[1]['FULL_NAME']);

$time ='';
$time_last = '';



$futureAbsence = DBGet("SELECT * FROM parentattendance_selfreported
					Where STUDENT_ID ='" . UserStudentID() . "'
					ORDER BY SCHOOL_DATE ASC");

?>

<head>
<style>
.container{
  margin: 20px auto;
}
h2 {
  text-align: center;
}
table {
    font-family: arial, sans-serif;
    border-collapse: collapse;
    width: 100%;
}

td, th {
    border: 1px solid #dddddd;
    text-align: left;
    padding: 8px;
}

tr:nth-child(even) {
    background-color: #dddddd;
}

body{
  font-family:Arial, Helvetica, sans-serif;
  font-size:13px;
}
.success, .error{
  border: 1px solid;
  margin: 10px 0px;
  padding:15px 10px 15px 50px;
  background-repeat: no-repeat;
  background-position: 10px center;
}

.success {
  color: #4F8A10;
  background-color: #DFF2BF;
  background-image:url('modules/ParentAttendance/includes/success.png');
  display: none;
}
.error {
  display: none;
  color: #D8000C;
  background-color: #FFBABA;
  background-image: url('modules/ParentAttendance/includes/error.png');
}

caption{
	color: red;
}
</style>

<?php
// Get all the one time use data needed out of the way.





//Make the times for month year selections -- 
getMonthYearSelectionRanges($time,$time_last);

$startMonth = date( "Y-m-d", $time );
$lastMonth = date( "Y-m-d", $time_last );



// Get pre-defined reasons
$reasons_RET = getReasonCodes();
//Get the setup options that permit what parents can do to be enforced This includes the Absent Types
$setupOptions_RET = getSetupOptions();
$Prettycutoff_time = prettyTime($setupOptions_RET[1]['CUTOFF_TIME']); // Used in the message and there can only be one cutoff
$cutoff_time = $setupOptions_RET[1]['CUTOFF_TIME']; // needed to determine if we show todays date

//Get school calendar possible attendance dates
$calendarDays_RET = attendanceReportingDates($startMonth,$lastMonth,$cutoff_time);



/* a bunch of tesing info to get the reight data on student attendance class */

$date = RequestedDate( 'date', DBDate(), 'set' );
$qtr_id = GetCurrentMP( 'QTR', $date, false );

//echo ("Got a marking Period ID " . $qtr_id);

//I have to take the student id and then run through schedule and find the class for this year that takes attendance 

?>
</head>
<body>
  <div class="container">

<table dir="ltr" width="60%" border="1" 
			summary="absence">
	<caption><b>Definition of Attendance Codes for State Reporting* <small> See footnotes </small></b>
	</caption>
	<colgroup width="50%" />
	<colgroup id="colgroup" class="colgroup" align="center" 
			valign="middle" title="title" width="1*" 
			span="3" style="background:#ddd;" />
	<thead>
		<tr>
			<th scope="col">Attendance Code</th>
			<th scope="col">State Definition</th>
		</tr>
	</thead>
	
	<tbody>
		<tr>
			<td>Absent Full Day</td>
			<td>Not in school or leave school before 9 AM</td>
		</tr>
		<tr>
			<td>Absent AM</td>
			<td>Arrive at school after 10:30 AM</td>
		</tr>
		<tr>
			<td>Absent PM</td>
			<td>Leave school at 12 PM or before</td>
		</tr>
	</tbody>
</table>
<p style="text-align:center">1- Students require a Doctors Note for 3+ consecutive school days of absence.</br> 2- Planned travel absence requires documented itinerary of educational travel with prior approval.</br>
3 - * This form is not for reporting new COVID Information. You must contact the office directly. *

<h4 style="text-align:center">4 - Do NOT Include any requests for homework sent home, or other on this form. You must contact the teacher via email for such requests.</h4></p>


<p><b><h4 style="text-align:center">Current day absence should be reported by <?php echo $Prettycutoff_time  ?> with this tool or by phone call. Phoned in absences still require a parent note upon student return.</br>

	This tool does not allow current day absence input after <?php echo $Prettycutoff_time  ?>.</h4></b>



<?php


echo '<form action="' . URLEscape( 'Modules.php?modname=' . $_REQUEST['modname'] . '&month=' . $_REQUEST['month'] . '&year=' . $_REQUEST['year']  ) . '" method="POST">';




DrawHeader(
	'Change for Future Month Reporting -->'.
		PrepareDate(
			mb_strtoupper( date( "d-M-y", $time ) ),
			'',
			false,
			array(
				'M' => 1,
				'Y' => 1,
				'submit' => true,
			)
		)
	);


?>




    <h2>Add An Absence</h2>
    <div class="success"></div>
    <div class="error"></div>
    <form>
       <table>
        <tr>
          <td colspan="4" style="text-align: center">
            <input type="hidden" id ='id' value='' />
            <input type="hidden" id ='schoolid' value=' <?php echo UserSchool(); ?>' />
            <input type="hidden" id ='syear' value=' <?php echo UserSYear(); ?>' />
            <input type="hidden" id ='student_id' value=' <?php echo UserStudentID(); ?>' />
            <input type="hidden" id ='parent_id' value=' <?php echo UserStaffID(); ?>' />
<?php

//=======================    date
		$absenceDateSelect = '<select id="absenceDate" name="absenceDate" style="width:150px;" >';
		$absenceDateSelect .= '<option value="NO" selected>--- Absence Date ---</option>';
		foreach($calendarDays_RET as $date){
			$absenceDateSelect .= '<option value=' . $date['SCHOOL_DATE'] . '>' . $date['SCHOOL_DATE'] . '</option>';
				
		}
		$absenceDateSelect .= '</select>';
		echo $absenceDateSelect;

//=================================== Type of absence
		$absenceTypeSelect = '<select id="absenceType" name="absenceType" style="width:300px;" >';
		$absenceTypeSelect .= '<option value="NO" selected>Type of Absence/Dismissal</option>';
		
		/* Cannot keep the setup options absence types in the incoming record format. we need 
		to explode them */
		$permittedCodes = explode('::', $setupOptions_RET[1]['PARENTABSENCE_CODES_PERMITED']);

		

		foreach($permittedCodes as $reasons){
			
			$reason = explode(':', $reasons);

			//echo('<pre>' . print_r($reason,true) . '</pre>');
			if($reason[0] != NULL){
				$absenceTypeSelect .= '<option value=' . $reason[0] . '>' . $reason[1] . '</option>';
			
			}
			
			
		}
		$absenceTypeSelect .= '</select>';
		echo $absenceTypeSelect;

//======================================  Reason
		$reasonsSelect = '<select id="absenceReason" name="absenceReason" style="width:300px;" >';
		$reasonsSelect .= '<option value="NO" selected>   Reason For Absence   </option>';
		
		foreach($reasons_RET as $reason){
			$reasonsSelect .= '<option value="' . $reason['TITLE'] . '">' . $reason['TITLE'] . '</option>';
		}
		$reasonsSelect .= '</select>';
		echo $reasonsSelect;
?>
          
<textarea id="explaination" name="explaination" placeholder="Parent Explaination" rows="4" cols="50"></textarea>
   
            <input type='button' id='saverecords'  value ='Add Absence' />

        </tr>
      </table>
    </form>
    <h2>Future Planned Absence</h2>



    <table>
      <tr>
        <th>#</th>
        <th>Absence Date</th>
        <th>Absence Type</th>
        <th>Reason</th>
        <th>Absence Note</th>
        <th>Action</th>
      </tr>


<?php
  /* FetchAll foreach with edit and delete using Ajax */
 
   foreach($futureAbsence as $row){ 
  	/* Absence Type needs to be converted to Verbiage
  	Wont hurt because EDIT populates based on ID */

  	//Go look it up
  	$txtABSENT_TYPE = DBGet("SELECT TITLE
  						FROM attendance_codes
  						WHERE ID = '" . $row['ABSENT_TYPE'] ."'"
  					);

   	
?>
     <tr>
       <td><?php echo $row['ID']; ?></td>
       <td><?php echo $row['SCHOOL_DATE']; ?></td>
       <td><?php echo $txtABSENT_TYPE[1]['TITLE']; ?></td>
       <td><?php echo $row['ABSENT_REASON']; ?></td>
       <td><?php echo $row['ABSENCE_NOTE']; ?></td>
       <td><a data-id = <?php echo $row['ID']; ?> class='editbtn' href= 'javascript:void(0)'>Edit</a>&nbsp;|&nbsp;<a class='delbtn' data-id=<?php echo $row['ID']; ?> href='javascript:void(0)'>Delete</a></td>
     </tr>
   <?php }  

   ?>


  
  </table>
  </div>
  
 



<p><i>
	
</br></br><b>Disclosure: PA STATE LAW</b></br>
Pennsylvania law broadly defines absences as excused when a student is prevented from attendance for mental, physical, or other urgent reasons. An absence is lawful when a student is dismissed during school hours by a certified school nurse, registered nurse, licensed practical nurse or a school administrator or designee or if the student is absent to obtain professional health care or therapy care service rendered by a licensed practitioner in the healing arts. Additionally, schools and nonpublic schools should consider illness, family emergency, death of a family member, medical or dental appointments, authorized school activities, and documented itinerary educational travel with prior approval as lawful absences. An absence that requires a student to leave school for the purposes of attending court hearings related to their involvement with a county children and youth agency or juvenile probation may not be categorized as unlawful.</br></br>

The purpose of this Basic Education Circular (BEC) is to provide an overview of the compulsory attendance and truancy laws in Pennsylvania, as amended via Act 138 of 2016 (Act 138), Act 39 of 2018 (Act 39), and Act 16 of 2019 (Act 16). For more information regarding PA Truancy and Attendance Mandatory reporting, see the PED website.</br>
</i>
</p>

<?php

echo ('</form>');
//echo('<pre>' . print_r($calendarDays_RET,true) . '</pre>');
} //PARENT IF is OVER.....
else{
	echo('<h2>This module is a Parent only Module. Use Adminstrator Take Absence or Teacher Absent Forms');
}

//Pretty up the hours into a nice time format

function prettyTime($hours) {
    if ($hours < 12) {
       $return = $hours . " AM ";
    }else{
    	$hours = $hours - 12;
    	$return = $hours . " PM ";
    }
   
    return $return;
}


/*These are the pre-defined Reasons for absence available for parents

*/

function getSetupOptions(){

	$setupOptions_RET = DBGet( "SELECT * 
				FROM parentattendance_setupoptions
				WHERE SYEAR = '" . UserSyear() . "'
				AND SCHOOL_ID = '" . UserSchool() . "'"
			);

	return $setupOptions_RET;
}


/*These are the pre-defined Reasons for absence available for parents

*/

function getReasonCodes(){

	$reasons_RET = DBGet( "SELECT * 
				FROM parentattendance_reasoncodes
				WHERE SCHOOL_ID = '" . UserSchool() . "'
				Order By title ASC"
			);

	return $reasons_RET;
}

/*makes the form available for the future dates absences can be reported for
-- Takes into account hours before current date cutoff.
-- Historical dates are not available here but can be seen in the reporting area.
the return is all future dates
*/

function attendanceReportingDates($startDay,$lastDayOfMonth,$cutoff_time){


/*Compare the month value to see if the first day of attendance available needs to be today and/or later

*/
	if(date('m') == $_REQUEST['month']){
		//change the startDay
		$startDay = date("Y-m-d");

		//Now check the time. If after cutoff, the first day is tomorrow
		$currentHour = date('H');
		if($currentHour > $cutoff_time ){
			$startDay = date('Y-m-d', strtotime($startDay . ' +1 day'));
			//echo ' This start date is ====== ' . $startDay;
		}
	}

$calendarDays_RET = DBGet( "SELECT * 
					FROM attendance_calendar
					WHERE SYEAR = '" . UserSyear() . "'
					AND SCHOOL_ID = '" . UserSchool() . "'
					AND SCHOOL_DATE BETWEEN '" . $startDay . "' AND '" . $lastDayOfMonth . "'
					ORDER BY school_date ASC, calendar_id ASC");


	return $calendarDays_RET;
}



/* this creates the Month and Year Selection for the Menu dates
multiple variables passed by reference and updated.
*/
function getMonthYearSelectionRanges(&$time,&$time_last){
    if ( empty( $_REQUEST['month'] ) )
        {
            $_REQUEST['month'] = date( 'm' );
        }

        if ( empty( $_REQUEST['year'] ) )
        {
            $_REQUEST['year'] = date( 'Y' );
        }

        $last = 31;
         
        while ( ! checkdate( $_REQUEST['month'], $last, $_REQUEST['year'] ) )
        {
            $last--;
        }

        $time = mktime( 0, 0, 0, $_REQUEST['month'], 1, $_REQUEST['year'] );
        $time_last = mktime( 0, 0, 0, $_REQUEST['month'], $last, $_REQUEST['year'] );


}

?>

 <script>
    $(function(){

      /* Delete button ajax call */
      $('.delbtn').on( 'click', function(){
        if(confirm('This action will delete this record. Are you sure?')){
          var id = $(this).data('id');
          $.post( "modules/ParentAttendance/functions/ajaxRecordParentAbsence.fnc.php", { 
          	action: 'delete',
          	id: id }
          	)
          .done(function( data ) {
            if(data > 0){
              $('.success').show(3000).html("Record deleted successfully.").delay(3200).fadeOut(6000);
            }else{
              $('.error').show(3000).html("Record could not be deleted. Please try again.").delay(3200).fadeOut(6000);;
            }
            setTimeout(function(){
                window.location.reload(1);
            }, 5000);
          });
        }
      });

     /* Edit button ajax call */
      $('.editbtn').on( 'click', function(){
      

          var id = $(this).data('id');

          //console.log('The id is   ' + id);
          $.post( "modules/ParentAttendance/functions/ajaxRecordParentAbsence.fnc.php", { 
          	action: 'edit',
          	id: id 
          })
            
           .done(function( data ) {
              data = $.parseJSON(data);

            //  console.log(data);

			//Because this is done  by record id the return can be an array(1) of arrays
              if(data){

                $('#id').val(data[1]['ID']);
                $('#schoolid').val(data[1]['SCHOOL_ID']); //title
                $('#syear').val(data[1]['SYEAR']);
                $('#student_id').val(data[1]['STUDENT_ID']);
                $('parent_id').val(data[1]['PARENT_REPORTING']);
                $('#absenceDate').val(data[1]['SCHOOL_DATE']);
           		$('#absenceType').val(data[1]['ABSENT_TYPE']);
          		$('#absenceReason').val(data[1]['ABSENT_REASON']);
          		$('#explaination').val(data[1]['ABSENCE_NOTE']);
                $("#saverecords").val('Save Record');

            }
          });
      });

      /* Save button ajax call */
       $('#saverecords').on( 'click', function(){
           var id  = $('#id').val();
           var schoolid = $('#schoolid').val();
           var syear   = $('#syear').val();
           var student_id = $('#student_id').val();
           var parent_id = $('#parent_id').val();
           var absenceDate = $('#absenceDate').val();
           var absenceType = $('#absenceType').val();
           var absenceReason = $('#absenceReason').val();
           var explaination = $('#explaination').val();

           if(absenceDate == 'NO' || absenceType == 'NO' || absenceReason == 'NO' || !explaination){
           	
             $('.error').show(3000).html("All fields are required.").delay(3200).fadeOut(3000);
           }else{
           	 var url = 'modules/ParentAttendance/functions/ajaxRecordParentAbsence.fnc.php';
              if(id){               
                	var send_action = 'update';
 
              }else{
                	var send_action = 'add';
                		
              }
              $.post( url, {
                	action: send_action,
                	schoolid: schoolid,
                	id: id, 
                	syear: syear,
                	student_id: student_id,
                	parent_id: parent_id,
                	absenceDate: absenceDate,
                	absenceType: absenceType,
                	absenceReason: absenceReason,
                	explaination: explaination
                	 }
                	)
               .done(function( data ) {
               

                 if(data <= 1){
                   $('.success').show(4000).html("Absence Recorded.").delay(2000).fadeOut(1000);
                 }else if(data == 61){
                 	$('.error').show(4000).html("That absence has already been reported. Please Edit it if you wish to make a change.").delay(2000).fadeOut(1000);
                 }

                 else{	
                   $('.error').show(2000).html("Absence could not be saved. Please try again.").delay(2000).fadeOut(1000);
                 }

                 $("#saverecords").val('Add Record');
                 setTimeout(function(){
                     window.location.reload(1);
                 }, 8000);
             });
          }
       });
    });
 </script>
</body>
</html>	