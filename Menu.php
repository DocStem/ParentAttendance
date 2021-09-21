<?php
/**
 * Parent Attendance module Menu entries
 *
 * @uses $menu global var
 *
 * @see  Menu.php in root folder
 * 
 * @package RosarioSIS
 * @subpackage modules
 */

$module_name = dgettext( 'Attendance', 'Attendance' );

if ( $RosarioModules['Attendance'] )
{
    

    $menu['Attendance']['admin'] += array(
  //  'title' => _( 'Attendance' ),
    41 => _( 'Parents' ),
    'ParentAttendance/ParentAttendance.php' => _( 'Report Absences' ),
    'ParentAttendance/AbsentReasonCodes.php' => _( 'Absent Reason Codes' ),
    'ParentAttendance/SetupOptions.php' => _( 'Parent Absence Setup Options' )

);


    $menu['Attendance']['parent'] += array(
 //   'title' => _( 'Attendance' ),
    41 => _( 'Actions' ),
    'ParentAttendance/ParentAttendance.php' => _( 'Report Absences' )
);


}