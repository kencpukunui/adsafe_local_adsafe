<?php
/*
 * ADSAFE
 *
 * Conference list index page.
 *
 * @package    : local_adsafe
 * @copyright  : 2018 Pukunui
 * @author     : Ken Chang, Pukunui {@link http://pukunui.com}
 * @license    : http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
require($CFG->dirroot.'/local/adsafe/conferencelist_form.php');
require($CFG->dirroot.'/local/adsafe/detect_state_from_sql.php');
//require($CFG->dirroot.'/local/tellcent/lib.php'); // system will block same function name of this file, so I just set the path temporary

$conferenceid = optional_param('id', '', PARAM_INT);
$action    = optional_param('action', '', PARAM_RAW);
$strtitle  = get_string('conference', 'local_adsafe');

$systemcontext = context_system::instance();
$url = new moodle_url('/local/adsafe/conferencelist_index.php');
if ((has_capability('local/adsafe:conferencelistview', $systemcontext))) {
    $role = get_string('admin', 'local_adsafe');
} else {
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
    $getlocationrecords = $DB->count_records('local_adsafe_location', array('conferenceid' => $conferenceid));
    if($getlocationrecords > 0){
        echo $OUTPUT->header();
        echo $OUTPUT->error_text(get_string('warning_delete_location', 'local_adsafe'));
        echo $OUTPUT->continue_button(new moodle_url($CFG->wwwroot."/local/adsafe/conferencelist_index.php")); //haven't create this page yet 20062018
        echo $OUTPUT->footer();
        exit();
        //$dellocate = $DB->delete_records('local_adsafe_location', array('conferenceid' => $conferenceid));
        //error
    }
    else {
        $linkyes = "$CFG->wwwroot/local/adsafe/conferencelist_index.php?id=$conferenceid&action=confirm";
        $linkno  = "$CFG->wwwroot/local/adsafe/conferencelist_index.php";
        echo $OUTPUT->header();
        echo $OUTPUT->confirm(get_string('conferconfirmdeletion', 'local_adsafe'), $linkyes, $linkno);
        echo $OUTPUT->footer();
        exit();
    }
} else if (!strcmp($action, get_string('confirm', 'local_adsafe'))) {
    $del = $DB->delete_records('local_adsafe_conference', array('id' => $conferenceid));
    if ($del == 0) {
        echo $OUTPUT->header();
        echo $OUTPUT->error_text(get_string('warning_delete_conference', 'local_adsafe'));
        echo $OUTPUT->continue_button(new moodle_url($CFG->wwwroot."/local/adsafe/conferencelist_index.php")); //haven't create this page yet 20062018
        echo $OUTPUT->footer();
        exit();
    }
}

$mform = new conferencelist_form();

if ($data = $mform->get_data()) {
    redirect($CFG->wwwroot."/local/adsafe/conferenceedit_index.php");
}
// Output renderers.
echo $OUTPUT->header();
echo $mform->display();
echo $OUTPUT->footer();