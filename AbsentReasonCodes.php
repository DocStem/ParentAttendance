<?php

DrawHeader( ProgramTitle());


$result = DBGet("SELECT * FROM parentattendance_reasoncodes");

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
            <input type="hidden" id ='prod_id' value='' />
            <input type="hidden" id ='schoolid' value=' <?php echo UserSchool(); ?>' />
            <input type='text' id='product_name' placeholder='Reason Code' required />&nbsp;&nbsp;
          <input type='text' id='price' placeholder='Shortname' required />&nbsp;&nbsp;
          <input type='text' id='category' placeholder='Report Category' required />&nbsp;&nbsp;
           <input type='button' id='saverecords'  value ='Add Records' />

        </tr>
      </table>
    </form>
    <h2>Reason Codes</h2>
    <table>
      <tr>
        <th>#</th>
        <th>Reason Code</th>
        <th>Shortname</th>
        <th>Report Category</th>
        <th>Action</th>
      </tr>


  <?php
  /* FetchAll foreach with edit and delete using Ajax */
 
   foreach($result as $row){ ?>
  
     <tr>
       <td><?php echo $row['ID']; ?></td>
       <td><?php echo $row['TITLE']; ?></td>
       <td><?php echo $row['SHORT_NAME']; ?></td>
       <td><?php echo $row['TYPE']; ?></td>
       <td><a data-pid = <?php echo $row['ID']; ?> class='editbtn' href= 'javascript:void(0)'>Edit</a>&nbsp;|&nbsp;<a class='delbtn' data-pid=<?php echo $row['ID']; ?> href='javascript:void(0)'>Delete</a></td>
     </tr>
   <?php }  

   ?>


  
  </table>
  </div>
  
  <script>
    $(function(){

      /* Delete button ajax call */
      $('.delbtn').on( 'click', function(){
        if(confirm('This action will delete this record. Are you sure?')){
          var pid = $(this).data('pid');
          $.post( "modules/ParentAttendance/functions/ajaxDatabase.fnc.php", { 
          	action: 'delete',
          	id: pid }
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
      

          var pid = $(this).data('pid');
          $.post( "modules/ParentAttendance/functions/ajaxDatabase.fnc.php", { 
          	action: 'edit',
          	id: pid 
          })
            
           .done(function( product ) {
              data = $.parseJSON(product);

			//Because this is done  by record id the return can be an array(1) of arrays
              if(data){


                $('#prod_id').val(data[1]['ID']);
                $('#product_name').val(data[1]['TITLE']); //title
                $('#price').val(data[1]['SHORT_NAME']);
                $('#category').val(data[1]['TYPE']);
                $('schoolid').val(data[1]['SCHOOL_ID'])
                $("#saverecords").val('Save Record');

            }
          });
      });

      /* Save button ajax call */
       $('#saverecords').on( 'click', function(){
           var prod_id  = $('#prod_id').val();
           var product = $('#product_name').val();
           var price   = $('#price').val();
           var category = $('#category').val();
           var schoolid = $('#schoolid').val();

           if(!product || !price || !category){
             $('.error').show(3000).html("All fields are required.").delay(3200).fadeOut(3000);
           }else{
           	 var url = 'modules/ParentAttendance/functions/ajaxDatabase.fnc.php';
              if(prod_id){               
                	var send_action = 'update';
 
              }else{
                	var send_action = 'add';
              }
              $.post( url, {
                	action: send_action,
                	schoolid: schoolid,
                	id: prod_id, 
                	product: product, 
                	category: category, 
                	price: price  }
                	)
               .done(function( data ) {

                 if(data > 0){
                 		
                   $('.success').show(2000).html("Record saved successfully.").delay(2000).fadeOut(1000);
                 }else{
                 		
                   $('.error').show(2000).html("Record could not be saved. Please try again.").delay(2000).fadeOut(1000);
                 }
                 $("#saverecords").val('Add Record');
                 setTimeout(function(){
                     window.location.reload(1);
                 }, 5000);
             });
          }
       });
    });
 </script>
</body>
</html>