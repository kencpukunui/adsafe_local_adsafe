<?php
/*
 * ADSAFE
 *
 * Add working with children card
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
$memid   = optional_param('memid', 0, PARAM_INT);
$sesskey = optional_param('sesskey', '', PARAM_TEXT);
$save    = optional_param('submitbutton', '', PARAM_ALPHA);

$spsuserid = optional_param('spsuserid',0,PARAM_INT);
$wwcindicator = optional_param('wwcindicator',0,PARAM_INT);
$state = optional_param('state',0,PARAM_INT);
$nameoncard = optional_param('nameoncard','',PARAM_TEXT);
$dateofbirth = optional_param('dateofbirth',0,PARAM_INT);
$cardnumber = optional_param('cardnumber', '', PARAM_TEXT);
$expirydate = optional_param('expirydate', 0, PARAM_INT);
$timecreated = optional_param('timecreated', 0, PARAM_INT);


/*var_dump($spsuserid);
var_dump($wwcindicator);
var_dump($state);
var_dump($nameoncard);
var_dump($dateofbirth);
var_dump($cardnumber);
var_dump($expirydate);
var_dump($timecreated);
var_dump($action);*/

// Set up basic information.
$url     = new moodle_url('/local/adsafe/workingwithchildrencard.php');
//$url     = new moodle_url('/local/adsafe/churchoreventedit.php'.'?memid='.$memid.'&action='.$action.'&sesskey='.$sesskey);
$context = context_system::instance();
$title   = get_string('workingwithchildrencard', 'local_adsafe');

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
//$PAGE->navbar->add(get_string('churchoreventnav', 'local_adsafe'));
//$PAGE->navbar->add(get_string('newaccount', 'local_adsafe'), new moodle_url('/local/adsafe/churchoreventlist.php'));
//$PAGE->navbar->add($title);

$output = $PAGE->get_renderer('local_adsafe');


if($action == 'tsave'){
    
    $newdata = new \stdClass;
    $newdata->spsuserid    = $spsuserid;
    $newdata->wwcindicator = $wwcindicator;
    $newdata->state        = $state;
    $newdata->nameoncard   = $nameoncard;
    $newdata->dateofbirth  = $dateofbirth;
    $newdata->cardnumber   = $cardnumber;
    $newdata->expirydate   = $expirydate;
    
    if(\local_adsafe\utils::update_record_through_userid($newdata)){
        redirect($CFG->wwwroot);
        exit;
    } else {
        redirect($CFG->wwwroot);
        exit;
    }
}



if(!empty($save)) {
    $workingwithchildrencardform = new \local_adsafe\forms\workingwithchildrencardform('',array('userid'=>$USER->id));
    if ($data = $workingwithchildrencardform->get_data()) {
        
        //var_dump($data);
        
        if($data->wwcindicator > 0) {
            if (empty($confirm)) {
                if ($out = $output->wwc_verification_save_confirmation($USER->id,$data)) {
                    echo $output->header();
                    echo $out;
                    echo $output->footer();
                    exit;
                } else {
                    //$notifications = $output->notify_problem(get_string('unknownmemid', 'local_adsafe'));
                    //$notifications = \core\notification::error(get_string('unknownmemid', 'local_adsafe'));
                    
                    if(\local_adsafe\utils::update_record_through_userid($data)){
                        
                        //redirect(new moodle_url("/local/adsafe/workingwithchildrencard.php"));
                        redirect($CFG->wwwroot);
                        exit;
                    } else {
                        redirect($CFG->wwwroot);
                        exit;
                    }
                    
                    
                }
            }
        }
        if($data->wwcindicator <= 0) {
            redirect($CFG->wwwroot);
            exit;
        }
        
        
        
    
        
        /*if($data->wwcindicator > 0) {
            if(\local_adsafe\utils::update_record_through_userid($data)){
                //redirect(new moodle_url("/local/adsafe/churchrolelist.php"));
                redirect($CFG->wwwroot);
                exit;
            } else {
                redirect($CFG->wwwroot);
                exit;
                //echo('error');
            }
        }
        if($data->wwcindicator <= 0) {
            redirect($CFG->wwwroot);
            exit;
        }*/
    }
} else {
    $workingwithchildrencardform = new \local_adsafe\forms\workingwithchildrencardform('',array('userid'=>$USER->id));
    if($workingwithchildrencardform->is_cancelled()) {
        redirect(new moodle_url("/local/adsafe/churchrolelist.php"));
        exit;
    }
}

echo $output->header();
echo $output->add_working_with_children_card_list($USER->id);
echo $output->footer();