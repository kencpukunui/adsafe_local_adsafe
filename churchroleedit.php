<?php
/*
 * ADSAFE
 *
 * Edit my roles
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
$sesskey = optional_param('sesskey', '', PARAM_TEXT);
$save    = optional_param('submitbutton', '', PARAM_ALPHA);

// Set up basic information.
$url     = new moodle_url('/local/adsafe/churchroleedit.php');
//$url     = new moodle_url('/local/adsafe/churchoreventedit.php'.'?memid='.$memid.'&action='.$action.'&sesskey='.$sesskey);
$context = context_system::instance();
$title   = get_string('myroles', 'local_adsafe');

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
$PAGE->navbar->add(get_string('selectachurchrole', 'local_adsafe'), new moodle_url('/local/adsafe/churchrolelist.php'));
$PAGE->navbar->add(get_string('vieweditmyroles', 'local_adsafe'));

//$PAGE->navbar->add($title);

$output = $PAGE->get_renderer('local_adsafe');

if(!empty($save)) {
    $editmyroleform = new \local_adsafe\forms\churchroleeditform('',array('roleid'=>$roleid));
    if ($data = $editmyroleform->get_data() and confirm_sesskey()) {
        if(\local_adsafe\utils::update_record_through_roleid($data)){
            redirect(new moodle_url("/local/adsafe/churchrolelist.php"));
            exit;
        } else {
            echo('error');
        }
    }
} else {
    $editmyroleform = new \local_adsafe\forms\churchroleeditform('',array('roleid'=>$roleid));
    if($editmyroleform->is_cancelled()) {
        redirect(new moodle_url("/local/adsafe/churchrolelist.php"));
        exit;
    }
}

echo $output->header();
echo $output->edit_my_role_list($roleid);
echo $output->footer();