<?php
/*
 * ADSAFE
 *
 * To add new location detail and edit the existing location detail records.
 *
 * @package    : local_adsafe
 * @copyright  : 2018 Pukunui
 * @author     : Ken Chang, Pukunui {@link http://pukunui.com}
 * @license    : http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
require($CFG->dirroot.'/local/adsafe/locationdetail_form.php');
require($CFG->dirroot.'/local/adsafe/detect_state_from_sql.php');
//require($CFG->dirroot.'/local/tellcent/lib.php'); // system will block same function name of this file, so I just set the path temporary

$locationid = optional_param('id', '', PARAM_INT);
$confid     = optional_param('pid', '', PARAM_INT);

$acid       = optional_param('acid', '', PARAM_INT);
$mode       = optional_param('mode', '', PARAM_RAW);

$action     = optional_param('action', '', PARAM_RAW);


$courselist = optional_param('courselist', '', PARAM_RAW);


$strtitle   = get_string('editornewlocation', 'local_adsafe');
$systemcontext = context_system::instance();
$url = new moodle_url('/local/adsafe/locationdetail_index.php');

if (!(has_capability('local/adsafe:conferencelistview', $systemcontext))) {
    print_error(get_string('error_accessrestricetedt', 'local_adsafe'));
}

require_capability('local/adsafe:conferencelistview', $systemcontext);

// Set up PAGE Object.
$PAGE->set_url($url);
$PAGE->set_context($systemcontext);
$PAGE->set_title($strtitle);
$PAGE->set_pagelayout('admin');
$PAGE->set_heading($strtitle);

if (!strcmp($action, get_string('sdelete', 'local_adsafe'))) {
    
    $locidfrommember = $DB->count_records('local_adsafe_member', array('locationid' => $locationid));
    $locidfromcoordinator = $DB->count_records('local_adsafe_coordinators', array('locationid' => $locationid));
    if (($locidfrommember > 0) || ($locidfromcoordinator > 0)) {
        echo $OUTPUT->header();
        echo $OUTPUT->error_text(get_string('warning_delete_location_member_coordinators', 'local_adsafe'));
        echo $OUTPUT->continue_button(new moodle_url($CFG->wwwroot."/local/adsafe/conferenceedit_index.php?id=$confid&action=edit")); //haven't create this page yet 20062018
        echo $OUTPUT->footer();
        exit(); 
    } else {
        $linkyes = "$CFG->wwwroot/local/adsafe/locationdetail_index.php?id=$locationid&pid=$confid&action=confirm";
        $linkno  = "$CFG->wwwroot/local/adsafe/conferenceedit_index.php?id=$confid&action=edit";
        echo $OUTPUT->header();
        echo $OUTPUT->confirm(get_string('locationconfirmdeletion', 'local_adsafe'), $linkyes, $linkno);
        echo $OUTPUT->footer();
        exit();
    }
} else if (!strcmp($action, get_string('confirm', 'local_adsafe'))) {
    $DB->delete_records('local_adsafe_location', array('id' => $locationid , 'conferenceid' => $confid));
    redirect($CFG->wwwroot."/local/adsafe/conferenceedit_index.php?id=$confid&action=edit");
}


if (empty($confid)) {
    $confid = optional_param('confid', '', PARAM_INT);
}

$mform = new locationdetail_form('', array('locationid' => $locationid, 'confid' => $confid));

if ($data = $mform->get_data()) {
    if (empty($data->locationid)) {
    
        $actlocation = new stdClass();
        $actlocation->id              = $data->locationid;
        $actlocation->name            = $data->locationname;
        $actlocation->address         = $data->address;
        $actlocation->pastoruserid    = $data->pastoruserid;
        $actlocation->conferenceid    = $confid;
        $actlocation->starttime       = $data->starttime;
        $actlocation->endtime         = $data->endtime;
        
        $ins = $DB->insert_record('local_adsafe_location', $actlocation);

        if ($ins == 0) {
            echo $OUTPUT->header();
            echo $OUTPUT->error_text(get_string('error_insert', 'local_adsafe'));
            echo $OUTPUT->continue_button(new moodle_url($CFG->wwwroot."/local/adsafe/conferenceedit_index.php?id=$data->confid"));
            echo $OUTPUT->footer();
            exit();
        }
        
       
    } else {
        $actlocation = new stdClass();
        $actlocation->id              = $data->locationid;
        $actlocation->name            = $data->locationname;
        $actlocation->address         = $data->address;
        $actlocation->pastoruserid    = $data->pastoruserid;
        $actlocation->starttime       = $data->starttime;
        $actlocation->endtime         = $data->endtime;
        
        $upd = $DB->update_record('local_adsafe_location', $actlocation);

        if ($upd == 0) {
            echo $OUTPUT->header();
            echo $OUTPUT->error_text(get_string('error_update', 'local_adsafe'));
            echo $OUTPUT->continue_button(new moodle_url($CFG->wwwroot."/local/adsafe/conferenceedit_index.php?id=$data->confid"));
            echo $OUTPUT->footer();
            exit();
        }
    }
    redirect($CFG->wwwroot."/local/adsafe/conferenceedit_index.php?id=$data->confid&action=edit");
} else if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot."/local/adsafe/conferenceedit_index.php?id=$confid&action=edit");

}

// Output renderers.
echo $OUTPUT->header();
echo $mform->display();
echo $OUTPUT->footer();