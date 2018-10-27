<?php
/*
 * ADSAFE
 *
 * Co-ordinator Memeber edit
 *
 * @package    : local_adsafe
 * @copyright  : 2018 Pukunui
 * @author     : Ken Chang, Pukunui {@link http://pukunui.com}
 * @license    : http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
require_once('../../config.php');

// Define parameters
$role ='';

// Get passed parameters.
$action  = optional_param('action', '', PARAM_ALPHA);
$roleid = optional_param('roleid', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);
$memid  = optional_param('memid', 0, PARAM_INT);
$sesskey = optional_param('sesskey', '', PARAM_TEXT);
$locationid = optional_param('hiddenlocationid', 0, PARAM_INT);
$returnmemid = optional_param('hiddenmemid', '', PARAM_INT);
$save = optional_param('submitbutton', '', PARAM_ALPHA);


/*
echo($returnmemid);
echo('</br>');
echo($memid);
echo('</br>');
echo($roleid);
echo('</br>');
echo($action);
echo('</br>');
echo($confirm);
echo('</br>');
echo($sesskey);
echo('</br>');
echo($USER->id);
*/

// Set up basic information.
$url     = new moodle_url('/local/adsafe/coordinatormemberedit.php');
//$url     = new moodle_url('/local/adsafe/churchoreventedit.php'.'?memid='.$memid.'&action='.$action.'&sesskey='.$sesskey);
$context = context_system::instance();
$title   = get_string('coordinatormember', 'local_adsafe');

// Sanity checks.
require_login();
//require_capability('local/adsafe:churchandeventlistview', $context); //19092018 turn off by ken for test 

// Check the user's capability and define them to be a specific role string
// Is the user is a admin/manager/pastor/coordinator

if ((has_capability('local/adsafe:adminmemberlistview', $context))) {
    $role = get_string('admin', 'local_adsafe');
} else if ((has_capability('local/adsafe:pastormemberlistview', $context))) {
    $role = get_string('pastor', 'local_adsafe');
} else {
    if($iscoordinator = \local_adsafe\utils::check_coordinator_user_through_userid($USER->id)){
        if ($iscoordinator > 0) {
            $role = get_string('co_ordinator', 'local_adsafe');
        } else {
            print_error(get_string('error_accessrestricetedt', 'local_adsafe'));
        }
    } else {
       print_error(get_string('error_accessrestricetedt', 'local_adsafe'));
    }
}

// Set up page.
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_heading($title);
$PAGE->set_title($title);
$PAGE->set_cacheable(false);
//$PAGE->navbar->add(get_string('churchoreventnav', 'local_adsafe'));
//$PAGE->navbar->add(get_string('newaccount', 'local_adsafe'), new moodle_url('/local/adsafe/churchoreventlist.php'));
//$PAGE->navbar->add($title);

$output = $PAGE->get_renderer('local_adsafe');

if(!empty($save)) {
    if($memid) {
        $coordinatormembereditform = new \local_adsafe\forms\coordinatormembereditform('',array('memid'=>$memid,'userid'=>$USER->id));
    } else {
        $coordinatormembereditform = new \local_adsafe\forms\coordinatormembereditform('',array('memid'=>$returnmemid,'userid'=>$USER->id));
    }
    if ($data = $coordinatormembereditform->get_data() and confirm_sesskey()) {
        if(\local_adsafe\utils::update_member_record_through_memid($data)){
            redirect(new moodle_url("/local/adsafe/coordinatordashboardlist.php",array('locationid'=>$data->hiddenlocationid)));
            exit;
        } else {
            //echo('error');
        }
    }
} else {
    if($memid) {
        $coordinatormembereditform = new \local_adsafe\forms\coordinatormembereditform('',array('memid'=>$memid,'userid'=>$USER->id));
    } else {
        $coordinatormembereditform = new \local_adsafe\forms\coordinatormembereditform('',array('memid'=>$returnmemid,'userid'=>$USER->id));
    }
    if($coordinatormembereditform->is_cancelled()) {
        redirect(new moodle_url("/local/adsafe/coordinatordashboardlist.php",array('locationid'=>$locationid)));
        exit;
    }
}



echo $output->header();
if($memid) {
   echo $output->view_coordinator_confirmed_page($memid, $USER->id,$locationid); 
} else {
   echo $output->view_coordinator_confirmed_page($returnmemid, $USER->id,$locationid);
}

echo $output->footer();