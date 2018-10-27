<?php
/*
 * ADSAFE
 *
 * View church or event
 *
 * @package    : local_adsafe
 * @copyright  : 2018 Pukunui
 * @author     : Ken Chang, Pukunui {@link http://pukunui.com}
 * @license    : http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
require_once('../../config.php');

// Get passed parameters.
$action  = optional_param('action', '', PARAM_ALPHA);
$confirm = optional_param('confirm', 0, PARAM_INT);
$memid  = optional_param('memid', 0, PARAM_INT);
$nextmyrole = optional_param('submitbutton','', PARAM_ALPHA);

// Set up basic information.
$url     = new moodle_url('/local/adsafe/churchoreventlist.php');
$context = context_system::instance();
$title   = get_string('newaccountdashchurchorevent', 'local_adsafe');

// Sanity checks.
require_login();
//require_capability('local/adsafe:churchandeventlistview', $context); //19092018 turn off by ken for test 

// Set up page.
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_heading($title);
$PAGE->set_title($title);
$PAGE->set_cacheable(false);

$output = $PAGE->get_renderer('local_adsafe');

if(!empty($nextmyrole)) {
    $churchandeventbuttonform = new \local_adsafe\forms\churchsandeventsbuttonform();
    if ($data = $churchandeventbuttonform->get_data()) {
        
       
        
        
       if($data->submitbutton) {
           
           $currenttime = time();
           
           /* $planssql = "SELECT lam.*,lac.name, x.locationname,x.cooruserid,x.firstname,x.lastname,x.username, x.emailaddress,CONCAT(u.lastname,', ',u.firstname) as newusername
                         FROM mdl_local_adsafe_member lam
                         JOIN mdl_local_adsafe_location lac
                         ON lac.id = lam.locationid
                         LEFT JOIN (SELECT lac.locationid,lal.name as locationname,lac.userid as cooruserid,u2.firstname,u2.lastname,CONCAT(u2.lastname,', ',u2.firstname) as username, u2.email as emailaddress
                         FROM mdl_local_adsafe_coordinators lac
                         JOIN mdl_local_adsafe_location lal
                         ON lal.id = lac.locationid
                         JOIN mdl_user u2
                         ON u2.id = lac.userid
                         WHERE (lac.starttime <= $currenttime
                         AND lac.endtime > $currenttime)
                         OR lac.endtime = NULL) AS x ON x.locationid = lam.locationid
                         JOIN mdl_user u
                         ON u.id = lam.userid
                         WHERE lam.userid = $USER->id
                         ORDER BY lam.main DESC,lac.name ASC"; */
            $planssql = "Select CONCAT(m.id,loc.id,co.userid) as id,
                                m.userid as memberid,
                                CONCAT(umem.lastname,', ',umem.firstname) as membername,
                                loc.id as locationid,
                                loc.name as locationname,
                                co.userid as cooruserid,
                                CONCAT(uco.lastname,', ',uco.firstname) as coordname,
                                uco.lastname as lastname,
                                uco.firstname as firstname,
                                uco.email as coordemail
                                from        mdl_local_adsafe_member m
                                Join        mdl_user as umem on umem.id = m.userid
                                Join        mdl_local_adsafe_location loc on loc.id = m.locationid
                                Left Join   mdl_local_adsafe_coordinators co
                                            on co.locationID = loc.id
                                            and co.starttime < $currenttime
                                            and (co.endtime > $currenttime or co.endtime is null)
                                Left Join   mdl_user as uco on uco.id = co.userid
                                Where       m.userid = $USER->id
                                and co.userid > 0
                                Order by co.userid";
        
        if ($getplans = $DB->get_records_sql($planssql,null)) {
           
           $length = count($getplans);
           
            $from = get_admin();
            
            foreach ($getplans as $plan) {
               
                $to = new stdClass();
                $to->id                = $plan->cooruserid;
                $to->username          = ($plan->coordname);
                $to->firstname         = $plan->firstname;
                $to->lastname          = $plan->lastname;
                $to->alternatename     = '';
                $to->middlename        = '';
                $to->firstnamephonetic = '';
                $to->lastnamephonetic  = '';
                $to->email             = $plan->emailaddress;
                $to->maildisplay       = 1;
                $to->mailformat        = 1;
                
                $emailsubject = 'New member start in the church Notification_'.date("d-m-Y H:i:s",time());

                $emailbody = 'Hi, this email is to inform you that those members below were registered them to the specific church.'."\r\n\r\n".
                         '[Information:]'."\r\n\r\n".
                         'UserID'." | ".'UserName'." | ".'LocationName'."\r\n\r\n";
                
                $emailbody .= $plan->memberid." | ".
                              $plan->locationname." | ".
                              $plan->membername."\r\n\r\n";   

                    $emailbody .= '!! Those records were generated from system, please do not reply this email, thanks !!'."\r\n\r\n".$plan->id;
                    // Send email.
                    email_to_user($to, $from, $emailsubject, $emailbody);
                    //var_dump($plan->id);
                }
        }
           redirect(new moodle_url("/local/adsafe/churchrolelist.php"));
       }
    }
} else {
    $churchandeventbuttonform = new \local_adsafe\forms\churchsandeventsbuttonform();
    if($churchandeventbuttonform->is_cancelled()) {
        redirect($CFG->wwwroot);
    }
}


// Respond to any actions.
switch ($action) {

    // Change active status?
    case 'active':
        \local_adsafe\utils::church_event_active_change_status($memid);
        redirect($url);
        break;
        
    // Change main status?
    case 'main':
        \local_adsafe\utils::church_event_main_change_status($memid);
        redirect($url);
        break;

    // Add a new church or event
    case 'add':
        //var_dump($USER);
        //echo('</br></br></br>');
        if (\local_adsafe\utils::church_event_add($USER->id)) {
            //$notifications = $output->notify_success(get_string('churcheventadded', 'local_adsafe'));
            $notifications = \core\notification::success(get_string('churcheventadded', 'local_adsafe'));
        } else {
            //$notifications = $output->notify_problem(get_string('churcheventnotadded', 'local_adsafe'));
            $notifications = \core\notification::error(get_string('churcheventnotadded', 'local_adsafe'));
            
        }
        redirect($url, $notifications);
        exit;
        break;

    // Delete a church or event
    case 'del':
        if (empty($confirm)) {
            if ($out = $output->church_event_delete_confirmation($memid)) {
                echo $output->header();
                echo $out;
                echo $output->footer();
                exit;
            } else {
                //$notifications = $output->notify_problem(get_string('unknownmemid', 'local_adsafe'));
                $notifications = \core\notification::error(get_string('unknownmemid', 'local_adsafe'));
            }
        } else {
            if (\local_adsafe\utils::church_event_delete($memid)) {
                //$notifications = $output->notify_success(get_string('churcheventdeleted', 'local_adsafe'));
                $notifications = \core\notification::success(get_string('churcheventdeleted', 'local_adsafe'));
            } else {
                //$notifications = $output->notify_problem(get_string('churcheventnotdeleted', 'local_adsafe'));
                $notifications = \core\notification::error(get_string('churcheventnotdeleted', 'local_adsafe'));
            }
        }
        break;
    default:
        $notifications = '';
}

echo $output->header();
echo $notifications;
echo $output->church_event_list($USER->id);
echo $output->footer();