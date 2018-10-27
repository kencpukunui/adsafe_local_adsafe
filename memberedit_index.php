<?php
/*
 * ADSAFE
 *
 * To add or edit the existing member records.
 *
 * @package    : local_adsafe
 * @copyright  : 2018 Pukunui
 * @author     : Ken Chang, Pukunui {@link http://pukunui.com}
 * @license    : http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
require($CFG->dirroot.'/local/adsafe/memberedit_form.php');
require($CFG->dirroot.'/local/adsafe/detect_state_from_sql.php');

// Get passed value from URLS
// when User get onto this memberedit_form.php page, we need the right location id($locid) to display the church for user
// The $locid value comes from the hyperlink of memberlist_index.php
// But If the user press the cancel button, the $locid will becomes to NULL
// So I done a tricky way to get the location id from the form ($returnlocid), when cancel the form I still got the value
$id          = optional_param('id', '', PARAM_INT);
$locid       = optional_param('locid', '', PARAM_INT); // passed from memberlist_index.php
$returnlocid = optional_param('locationid', '', PARAM_INT); // passed from this form memberedit_form.php
$passedlocid = '';
//var_dump('id = ' .$id. ' | ' . 'locid = ' .$locid . ' | ' . 'returnlocid = ' .  $returnlocid);
//echo('</br>');

// Page title
$strtitle = get_string('editornewmember', 'local_adsafe');
$url = new moodle_url('/local/adsafe/memberedit_index.php');

// Check the user's capability and define them to be a specific role string
// Is the user is a admin/manager/pastor/coordinator
$systemcontext = context_system::instance();
if ((has_capability('local/adsafe:adminmemberlistview', $systemcontext))){
    $role = get_string('admin', 'local_adsafe');
}
else if ((has_capability('local/adsafe:pastormemberlistview', $systemcontext))){
    $role = get_string('pastor', 'local_adsafe');
}
else {
    if (!$returnlocid) {
        $passedlocid = $locid;
    } else {
        $passedlocid = $returnlocid;
    }
    
    $cosql = "SELECT COUNT(*) AS CNT
              FROM {local_adsafe_coordinators} lac
              JOIN {user} u
              WHERE lac.userid = u.id
              AND lac.locationid = $passedlocid
              AND lac.id = $id";
    $iscosql = $DB->get_field_sql($cosql);
    if($iscosql > 0) {
        $role = get_string('co_ordinator', 'local_adsafe');
    }
    else {
        print_error(get_string('error_accessrestricetedt', 'local_adsafe'));
        
    }
    //print_error(get_string('error_accessrestricetedt', 'local_adsafe'));
}

// Set up PAGE Object.
$PAGE->set_url($url);
$PAGE->set_context($systemcontext);
$PAGE->set_title($strtitle);
$PAGE->set_pagelayout('admin');
$PAGE->set_heading($strtitle);


/*if (!empty($returnlocid)) {
    redirect($CFG->wwwroot."/local/adsafe/memberlist_index.php?locid=$locid");
}*/

// New a form object and pass the necessary values to the form to display it
$mform = new memberedit_form('', array('id' => $id, 'locid' => $locid, 'relocid' => $returnlocid));

// Get all values from memberedit_form.php
if ($data = $mform->get_data()) {
    if (!$data->id) { // New member
        $memberrecords = new stdClass();
        $memberrecords->locationid = $data->locationid;
        $memberrecords->userid = $data->userid;
        $memberrecords->starttime = $data->starttime;
        // when they un-ticked the checkbox of endtime, the endtime <=0
        if($data->endtime <= 0) {
            $memberrecords->endtime = null;
        } else {
           $memberrecords->endtime = $data->endtime; 
        }
        // When they un-ticked the active checkbox, the value will return NULL(empty)
        $memberrecords->activated = $data->activated;
        if(empty($memberrecords->activated)) {
            $memberrecords->activated = 0;
        } else {
            $memberrecords->activated = $data->activated;
        }
        $memberrecords->main = $data->maincheckbox;
        if(empty($memberrecords->main)) {
            $memberrecords->main = 0;
        } else {
            $memberrecords->main = $data->maincheckbox;
        }
        $ins = $DB->insert_record('local_adsafe_member', $memberrecords);
        if ($ins == 0) { //insert the record failured
            echo $OUTPUT->header();
            echo $OUTPUT->error_text(get_string('error_insert', 'local_adsafe'));
            echo $OUTPUT->continue_button(new moodle_url($CFG->wwwroot."/local/adsafe/memberedit_index.php?locid=$locid&id=$id&action=edit"));
            echo $OUTPUT->footer();
            exit();
        }
    } else { // Edit member
        $memberrecords = new stdClass();
        $memberrecords->id = $data->id;
        $memberrecords->locationid = $data->locationid;
        $memberrecords->userid = $data->userid;
        $memberrecords->starttime = $data->starttime;
        if($data->endtime <= 0) {
            $memberrecords->endtime = null;
        } else {
           $memberrecords->endtime = $data->endtime; 
        }
        $memberrecords->activated = $data->activated;
        if(empty($memberrecords->activated)) {
            $memberrecords->activated = 0;
        } else {
            $memberrecords->activated = $data->activated;
        }
        $memberrecords->main = $data->maincheckbox;
        if(empty($memberrecords->main)) {
            $memberrecords->main = 0;
        } else {
            $memberrecords->main = $data->maincheckbox;
        }
        $upd = $DB->update_record('local_adsafe_member', $memberrecords);
        if ($upd == 0) {
            echo $OUTPUT->header();
            echo $OUTPUT->error_text(get_string('error_update', 'local_adsafe'));
            echo $OUTPUT->continue_button(new moodle_url($CFG->wwwroot."/local/adsafe/memberedit_index.php?id=$returnlocid&action=edit"));
            echo $OUTPUT->footer();
            exit();
        }
    }
    redirect($CFG->wwwroot."/local/adsafe/memberlist_index.php?locid=$memberrecords->locationid");
} else if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot."/local/adsafe/memberlist_index.php?locid=$returnlocid");
}

// Output renderers.
echo $OUTPUT->header();
echo $mform->display();
echo $OUTPUT->footer();