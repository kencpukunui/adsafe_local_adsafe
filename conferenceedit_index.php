<?php
/*
 * ADSAFE
 *
 * Conference edit index page
 *
 * @package    : local_adsafe
 * @copyright  : 2018 Pukunui
 * @author     : Ken Chang, Pukunui {@link http://pukunui.com}
 * @license    : http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
require($CFG->dirroot.'/local/adsafe/conferenceedit_form.php');
require($CFG->dirroot.'/local/adsafe/detect_state_from_sql.php');

$confid = optional_param('id', 0, PARAM_INT);

$newlocation = optional_param('newlocation','',PARAM_RAW);

$strtitle    = get_string('editornewconference', 'local_adsafe');
$systemcontext = context_system::instance();

$url = new moodle_url('/local/adsafe/conferenceedit_index.php');
if (!(has_capability('local/adsafe:conferencelistedit', $systemcontext))) {
    print_error(get_string('error_accessrestricetedt', 'local_adsafe'));
}

require_capability('local/adsafe:conferencelistedit', $systemcontext);

// Set up PAGE Object.
$PAGE->set_url($url);
$PAGE->set_context($systemcontext);
$PAGE->set_title($strtitle);
$PAGE->set_pagelayout('admin');
$PAGE->set_heading($strtitle);
if (!empty($newlocation)) {
    redirect($CFG->wwwroot."/local/adsafe/locationdetail_index.php?pid=$confid");
}

$mform = new conferenceedit_form('', array('confid' => $confid));

if ($data = $mform->get_data()) {
    if (!$data->id) {
        $confrecords = new stdClass();
        $confrecords->name = $data->name;
        $confrecords->stateid = $data->stateid;
        $confrecords->conferenceuserid = $data->conferenceuserid;

        $ins = $DB->insert_record('local_adsafe_conference', $confrecords);
        $conferenceid = $DB->get_field('local_adsafe_conference', 'id',
                array('name' => $data->name,
                      'stateid' => $data->stateid,
                      'conferenceuserid' => $data->conferenceuserid));
        if ($ins == 0) { //insert the record failured
            echo $OUTPUT->header();
            echo $OUTPUT->error_text(get_string('error_insert', 'local_adsafe'));
            echo $OUTPUT->continue_button(new moodle_url($CFG->wwwroot."/local/adsafe/conferenceedit_index.php?id=$conferenceid&action=edit"));
            echo $OUTPUT->footer();
            exit();
        }
    } else {
        $confrecords = new stdClass();
        $confrecords->id = $data->id;
        $confrecords->name = $data->name;
        $confrecords->stateid = $data->stateid;
        $confrecords->conferenceuserid = $data->conferenceuserid;
        
        $upd = $DB->update_record('local_adsafe_conference', $confrecords);
        $conferenceid = $data->id;
        if ($upd == 0) {
            echo $OUTPUT->header();
            echo $OUTPUT->error_text(get_string('error_update', 'local_adsafe'));
            echo $OUTPUT->continue_button(new moodle_url($CFG->wwwroot."/local/adsafe/conferenceedit_index.php?id=$conferenceid&action=edit"));
            echo $OUTPUT->footer();
            exit();
        }
    }
    redirect($CFG->wwwroot."/local/adsafe/conferenceedit_index.php?id=$conferenceid&action=edit");
} else if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot."/local/adsafe/conferencelist_index.php");
}

// Output renderers.
echo $OUTPUT->header();
echo $mform->display();
echo $OUTPUT->footer();