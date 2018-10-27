<?php
/*
 * ADSAFE
 *
 * Co-ordinator role edit
 *
 * @package    : local_adsafe
 * @copyright  : 2018 Pukunui
 * @author     : Ken Chang, Pukunui {@link http://pukunui.com}
 * @license    : http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
require_once('../../config.php');

// Define parameters
$role ='';
$notifications ='';

// Get passed parameters.
$action  = optional_param('action', '', PARAM_ALPHA);
$roleindex = optional_param('roleindex', '', PARAM_TEXT);
$confirm = optional_param('confirm', 0, PARAM_INT);
$sesskey = optional_param('sesskey', '', PARAM_TEXT);
$locationid = optional_param('locationid', 0, PARAM_INT);
$returnlocationid = optional_param('hiddenlocationid', 0, PARAM_INT);
$returnroleindex = optional_param('hiddenroleid', 0, PARAM_INT);
$saveordel = optional_param('saveordelbtn', '', PARAM_ALPHA);
$cancel = optional_param('cancelbtn', '', PARAM_ALPHA);


/*
echo('------------------------------</br>');
echo($returnroleindex);
echo('</br>------------------------------</br>');
echo('------------------------------</br>');
echo($returnlocationid);
echo('</br>------------------------------</br>');
echo($roleindex);
echo('</br>');
echo($action);
echo('</br>');
echo($confirm);
echo('</br>');
echo($sesskey);
echo('</br>');
echo($USER->id);
echo('</br>');
echo($locationid);*/

// Set up basic information.
$url     = new moodle_url('/local/adsafe/coordinatorroleedit.php');
//$url     = new moodle_url('/local/adsafe/churchoreventedit.php'.'?memid='.$memid.'&action='.$action.'&sesskey='.$sesskey);
$context = context_system::instance();
$title   = get_string('membersroleconfirmation', 'local_adsafe');

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

if(!empty($cancel) && $cancel == 'Cancel') {
    if($locationid) {
        redirect(new moodle_url("/local/adsafe/coordinatordashboardlist.php",array('locationid'=>$locationid)));
    } else {
        redirect(new moodle_url("/local/adsafe/coordinatordashboardlist.php",array('locationid'=>$returnlocationid)));
    }
    exit;
}
if(!empty($saveordel)) {
    if($locationid && $roleindex) {
       $coordinatorroleeditform = new \local_adsafe\forms\coordinatorroleeditform('',array('roleindex'=>$roleindex,'locationid'=>$locationid,'userid'=>$USER->id)); 
    } else {
       $coordinatorroleeditform = new \local_adsafe\forms\coordinatorroleeditform('',array('roleindex'=>$returnroleindex,'locationid'=>$returnlocationid,'userid'=>$USER->id)); 
    }
    if ($data = $coordinatorroleeditform->get_data() and confirm_sesskey()) {
        if ($saveordel == 'Save') {
            if($update = \local_adsafe\utils::update_member_role_record_through_roleid($data)){
                if($locationid) {
                    redirect(new moodle_url("/local/adsafe/coordinatordashboardlist.php",array('locationid'=>$locationid))); 
                } else {
                    redirect(new moodle_url("/local/adsafe/coordinatordashboardlist.php",array('locationid'=>$returnlocationid))); 
                }
                
                exit;
            }
        } else {
            //redirect(new moodle_url('/local/evtp/planview.php', array('id'=>$planid)),get_string('addednewregistrarplan', 'local_evtp'));
            redirect(new moodle_url('/local/adsafe/coordinatorroleedit.php', array('roleindex'=>$data->hiddenroleid, 'userid'=>$data->hiddenmemberid, 'locationid'=>$data->hiddenlocationid,'action'=>'del')));
            //redirect(new moodle_url("/local/adsafe/coordinatorroleedit.php?roleindex=".$data->hiddenroleid."&userid=".$data->hiddenmemberid."&locationid=".$data->hiddenlocationid."&action=del"));
            exit;
        }
    }
}


switch($action) {
    case 'del':
        if (empty($confirm)) {
            if ($out = $output->member_role_delete_confirmation($roleindex)) {
                echo $output->header();
                echo $out;
                echo $output->footer();
                exit;
            } else {
                //$notifications = $output->notify_problem(get_string('unknownmemid', 'local_adsafe'));
                $notifications = \core\notification::error(get_string('unknownmemid', 'local_adsafe'));
            }
        } else {
            if (\local_adsafe\utils::member_role_delete_through_roleid($roleindex)) {
                //$notifications = $output->notify_success(get_string('memberroledeleted', 'local_adsafe'));
                $notifications = \core\notification::success(get_string('memberroledeleted', 'local_adsafe'));
            } else {
                //$notifications = $output->notify_problem(get_string('memberrolenotdeleted', 'local_adsafe'));
                $notifications = \core\notification::error(get_string('memberrolenotdeleted', 'local_adsafe'));
            }
            redirect(new moodle_url("/local/adsafe/coordinatordashboardlist.php"));
            exit;
        }
    break;
}


if($roleindex) {
    $locationname = \local_adsafe\utils::get_location_name_from_locationid($locationid);
    if($roleindex == 'NO ROLE INDEX') {
        $notifications = $output->notification(get_string('userdoesnthaveroleofthelocation', 'local_adsafe',$locationname));
        redirect(new moodle_url('/local/adsafe/coordinatordashboardlist.php'), $notifications);
        exit;
    } else {
        //$notifications = $output->notify_success(get_string('youareeditingaroleinthelocation', 'local_adsafe',$locationname));
        $notifications = \core\notification::success(get_string('youareeditingaroleinthelocation', 'local_adsafe',$locationname));
    }
}

echo $output->header();
echo $notifications;
if($roleindex) {
   echo $output->view_coordinator_role_page($roleindex,$locationid,$USER->id); 
} else {
   echo $output->view_coordinator_role_page($returnroleindex,$returnlocationid, $USER->id);
}

echo $output->footer();