<?php
/*
 * ADSAFE
 *
 * View / Add church role
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
$roleid  = optional_param('roleid', 0, PARAM_INT);
$savemyrole = optional_param('savemyrole', '', PARAM_ALPHA);
$wwcbutton = optional_param('submitbutton', '', PARAM_ALPHA);

// Set up basic information.
$url     = new moodle_url('/local/adsafe/churchrolelist.php');
$context = context_system::instance();
$title   = get_string('selectachurchrole', 'local_adsafe');

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
$PAGE->navbar->add(get_string('churchoreventnav', 'local_adsafe'));
$PAGE->navbar->add(get_string('newaccount', 'local_adsafe'), new moodle_url('/local/adsafe/churchoreventlist.php'));
$PAGE->navbar->add($title);


/*$PAGE->navbar->add(get_string('templateplanlist', 'local_evtp'), new moodle_url('/local/evtp/templateplanlist.php'));
$PAGE->navbar->add($title);*/

$output = $PAGE->get_renderer('local_adsafe');

if(!empty($wwcbutton)) {
    if($wwcbutton == 'NextWWC') {
        redirect(new moodle_url("/local/adsafe/workingwithchildrencard.php"));
    } else if ($wwcbutton == 'Save my role') {
        redirect(new moodle_url("/local/adsafe/churchrolelist.php"));
    }
} else {
    $churchrolebuttonform = new \local_adsafe\forms\churchrolebuttonform();
    if($churchrolebuttonform->is_cancelled()) {
        redirect(new moodle_url("/local/adsafe/churchoreventlist.php"));
    }
}

// Respond to any actions.
switch ($action) {

    // Change active status?
    case 'active':
        \local_adsafe\utils::my_role_active_change_status($roleid);
        redirect($url);
        break;
        
    // Add a new plan?
    case 'add':
        if (\local_adsafe\utils::church_role_add($USER->id)) {
            //$notifications = $output->notify_success(get_string('myroleadded', 'local_adsafe'));
            $notifications = \core\notification::success(get_string('myroleadded', 'local_adsafe'));
        } else {
            //$notifications = $output->notify_problem(get_string('myrolenotadded', 'local_adsafe'));
            $notifications = \core\notification::error(get_string('myrolenotadded', 'local_adsafe'));
        }
        redirect($url, $notifications);
        exit;
        break;

    // Delete a plan?
    case 'del':
        if (empty($confirm)) {
            if ($out = $output->my_role_delete_confirmation($roleid)) {
                echo $output->header();
                echo $out;
                echo $output->footer();
                exit;
            } else {
                //$notifications = $output->notify_problem(get_string('unknownroleid', 'local_adsafe'));
                $notifications = \core\notification::error(get_string('unknownroleid', 'local_adsafe'));
            }
        } else {
            if (\local_adsafe\utils::my_roles_delete($roleid)) {
                //$notifications = $output->notify_success(get_string('myroledeleted', 'local_adsafe'));
                $notifications = \core\notification::success(get_string('myroledeleted', 'local_adsafe'));
            } else {
                //$notifications = $output->notify_problem(get_string('myrolenotdeleted', 'local_adsafe'));
                $notifications = \core\notification::error(get_string('myrolenotdeleted', 'local_adsafe'));
            }
        }
        break;
        
    default:
        $notifications = '';
}

echo $output->header();
echo $notifications;
echo $output->church_role_list($USER->id);
echo $output->footer();