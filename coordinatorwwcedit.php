<?php
/*
 * ADSAFE
 *
 * Coordinator verifiy working with children card
 *
 * @package    : local_adsafe
 * @copyright  : 2018 Pukunui
 * @author     : Ken Chang, Pukunui {@link http://pukunui.com}
 * @license    : http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
require_once('../../config.php');

$role = '';

// Get passed parameters.
$action  = optional_param('action', '', PARAM_ALPHA);
$confirm = optional_param('confirm', 0, PARAM_INT);
$memid  = optional_param('memid', 0, PARAM_INT);
$sesskey = optional_param('sesskey', '', PARAM_TEXT);


$userid = optional_param('userid', 0, PARAM_INT);
$locationid = optional_param('locationid', 0, PARAM_INT);

$spsid = optional_param('spsid', 0, PARAM_INT);
$hiddenlocationid = optional_param('hiddenlocationid', 0, PARAM_INT);
$cancel = optional_param('cancelbtn', '', PARAM_ALPHA);
$save = optional_param('savebtn', '', PARAM_ALPHA);

$hiddenveridiedid = optional_param('hiddenveridiedid',0,PARAM_INT);

$newverification = optional_param('newverification','',PARAM_ALPHA);
$wwcvid = optional_param('wwcvid',0,PARAM_INT);


// Set up basic information.
$url     = new moodle_url('/local/adsafe/coordinatorwwcedit.php');
//$url     = new moodle_url('/local/adsafe/churchoreventedit.php'.'?memid='.$memid.'&action='.$action.'&sesskey='.$sesskey);
$context = context_system::instance();
$title   = get_string('coordinatorverificationwwc', 'local_adsafe');

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


// Redirect if form was cancelled.
if (!empty($cancel)) {
    if($locationid) {
        redirect(new moodle_url('/local/adsafe/coordinatordashboardlist.php',array('locationid'=>$locationid)));
    } else {
        redirect(new moodle_url('/local/adsafe/coordinatordashboardlist.php',array('locationid'=>$hiddenlocationid)));
    }
}
if(!empty($save)) {
    if($userid && $locationid) {
        $coordinatorwwceditform = new \local_adsafe\forms\coordinatorwwceditform('',array('userid'=>$userid,'locationid'=>$locationid, 'verifieduserid'=>$USER->id));
    } else {
        $coordinatorwwceditform = new \local_adsafe\forms\coordinatorwwceditform('',array('userid'=>$spsid,'locationid'=>$hiddenlocationid, 'verifieduserid'=>$USER->id));
    }
    if ($data = $coordinatorwwceditform->get_data() and confirm_sesskey()) {
        if(\local_adsafe\utils::update_wwc_verified_record_through_wwcvid($data)){
            if($locationid) {
                redirect(new moodle_url('/local/adsafe/coordinatordashboardlist.php',array('locationid'=>$locationid)));
            } else {
                redirect(new moodle_url('/local/adsafe/coordinatordashboardlist.php',array('locationid'=>$hiddenlocationid)));
            }
            echo('Done');
        } else {
            echo('error');
        }
    }
}


if(!empty($newverification)) {
    //echo('dasdsadsdasdasds');
    
    if (empty($confirm)) {
        
        //echo($hiddenveridiedid);
        
            if ($out = $output->new_wwc_verification_record_confirmation($hiddenveridiedid)) {
                echo $output->header();
                echo $out;
                echo $output->footer();
                exit;
            } else {
                $notifications = $output->notify_problem(get_string('unknownwwcverifiedid', 'local_adsafe'));
            }
    } 
} 

if(!empty($action) && !empty($confirm)) {
    if($action == 'new' && $confirm == 1) {
        echo($wwcvid);
        if (\local_adsafe\utils::duplicate_wwc_verified_record_through_wwcvid($wwcvid)) {
            $notifications = $output->notify_success(get_string('newwwcverifiedrecordaddsuccessfully', 'local_adsafe'));
        } else {
            $notifications = $output->notify_problem(get_string('newwwcverifiedrecorderror', 'local_adsafe'));
        }
        if($userid && $locationid) {
            redirect(new moodle_url("/local/adsafe/coordinatorwwcedit.php",array('userid'=>$userid,'locationid'=>$locationid,'action'=>'edi','sesskey'=>$sesskey)));
        } else {
            redirect(new moodle_url("/local/adsafe/coordinatorwwcedit.php",array('userid'=>$spsid,'locationid'=>$hiddenlocationid,'action'=>'edi','sesskey'=>$sesskey)));
        }
        exit;
    }
}



/*else {
    if (!empty($confirm)) {
        echo($hiddenveridiedid);
        if (\local_adsafe\utils::duplicate_wwc_verified_record_through_wwcvid($hiddenveridiedid)) {
                    $notifications = $output->notify_success(get_string('newwwcverifiedrecordaddsuccessfully', 'local_adsafe'));
        } else {
            $notifications = $output->notify_problem(get_string('newwwcverifiedrecorderror', 'local_adsafe'));
        }
        if($userid && $locationid) {
            redirect(new moodle_url("/local/adsafe/coordinatorwwcedit.php",array('userid'=>$userid,'locationid'=>$locationid,'action'=>'edi','sesskey'=>$sesskey)));
        } else {
            redirect(new moodle_url("/local/adsafe/coordinatorwwcedit.php",array('userid'=>$spsid,'locationid'=>$hiddenlocationid,'action'=>'edi','sesskey'=>$sesskey)));
        }
    }
    exit;
}*/



echo $output->header();
if($userid && $locationid) {
    echo $output->verify_working_with_children_card_list($userid,$locationid,$USER->id);
} else {
    echo $output->verify_working_with_children_card_list($spsid,$hiddenlocationid,$USER->id);
}


echo $output->footer();