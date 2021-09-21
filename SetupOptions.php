<?php

DrawHeader( ProgramTitle());




$result_RET = DBGet("SELECT * FROM parentattendance_setupoptions
				WHERE school_id = '" . UserSchool() ."'");

$possibleAbsenceCodes = DBGet("SELECT * FROM ATTENDANCE_CODES
				WHERE school_id = '" . UserSchool() ."' 
				AND SYEAR = '" . UserSYear() . "' 
				AND TABLE_NAME = 0"); 


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
</style>



</head>
<body>
  <div class="container">
    <h2>Maintenance</h2>
    <div class="success"></div>
    <div class="error"></div>

    <form>
       <table>
        <tr>
          <td colspan="4" style="text-align: center">
          	<table><tr><td>
            <input type="hidden" id ='id' value='' />
            <input type="hidden" id ='schoolid' value=' <?php echo UserSchool(); ?>' />
            <input type="hidden" id ='syear' value=' <?php echo UserSYear(); ?>' />
        </td>
        <td>Permitted Absence Codes </br>
        	<?php
        	foreach($possibleAbsenceCodes as $code){
        		echo "<input type='checkbox' name='absentCodes' 
        		id='". $code['ID'] . ":" . $code['TITLE'] . "' 
        		value='" . $code['ID'] . ":" . $code['TITLE'] . "'>" . 
        		$code['TITLE'] . "</input><br>";
        	}
        	?>
         
         </td>
         <td>

            <select id="cutoff_time" name="cutoff_time" style="width:400px;" required >
            	<option value="NO" selected>   --- Select Cuttoff Hour ---   </option>

            	<?php
            	for ($i=1; $i<=24; $i++)
            	{
            		
            		echo "<option value=" . $i . ">" . $i . "</option>";
            	}

            ?>

        	</select>
      	</td>
    	<td>
<?php
          if(! empty($result_RET)){

          	 echo "<input type='button' id='saverecords'  value ='Hide it Records' hidden /></td>";
        
        }else{

             echo "<input type='button' id='saverecords'  value ='Add Records' /></td>";

          }
        
?>
              
           </td>

           </td>
        </tr>
      </table>
    </form>
    <h2>Setup Fields</h2>
    <table>
      <tr>
        <th>#</th>
        <th>Permitted Absence Codes</th>
        <th>Cutoff Time (Current Day)</th>
        <th>Action</th>
      </tr>


<?php
  /* FetchAll foreach with edit and delete using Ajax */
 
   foreach($result_RET as $row){ ?>
  
     <tr>
       <td><?php echo $row['ID']; ?></td>
       <td><?php echo $row['PARENTABSENCE_CODES_PERMITED']; ?></td>
       <td><?php echo $row['CUTOFF_TIME']; ?></td>
       <td><a data-id = <?php echo $row['ID']; ?> class='editbtn' href= 'javascript:void(0)'>Edit</a></td>
     </tr>
   <?php }  

   ?>


  
  </table>
  </div>


  <script>
    $(function(){

    	     /* Save button ajax call */
       $('#saverecords').on( 'click', function(){
       	var codesArray =[];
       	$("input:checkbox[name=absentCodes]:checked").each(function(){
    		codesArray.push($(this).val());
			});


           var id  = $('#id').val();
           var schoolid = $('#schoolid').val();
           var syear  = $('#syear').val();
           var absentCodes = codesArray;  
           var cutoff_time = $('#cutoff_time').val();

           console.log( absentCodes + '   ' + cutoff_time);

           if(!codesArray || cutoff_time == 'NO'){
             $('.error').show(3000).html("All fields are required.").delay(3200).fadeOut(3000);
           }else{
           	 var url = 'modules/ParentAttendance/functions/setupOptions.fnc.php';
              if(id){               
                	var send_action = 'update';
 
              }else{
                	var send_action = 'add';
              }
              $.post( url, {
                	action: send_action,
                	schoolid: schoolid,
                	id: id, 
                	cutoff_time: cutoff_time, 
                	absentCodes: absentCodes, 
                	syear: syear  }
                	)
               .done(function( data ) {

                 if(data > 0){
                 		
                   $('.success').show(2000).html("Record saved successfully.").delay(2000).fadeOut(1000);
                 }else{
                 		
                   $('.error').show(2000).html("Record could not be saved. Please try again.").delay(2000).fadeOut(1000);
                 }
                 $("#saverecords").val('Add Records');
                 setTimeout(function(){
                     window.location.reload(1);
                 }, 5000);
             });
          }
       });


       $('.editbtn').on( 'click', function(){
         

          var id = $(this).data('id');
          $.post( "modules/ParentAttendance/functions/setupOptions.fnc.php", { 
          	action: 'edit',
          	id: id 
          })
            
           .done(function( product ) {
              data = $.parseJSON(product);

			//Because this is done  by record id the return can be an array(1) of arrays
              if(data){
				console.log( data[1]['PARENTABSENCE_CODES_PERMITED']);
				var absentValue = data[1]['PARENTABSENCE_CODES_PERMITED'];
				const myArr = absentValue.split("::");

				myArr.forEach(function(item, index, array) {
				  console.log(item, index)
					$("input:checkbox[id='" + item + "']").prop("checked", true);
					
				})

		        $('#id').val(data[1]['ID']);
                $('#schoolid').val(data[1]['SCHOOLID']); //title
                $('#syear').val(data[1]['SYEAR']);

                $('#cutoff_time').val(data[1]['CUTOFF_TIME']);
                 $("#saverecords").val('Save Record');
                $("#saverecords").show();
               
				
            }
          });
      });




    });
 </script>
