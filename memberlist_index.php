<?php
/*
 * ADSAFE
 *
 * To display member list.
 *
 * @package    : local_adsafe
 * @copyright  : 2018 Pukunui
 * @author     : Ken Chang, Pukunui {@link http://pukunui.com}
 * @license    : http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require($CFG->dirroot.'/local/adsafe/memberlist_form.php');
require($CFG->dirroot.'/local/adsafe/detect_state_from_sql.php');
//require($CFG->dirroot.'/local/tellcent/lib.php'); // system will block same function name of this file, so I just set the path temporary

// Get passed value from URLS (ex: if URLS contained a string id=7, then $id will get a interger value 7)
$id              = optional_param('id', '', PARAM_INT);
$action          = optional_param('action', '', PARAM_RAW);
$locationid      = optional_param('locationid', '', PARAM_INT);
$locid           = optional_param('locid', '', PARAM_INT);
$newmember       = optional_param('newmember','',PARAM_RAW);
$coordinatorpage = optional_param('coordinatorpage','',PARAM_RAW);

// Page title
$strtitle  = get_string('member', 'local_adsafe');
$url = new moodle_url('/local/adsafe/memberlist_index.php');

// Define parameters
$userid = $USER->id;
$accessform = 0;
$role ='';

// Check the user's capability and define them to be a specific role string
// Is the user is a admin/manager/pastor/coordinator
$systemcontext = context_system::instance();
if ((has_capability('local/adsafe:adminmemberlistview', $systemcontext))) {
    $role = get_string('admin', 'local_adsafe');
}
else if ((has_capability('local/adsafe:pastormemberlistview', $systemcontext))) {
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

// Set up PAGE Object.
$PAGE->set_url($url);
$PAGE->set_context($systemcontext);
$PAGE->set_title($strtitle);
$PAGE->set_pagelayout('admin');
$PAGE->set_heading($strtitle);

//Check this user is a existing pastor or coordinator in database and let them see the right form
if ($role == 'Pastor' || $role == 'Co-ordinator') {
    $checkpastorsql = "SELECT COUNT(*) AS CNT
                       FROM
                       (SELECT u.id, CONCAT( u.lastname,', ', u.firstname) as Pastorname
                       FROM {context} con                         
                       JOIN {role_assignments} ra 
                       ON con.id = ra.contextid AND con.contextlevel = 10                      
                       JOIN {role} r 
                       ON ra.roleid = r.id                      
                       JOIN {user} u 
                       ON u.id = ra.userid                      
                       WHERE r.id = 11) AS pas
                       JOIN {local_adsafe_location} lal 
                       ON lal.pastoruserid = pas.id
                       WHERE pas.id = $userid
                       AND (UNIX_TIMESTAMP() >= lal.starttime) 
                       AND ((UNIX_TIMESTAMP() <= (lal.endtime + 86400)) OR (lal.endtime IS NULL))";
    $ispastor = $DB->get_field_sql($checkpastorsql);
    if ($ispastor > 0) {
        $accessform = 1;
    }
    else {
        $checkcosql = "SELECT COUNT(*) AS CNT
                       FROM {local_adsafe_coordinators} lac
                       WHERE lac.userid = $userid
                       AND (UNIX_TIMESTAMP() >= lac.starttime) 
                       AND ((UNIX_TIMESTAMP() <= (lac.endtime + 86400)) OR (lac.endtime IS NULL))";
        $iscoordinator = $DB->get_field_sql($checkcosql);
        if ($iscoordinator > 0) {
            $accessform = 1;
        }
        else {
           $accessform = 0;
           print_error(get_string('error_accessrestricetedt', 'local_adsafe'));
           
        }
    }
    if ($accessform > 0) {
        $mform = new memberlist_form('', array('id' => $userid, 'role' => $role));
    }
}
else if ($role == 'admin') { //admin user can see all location
    $mform = new memberlist_form('', array('id' => $userid, 'role' => $role));
}

// if press the new member button
if (!empty($newmember)) {
    redirect($CFG->wwwroot."/local/adsafe/memberedit_index.php?locid=$locationid");
}
// if press the Co-ordinator button
if (!empty($coordinatorpage)) {
    redirect($CFG->wwwroot."/local/adsafe/coordinatorlist_index.php?locid=$locationid");
}

// if press the Delete hyperlink in the Action field
if (!strcmp($action, get_string('sdelete', 'local_adsafe'))) {
    $getmemberrecords = $DB->count_records('local_adsafe_member', array('id' => $id));
    if ($getmemberrecords <= 0) { // this one won't happen
        echo $OUTPUT->header();
        echo $OUTPUT->error_text(get_string('error_nonrecordsmember', 'local_adsafe'));
        echo $OUTPUT->continue_button(new moodle_url($CFG->wwwroot."/local/adsafe/memberlist_index.php"));
        echo $OUTPUT->footer();
        exit();
    }
    else {
        // Continue button link
        $linkyes = "$CFG->wwwroot/local/adsafe/memberlist_index.php?locid=$locid&id=$id&action=confirm";
        // Cancel button link
        $linkno  = "$CFG->wwwroot/local/adsafe/memberlist_index.php?locid=$locid";
        //pop-up window
        echo $OUTPUT->header();
        echo $OUTPUT->confirm(get_string('memberconfirmdeletion', 'local_adsafe'), $linkyes, $linkno);
        echo $OUTPUT->footer();
        exit();
    }
} else if (!strcmp($action, get_string('confirm', 'local_adsafe'))) {
    // Check is this user is an active user
    $membersql = "SELECT lam.*
                  FROM mdl_local_adsafe_member lam
                  WHERE lam.locationid = $locid
                  AND lam.id = $id";
    
    $getmemberfields = $DB->get_record_sql($membersql,null);
    if (($getmemberfields -> activated) > 0) { // Can not be deleted because the user still got active condition
        echo $OUTPUT->header();
        echo $OUTPUT->error_text(get_string('warning_deleting_activated_member', 'local_adsafe'));
        echo $OUTPUT->continue_button(new moodle_url($CFG->wwwroot."/local/adsafe/memberlist_index.php?locid=$locid")); //haven't create this page yet 20062018
        echo $OUTPUT->footer();
        exit();
    } else {
        // Is this user in the same location of coordinator table
        $coorsql = "SELECT lac.*
                    FROM mdl_local_adsafe_coordinators lac
                    WHERE userid = 
                    (SELECT lam.userid
                     FROM mdl_local_adsafe_member lam
                     WHERE lam.locationid = $locid
                     AND lam.id = $id)
                     AND lac.locationid = $locid";
        $getcorrecord = $DB->get_record_sql($coorsql);

        if($getcorrecord) { // Can not be deleted
            echo $OUTPUT->header();
            echo $OUTPUT->error_text(get_string('warning_delete_location_member_coordinators', 'local_adsafe'));
            echo $OUTPUT->continue_button(new moodle_url($CFG->wwwroot."/local/adsafe/memberlist_index.php?locid=$locid"));
            echo $OUTPUT->footer();
            exit();
        } else {
            $del = $DB->delete_records('local_adsafe_member', array('id' => $id));
            if ($del == 0) {
                echo $OUTPUT->header();
                echo $OUTPUT->error_text(get_string('error_nonrecordsmember', 'local_adsafe'));
                echo $OUTPUT->continue_button(new moodle_url($CFG->wwwroot."/local/adsafe/memberlist_index.php?locid=$locid"));
                echo $OUTPUT->footer();
                exit();
            }
            redirect($CFG->wwwroot."/local/adsafe/memberlist_index.php?locid=$locid");
        }
    }
    //redirect($CFG->wwwroot."/local/adsafe/memberlist_index.php?locid=$locid");
}
// user has choosen the location and press search button
if ($data = $mform->get_data()) {
    redirect($CFG->wwwroot."/local/adsafe/memberlist_index.php?locid=$locationid");
}

// Output renderers.
echo $OUTPUT->header();
echo $mform->display();
echo $OUTPUT->footer();