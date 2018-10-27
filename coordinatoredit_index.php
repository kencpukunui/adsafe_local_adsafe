<?php
/*
 * ADSAFE
 *
 * To add / edit the coordinator records.
 *
 * @package    : local_adsafe
 * @copyright  : 2018 Pukunui
 * @author     : Ken Chang, Pukunui {@link http://pukunui.com}
 * @license    : http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
require($CFG->dirroot.'/local/adsafe/coordinatoredit_form.php');
require($CFG->dirroot.'/local/adsafe/detect_state_from_sql.php');
//require($CFG->dirroot.'/local/tellcent/lib.php'); //need this library for temporary


$id = optional_param('id', '', PARAM_INT);
$locid = optional_param('locid', '', PARAM_INT);
$returnlocid = optional_param('locationid', '', PARAM_INT);
$userid = $USER->id;

//var_dump('id = ' .$id. ' | ' . 'locid = ' .$locid . ' | ' . 'returnlocid = ' .  $returnlocid);
//echo('</br>');

$strtitle    = get_string('editornewcoordinator', 'local_adsafe');
$systemcontext = context_system::instance();
$url = new moodle_url('/local/adsafe/coordinatorlist_index.php');

if ((has_capability('local/adsafe:adminmemberlistview', $systemcontext))){
    $role = get_string('admin', 'local_adsafe');
}
else if ((has_capability('local/adsafe:pastormemberlistview', $systemcontext))){
    $role = get_string('pastor', 'local_adsafe');
}
else {
    $cosql = "SELECT COUNT(*) AS CNT
              FROM {local_adsafe_coordinators} lac
              WHERE lac.userid = $userid
              AND (UNIX_TIMESTAMP() >= lac.starttime) 
              AND ((UNIX_TIMESTAMP() <= (lac.endtime + 86400)) OR (lac.endtime IS NULL))";
    $iscosql = $DB->get_field_sql($cosql);
    if($iscosql > 0) {
        $role = get_string('co_ordinator', 'local_adsafe');
    }
    else {
        print_error(get_string('error_accessrestricetedt', 'local_adsafe'));
    }
}
/*else {
    print_error(get_string('error_accessrestricetedt', 'local_adsafe'));
}*/


// Set up PAGE Object.
$PAGE->set_url($url);
$PAGE->set_context($systemcontext);
$PAGE->set_title($strtitle);
$PAGE->set_pagelayout('admin');
$PAGE->set_heading($strtitle);


/*if (!empty($returnlocid)) {
    redirect($CFG->wwwroot."/local/adsafe/memberlist_index.php?locid=$locid");
}*/

$mform = new coordinatoredit_form('', array('id' => $id, 'locid' => $locid, 'relocid' => $returnlocid));

if ($data = $mform->get_data()) {
    if (!$data->id) {
        
        $coordinatorrecords = new stdClass();
        $coordinatorrecords->userid = $data->userid;
        $coordinatorrecords->locationid = $data->locationid;
        $coordinatorrecords->starttime = $data->starttime;
        //$coordinatorrecords->endtime = $data->endtime;
        
        if($data->endtime <= 0) {
            $coordinatorrecords->endtime = null;
        }
        else {
           $coordinatorrecords->endtime = $data->endtime; 
        }


        $ins = $DB->insert_record('local_adsafe_coordinators', $coordinatorrecords);
        if ($ins == 0) { //insert the record failured
            echo $OUTPUT->header();
            echo $OUTPUT->error_text(get_string('error_insert', 'local_adsafe'));
            echo $OUTPUT->continue_button(new moodle_url($CFG->wwwroot."/local/adsafe/coordinatoredit_index.php?locid=$locid&id=$id&action=edit"));
            echo $OUTPUT->footer();
            exit();
        }
    } else {
        $coordinatorrecords = new stdClass();
        $coordinatorrecords->id = $data->id;
        $coordinatorrecords->userid = $data->userid;
        $coordinatorrecords->locationid = $data->locationid;
        $coordinatorrecords->starttime = $data->starttime;
        //$coordinatorrecords->endtime = $data->endtime;
        if($data->endtime <= 0) {
            $coordinatorrecords->endtime = null;
        }
        else {
           $coordinatorrecords->endtime = $data->endtime; 
        }

        $upd = $DB->update_record('local_adsafe_coordinators', $coordinatorrecords);
        $locid = $data->id;
        if ($upd == 0) {
            echo $OUTPUT->header();
            echo $OUTPUT->error_text(get_string('error_update', 'local_adsafe'));
            echo $OUTPUT->continue_button(new moodle_url($CFG->wwwroot."/local/adsafe/coordinatoredit_index.php?id=$returnlocid&action=edit"));
            echo $OUTPUT->footer();
            exit();
        }
        
    }
    redirect($CFG->wwwroot."/local/adsafe/coordinatorlist_index.php?locid=$returnlocid");
} else if ($mform->is_cancelled()) {
    
    redirect($CFG->wwwroot."/local/adsafe/coordinatorlist_index.php?locid=$returnlocid");
}

// Output renderers.
echo $OUTPUT->header();
echo $mform->display();
echo $OUTPUT->footer();