<?php
/**
 * ADSAFE working with children card
 *
 * workingwithchildrencardform form definition.
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
 * Form to add children card
 *
 * @package    local_adsafe
 * @author     Ken Chang (@link kenc@pukunui.com)
 * @copyright  2018 Pukunui (@link https://pukunui.com/)
 * @license    https://www/gnu.org/copyleft/copyleft/gpl.html GNU GPL v3 or later
 */
 
class workingwithchildrencardform extends \moodleform {
    
    /**
     * Form definition.
     *
     * @return void
     */
    public function definition() {
        global $CFG;
        $mform =& $this->_form;
        
        $customdata = $this->_customdata;
       
        
        $spsuserrecord = \local_adsafe\utils::get_wwc_record_from_userid($customdata['userid']);
        

        // When user made one or multiple wrong choosen for this page, show this string to them
        $strrequired = get_string('required');
        
        
        $mform->addElement('hidden', 'id', get_string('id', 'local_adsafe'), array('size' => 50));
        $mform->setType('id', PARAM_TEXT);
        
        
        $mform->addElement('hidden', 'spsuserid', get_string('id', 'local_adsafe'), array('size' => 50));
        $mform->setType('spsuserid', PARAM_INT);
        $mform->setDefault('spsuserid', $customdata['userid']);
        
        $wwccheckbox = $mform->addElement('advcheckbox', 'wwcindicator', get_string('ihaveavaildworkingwithchildrenscard', 'local_adsafe'),null);
        $mform->setType('wwcindicator', PARAM_INT);
        $wwccheckbox->setChecked(false);
        
        
        $statemenu = \local_adsafe\utils::get_all_state();
        $stateselect = $mform->addElement('select', 'state', get_string('stateissued', 'local_adsafe'), $statemenu);
        $mform->addRule('state', $strrequired, 'required', null, 'client');
        $mform->setType('state', PARAM_INT);
        
        
        $mform->addElement('text', 'nameoncard', get_string('nameoncard', 'local_adsafe'), array('size' => 50));
        //$mform->addRule('nameoncard', $strrequired, 'required', null, 'client');
        $mform->setType('nameoncard', PARAM_TEXT);
        $mform->setDefault('nameoncard', '');
        
        
        $thisyear = (int)date("Y");
        $mform->addElement('date_selector', 'dateofbirth', get_string('dateofbirth', 'local_adsafe'),
            array('startyear' => 1900,
                  'stopyear'  => ($thisyear + 10),
                  'timezone'  => 99,
                  'optional'  => false,
                 ));
        $mform->setType('dateofbirth', PARAM_INT);
        $mform->addRule('dateofbirth', $strrequired, 'required', null, 'client');
        
        
        $mform->addElement('text', 'cardnumber', get_string('cardnumber', 'local_adsafe'), array('size' => 50));
        
        //$mform->addRule('cardnumber', $strrequired, 'required', null, 'client');
        $mform->setType('cardnumber', PARAM_TEXT);
        $mform->setDefault('cardnumber', '');
        
        $thisyear2 = (int)date("Y");
        $mform->addElement('date_selector', 'expirydate', get_string('expirydate', 'local_adsafe'),
            array('startyear' => $thisyear2,
                  'stopyear'  => ($thisyear2 + 10),
                  'timezone'  => 99,
                  'optional'  => false,
                 ));
        $mform->setType('expirydate', PARAM_INT);
        $mform->addRule('expirydate', $strrequired, 'required', null, 'client');
        
        
        
        $mform->addElement('hidden', 'timecreated', get_string('timecreated', 'local_adsafe'), array('size' => 50));
        $mform->setType('timecreated', PARAM_TEXT);
        $mform->setDefault('timecreated', '');
        
        
        if($spsuserrecord) {
            $mform->setDefault('id', $spsuserrecord->id);
            $mform->setDefault('spsuserid', $spsuserrecord->spsuserid);
            
            if ($spsuserrecord->wwcindicator > 0) {
                $wwccheckbox->setChecked(true);
            } else {
                $wwccheckbox->setChecked(false);
            }
            $mform->setDefault('state', $spsuserrecord->stateid);
            $mform->setDefault('nameoncard', $spsuserrecord->nameoncard);
            $mform->setDefault('dateofbirth', $spsuserrecord->dateofbirth);
            $mform->setDefault('cardnumber', $spsuserrecord->cardnumber);
            $mform->setDefault('expirydate', $spsuserrecord->expirydate);
            $mform->setDefault('timecreated', $spsuserrecord->timecreated);
        }
      
        
        //$mform->addElement('hidden', 'action', 'edit');
        //$mform->setType('action', PARAM_ALPHA);
        

        $this->add_action_buttons(true, get_string('save', 'local_adsafe'));
        
        
        $mform->addElement('html', "<div class=memlstgup align=left style=width:623px>");
            $mform->addElement('html', "<p style=margin-left:120px;background-color:yellow;>Note: This WWC card will not be validate if the member is not active in a church.");
            $mform->addElement('html', "</p>");
        $mform->addElement('html', "</div>");
        
        
        
        
        
        
        /*
 
        $conferencemenu = \local_adsafe\utils::get_all_conferences();
        // select conference drop-down box
        $conferenceselect = $mform->addElement('select', 'conid', get_string('selecttheconference', 'local_adsafe'), $conferencemenu);
        $mform->addRule('conid', $strrequired, 'required', null, 'client');
        $mform->setType('conid', PARAM_RAW);
        
        //$churcheventmenu = \local_adsafe\utils::get_regarding_church_event_from_conferenceid();
        $churcheventmenu = '';
        // select conference drop-down box
        $churcheventselect = $mform->addElement('select', 'locid', get_string('selectthechurchslashevent', 'local_adsafe'), $churcheventmenu);
        $mform->addRule('locid', $strrequired, 'required', null, 'client');
        $mform->setType('locid', PARAM_RAW);
        
        $thisyear = (int)date("Y");
        $mform->addElement('date_selector', 'startdate', get_string('starttime', 'local_adsafe'),
            array('startyear' => $thisyear,
                  'stopyear'  => ($thisyear + 10),
                  'timezone'  => 99,
                  'optional'  => false,
                 ));
                 
        $mform->addElement('checkbox', 'main', get_string('mymainchurch', 'local_adsafe'));
        
        $mform->addElement('hidden', 'action', 'add');
        $mform->setType('action', PARAM_ALPHA);
        
        $this->add_action_buttons(false, get_string('addmetothechurchslashevent', 'local_adsafe'));*/
        
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
        if ($data['wwcindicator'] > 0) {
            if(empty($data['nameoncard'])) {
               $errors['nameoncard'] = get_string('required') ;
            }
            if(empty($data['cardnumber'])) {
               $errors['cardnumber'] = get_string('required') ;
            }
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