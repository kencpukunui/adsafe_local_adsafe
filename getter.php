<?php 
require_once("../../config.php");
global $DB;

// Get the parameter
$conid = optional_param('conid',  0,  PARAM_INT);



// If departmentid exists
if($conid && $conid > 0) {
    
    $churcheventmenu = \local_adsafe\utils::get_regarding_church_event_from_conferenceid($conid);
    //echo "<option value='0'>Select ...</option>";
    foreach ($churcheventmenu as $churchevent) {
        echo "<option value=".$churchevent->id.">" . $churchevent->name . "</option>"; 
    }
    
    
    //echo "<option value='0'>".$churcheventmenu."</option>";
    /*// Do your query 
    $query = 'SELECT * FROM {table_without_prefix} WHERE departmentid = ' . $departmentid;
    $student_arr = $DB->get_records_sql($query, null,  $limitfrom=0,  $limitnum=0);

    // echo your results, loop the array of objects and echo each one
    echo "<option value='0'>All Students</option>";
    foreach ($student_arr as $student) {
        echo "<option value=".$student->id.">" . $student->fullname . "</option>";  
    }*/

}
