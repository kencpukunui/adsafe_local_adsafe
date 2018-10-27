<?php
/*
 * Adsafe
 *
 * Form to add and edit the coordinator records.
 *
 * @package    : local_adsafe
 * @copyright  : 2018 Pukunui
 * @author     : Ken Chang, Pukunui {@link http://pukunui.com}
 * @license    : http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/formslib.php');
/*
 * Class conferenceedit_form extends moodleform
 */
class coordinatoredit_form extends moodleform {
    /*
     *Function Definition to define Form elements
     */
    public function definition() {
        //defined global parameter
        global $CFG, $DB;
        
        // Form initial
        $mform =& $this->_form;
        
        // Get outside values when the coordinatoredit.php call function to new this form
        $id      = $this->_customdata['id'];
        $locid   = $this->_customdata['locid'];
        $relocid = $this->_customdata['relocid'];
        $filterlocid = 0;
        
        // When user made one or multiple wrong choosen for this page, show this string to them
        $strrequired = get_string('required');

        //var_dump('form = ' . $locid);
        //echo('</br>');

        // Create an invisible textbox field
        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_RAW);
        
        // Create an invisible textbox field
        $mform->addElement('hidden', 'locationid', $locid);
        $mform->setType('locationid', PARAM_RAW);
        
        // when we press the edit hyperlink in the coordinatorlist_index.php, we can get the location id from urls(I have been hard code in the hyperlink)
        // but in the edit page, we need the form to pass the right location id to redirect us to right location page after we press the cancel button
        if(!$relocid) {
            $locationsql = "SELECT lal.id, lal.name
                            FROM {local_adsafe_location} lal
                            WHERE lal.id = $locid";
            $locationfields = $DB->get_records_sql($locationsql);
        } else {
            $locationsql = "SELECT lal.id, lal.name
                            FROM {local_adsafe_location} lal
                            WHERE lal.id = $relocid";
            $locationfields = $DB->get_records_sql($locationsql);
        }
        $storefield = new stdClass();
        foreach($locationfields as $field) { //convert sql record to an object
                $storefield->id = $field->id;
                $storefield->name = $field->name;
        }
        // Disable the location field, basically we are editing the user in this location without change it
        $mform->addElement('text', 'locationname', get_string('location', 'local_adsafe'),
        array('size' => 50, 'disabled' => 'disabled'));
        $mform->setType('locationname', PARAM_RAW);
        $mform->setDefault('locationname', $storefield->name);
        
        // get all the user from user table
        $namemenu = array('0' => get_string('selectamember', 'local_adsafe'));
        //$storefield->id
        if(!$relocid) {
            $filterlocid = $locid;
        } else {
            $filterlocid = $relocid;
        }
        $namesql = "SELECT lam.userid, CONCAT(u.lastname,', ',u.firstname)
                    FROM {local_adsafe_member} lam
                    JOIN {user} u
                    ON lam.userid = u.id
                    WHERE lam.locationid = $filterlocid
                    AND lam.activated = 1
                    ORDER BY 2";
        /*$namesql = "SELECT u.id,CONCAT(u.lastname,', ',u.firstname)
                    FROM {user} u
                    ORDER BY 2";*/
        if($getnameidfields = $DB->get_records_sql_menu($namesql,null)){
            foreach($getnameidfields as $getid => $getname) {
                $namemenu["$getid"] = $getname; // put the user id and name into an array
            }
        }
        
        // put all user in the Name drop-down box
        $nameselect = $mform->addElement('select', 'userid', get_string('member', 'local_adsafe'), $namemenu);
        $mform->addRule('userid', $strrequired, 'required', null, 'client');
        $mform->setType('userid', PARAM_RAW);
        $nameselect->setSelected(0); // new user will show select an username
        
        $mform->addElement('date_selector', 'starttime', get_string('starttime', 'local_adsafe'));
        $mform->addRule('starttime', $strrequired, 'required', null, 'client');
        
        // tickbox function for endtime of date_selector
        $testarr =   array('startyear' => 1970, 'timezone' => 99, 'optional' => true);
        $mform->addElement('date_selector', 'endtime', get_string('endtime', 'local_adsafe'),$testarr);
        //$mform->addRule('endtime', $strrequired, 'required', null, 'client');

        // if editing the user of coordinator page $id reutrn true
        // pre-loading the user information
        if ($id) {
            $coosql = "SELECT lac.*
                       FROM mdl_local_adsafe_coordinators lac
                       WHERE lac.id = :id";
            $coordinatordetails = $DB->get_records_sql($coosql, array('id' => $id));
            foreach ($coordinatordetails as $cds) {
                $mform->setDefault('locid', $cds->locationid);
                $nameselect->setSelected($cds->userid);
                $mform->setDefault('starttime', $cds->starttime);
                $mform->setDefault('endtime', $cds->endtime);
            }
        }
        //put save and cancel buttons in same group
        //$mform->addElement('html', "<div class=memlstgup align=center>");
        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'save', get_string('save', 'local_adsafe'));
        $buttonarray[] = &$mform->createElement('cancel', 'cancel', get_string('cancel', 'local_adsafe'));
        $mform->addGroup($buttonarray, 'buttomem', '&nbsp;', array(''), false);
        //$mform->addElement('html', "</div>");
    }
    /**
     * Function validation to validate form elements
     * @param $data holds the data Submitted form the Form
     * @param $files, files Submitted as part of the Form
     * @return $errors displays the Error message when encountered
     */
     public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);
        if (!empty($data['id'])) { //When Edit the coordinator
            if ($DB->count_records('local_adsafe_member', array('id' => $data['id']))) {
                $id = $data['id'];
                $locationid = $data['locationid'];
                $userid = $data['userid'];
                // Check is this user already existed in coordinator table of this location 
                $useridsql = "SELECT lac.userid
                              FROM {local_adsafe_coordinators} lac
                              WHERE lac.locationid = '$locationid'
                              AND lac.userid = '$userid'
                              AND lac.id <> '$id'";
                $useriddetails = $DB->get_records_sql($useridsql,null);
                if ($useriddetails) {
                    $errors['userid'] = get_string('error_duplicatecoordinatorname', 'local_adsafe');
                }
            }
        } else { // When New a coordinator
            // Check is this user already existed in coordinator table of this location 
            if ($DB->count_records('local_adsafe_coordinators', array('userid' => $data['userid'], 'locationid' => $data['locationid']))) {
                $errors['userid'] = get_string('error_duplicatecoordinatorname', 'local_adsafe');
            }
        }
        if ($data['userid'] <= 0) { // Non selected userid
            $errors['userid'] = get_string('error_nonselectusername', 'local_adsafe');
        }
        if ($data['endtime'] > 0) { // If the endtime checkbox has been ticked-up, which means set the expiry date for coordinator 
            if ($data['starttime'] >= $data['endtime']) { //non valid timpstamp
                $errors['starttime'] = get_string('error_starttimemustlessthanendtime', 'local_adsafe');
            }
        }
        return $errors;
    }
}