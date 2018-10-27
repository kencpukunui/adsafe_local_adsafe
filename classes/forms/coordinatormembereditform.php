<?php
/**
 * Co-ordinator member edit form
 *
 * coordinatormembereditform form definition.
 *
 * @package    local_adsafe
 * @author     Ken Chang (@link kenc@pukunui.com)
 * @copyright  2018 Pukunui (@link https://pukunui.com/)
 * @license    https://www/gnu.org/copyleft/copyleft/gpl.html GNU GPL v3 or later
 */
 
namespace local_adsafe\forms;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir . '/pagelib.php');
/**
 * Form to let coordinator / manager / Admin to view or confirm user
 *
 * @package    local_adsafe
 * @author     Ken Chang (@link kenc@pukunui.com)
 * @copyright  2018 Pukunui (@link https://pukunui.com/)
 * @license    https://www/gnu.org/copyleft/copyleft/gpl.html GNU GPL v3 or later
 */
 
class coordinatormembereditform extends \moodleform {
    
    /**
     * Form definition.
     *
     * @return void
     */
    public function definition() {
        global $CFG;
        $mform =& $this->_form;
        
        $customdata = $this->_customdata;
       

        // When user made one or multiple wrong choosen for this page, show this string to them
        $strrequired = get_string('required');
        
        //var_dump($customdata['userid']);
        //echo('</br>');
        
        
        $memid = $customdata['memid'];

        $memrecord = \local_adsafe\utils::get_record_from_member_table_through_memid($memid); 
        
        
        $mform->addElement('hidden', 'hiddenmemid', get_string('location', 'local_adsafe'), array('size' => 50));
        $mform->setType('hiddenmemid', PARAM_INT);
        
        
        $mform->addElement('text', 'location', get_string('location', 'local_adsafe'), array('size' => 50,'disabled' => 'disabled'));
        $mform->setType('location', PARAM_TEXT);
        $mform->setDefault('location', '');
        
        $mform->addElement('hidden', 'hiddenlocationid', get_string('location', 'local_adsafe'), array('size' => 50));
        $mform->setType('hiddenlocationid', PARAM_TEXT);
        
        $mform->addElement('text', 'member', get_string('member', 'local_adsafe'), array('size' => 50,'disabled' => 'disabled'));
        $mform->setType('member', PARAM_TEXT);
        $mform->setDefault('member', '');
        
        $mform->addElement('hidden', 'hiddenmemberid', get_string('member', 'local_adsafe'), array('size' => 50));
        $mform->setType('hiddenmemberid', PARAM_TEXT);
        
        /*$thisyear = (int)date("Y");
        $mform->addElement('date_selector', 'startdate', get_string('starttime', 'local_adsafe'),
            array('startyear' => $thisyear,
                  'stopyear'  => ($thisyear + 10),
                  'timezone'  => 99,
                  'optional'  => false,
                 ));
        $mform->addRule('startdate', $strrequired, 'required', null, 'client');
        $mform->setDefault('startdate', '');
        
        $thisyear2 = (int)date("Y");
        $mform->addElement('date_selector', 'enddate', get_string('endtime', 'local_adsafe'),
        array('startyear' => $thisyear2,
              'stopyear'  => ($thisyear2 + 10),
              'timezone'  => 99,
              'optional'  => true,
             ));
        $mform->setDefault('enddate', '');*/
        
        

       /* $mform->addElement('header', 'confirmation', get_string('confirmation', 'local_adsafe'));
        $renderer =& $this->_form->defaultRenderer();
        $highlightheadertemplate = str_replace('ftoggler', 'ftoggler bold highlight', $renderer->_headerTemplate);
        $renderer->setElementTemplate($highlightheadertemplate , 'confirmation');*/
        
        
        

        $confirmationstatus = \local_adsafe\utils::get_confirmation_statuses();
        // select confirmation drop-down box
        $comfirmationselect = $mform->addElement('select', 'status', get_string('confirmation', 'local_adsafe'), $confirmationstatus);
        $mform->addRule('status', $strrequired, 'required', null, 'client');
        $mform->setType('status', PARAM_INT);
        
        $thisyear3 = (int)date("Y");
        $mform->addElement('date_selector', 'dateupdated', get_string('dateupdated', 'local_adsafe'),
        array('startyear' => $thisyear3,
              'stopyear'  => ($thisyear3 + 10),
              'timezone'  => 99,
              'optional'  => false,
             ));
        $mform->addRule('dateupdated', $strrequired, 'required', null, 'client');
             
        $mform->addElement('text', 'confirmeduserid', get_string('confirmedby', 'local_adsafe'), array('size' => 50,'disabled' => 'disabled'));
        $mform->setType('confirmeduserid', PARAM_TEXT);
        $mform->setDefault('confirmeduserid', '');
        
        $mform->addElement('hidden', 'hiddenconfirmeduserid', get_string('confirmedby', 'local_adsafe'), array('size' => 50));
        $mform->setType('hiddenconfirmeduserid', PARAM_TEXT);
        
        $mform->addElement('textarea', 'commentarea', get_string("comments", "local_adsafe"), 'wrap="virtual" rows="3" cols="50"');
        $mform->setDefault('commentarea', '');
        
        $activecheckbox = $mform->addElement('advcheckbox', 'activecheckbox', get_string('activated', 'local_adsafe'),null);
        $mform->addRule('activecheckbox', $strrequired, 'required', null, 'client');
        $activecheckbox->setChecked(false);
        
        if($memrecord) {
            $mform->setDefault('hiddenmemid', $memrecord->memid);
            
            $mform->setDefault('location', $memrecord->location);
            
            $mform->setDefault('hiddenlocationid', $memrecord->locationid);
            
            
            $mform->setDefault('member', $memrecord->member);
            
            $mform->setDefault('hiddenmemberid', $memrecord->userid);
            
            
            //$mform->setDefault('startdate', $memrecord->starttime);
            //$mform->setDefault('enddate', $memrecord->endtime);
            
            
            
            $mform->setDefault('status', $memrecord->confirmed);
            $mform->setDefault('confirmeduserid', $memrecord->confirmedusername);
            
            $mform->setDefault('hiddenconfirmeduserid', $customdata['userid']);
            
            $mform->setDefault('commentarea', $memrecord->comments);
            
            if($memrecord->active == 0){
                $activecheckbox->setChecked(false);
            } else {
                $activecheckbox->setChecked(true);
            }
        }
        
        
        $this->add_action_buttons(true, get_string('save', 'local_adsafe'));
        /*
        
        $mform->addElement('hidden', 'id', get_string('id', 'local_adsafe'), array('size' => 50));
        $mform->setType('id', PARAM_TEXT);
        $mform->setDefault('id', $customdata['userid']);
        
        $mform->addElement('hidden', 'role', get_string('role', 'local_adsafe'), array('size' => 50));
        $mform->setType('role', PARAM_TEXT);
        $mform->setDefault('role', $customdata['role']);*/
        
      //  $locationrecord = \local_adsafe\utils::get_location_record_from_userid_and_role($customdata['userid'],$customdata['role']);
       /* $locationidselect = $mform->addElement('select', 'locationid', get_string('forlocation', 'local_adsafe'), $locationrecord);
        $mform->addRule('locationid', $strrequired, 'required', null, 'client');
        $mform->setType('locationid', PARAM_RAW);*/
        
      /*  $untilgroup=array();
        $untilgroup[] = &$mform->createElement('select', 'locationid', get_string('forlocation', 'local_adsafe'), $locationrecord);
        $mform->setType('locationid', PARAM_RAW);
        $untilgroup[] = &$mform->createElement('submit', 'display', get_string('display', 'local_adsafe'));
        $mform->addGroup($untilgroup, 'untilgroup', '&nbsp', array(''), false);
        */
        
        /*if($customdata['locationid']==''){
            $mform->setDefault('locationid', 1);
        } else {
            $mform->setDefault('locationid', $customdata['locationid']);
        }*/
        
        
       /* $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'searchlocation', get_string('search', 'local_adsafe'));
        $buttonarray[] = &$mform->createElement('submit', 'coordinatorpage', get_string('co_ordinator', 'local_adsafe'));
        $mform->addGroup($buttonarray, 'buttonar', '&nbsp;', array(''), false);*/
        
        
        
     /*   $statemenu = \local_adsafe\utils::get_all_state();
        $stateselect = $mform->addElement('select', 'state', get_string('stateissued', 'local_adsafe'), $statemenu);
        $mform->addRule('state', $strrequired, 'required', null, 'client');
        $mform->setType('state', PARAM_RAW);
        
        
        $mform->addElement('text', 'nameoncard', get_string('nameoncard', 'local_adsafe'), array('size' => 50));
        $mform->addRule('nameoncard', $strrequired, 'required', null, 'client');
        $mform->setType('nameoncard', PARAM_TEXT);
        $mform->setDefault('nameoncard', '');
        
        $mform->addElement('text', 'cardnumber', get_string('cardnumber', 'local_adsafe'), array('size' => 50));
        
        $mform->addRule('cardnumber', $strrequired, 'required', null, 'client');
        $mform->setType('cardnumber', PARAM_TEXT);
        $mform->setDefault('cardnumber', '');
        
        $thisyear = (int)date("Y");
        $mform->addElement('date_selector', 'expirydate', get_string('expirydate', 'local_adsafe'),
            array('startyear' => $thisyear,
                  'stopyear'  => ($thisyear + 10),
                  'timezone'  => 99,
                  'optional'  => false,
                 ));
        $mform->addRule('expirydate', $strrequired, 'required', null, 'client');
        
        
        
        $mform->addElement('hidden', 'timecreated', get_string('timecreated', 'local_adsafe'), array('size' => 50));
        $mform->setType('timecreated', PARAM_TEXT);
        $mform->setDefault('timecreated', '');
        
        
        if($spsuserrecord) {
            
            $mform->setDefault('id', $spsuserrecord->id);
            
            if ($spsuserrecord->wwcindicator > 0) {
                $wwccheckbox->setChecked(true);
            } else {
                $wwccheckbox->setChecked(false);
            }
            $mform->setDefault('state', $spsuserrecord->stateid);
            $mform->setDefault('nameoncard', $spsuserrecord->nameoncard);
            $mform->setDefault('cardnumber', $spsuserrecord->cardnumber);
            $mform->setDefault('expirydate', $spsuserrecord->expirydate);
            $mform->setDefault('timecreated', $spsuserrecord->timecreated);
        }*/
      
        
        //$mform->addElement('hidden', 'action', 'edit');
        //$mform->setType('action', PARAM_ALPHA);
        

       // $this->add_action_buttons(true, get_string('save', 'local_adsafe'));
  
        
    }
    
    /**
     * Form validation.
     *
     * @param array $data  data from the form.
     * @param array $files  files uploaded.
     * @return array
     */
    public function validation($data, $files) {
        global $USER;
        $errors = parent::validation($data,$files);
        if ($data['status'] < 0) { //Non selected a valid location
            $errors['status'] = get_string('error_nonselectconfirmation', 'local_adsafe');
        }
        return $errors;
    }

    /*function get_data() {
        global $DB;
        $data = parent::get_data();
        if (!empty($data)) {
            $mform =& $this->_form;
            // Add the church event (locationid) properly to the $data object.
            if(!empty($mform->_submitValues['locid'])) {
                $data->locid = $mform->_submitValues['locid'];
            }
        }
        return $data;
    }*/
}