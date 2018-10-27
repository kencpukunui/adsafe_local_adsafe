<?php
/**
 * ADSAFE edit my roles
 *
 * churchroleeditform form definition.
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
 * Form to edit my roles
 *
 * @package    local_adsafe
 * @author     Ken Chang (@link kenc@pukunui.com)
 * @copyright  2018 Pukunui (@link https://pukunui.com/)
 * @license    https://www/gnu.org/copyleft/copyleft/gpl.html GNU GPL v3 or later
 */
 
class churchroleeditform extends \moodleform {
    
    /**
     * Form definition.
     *
     * @return void
     */
    public function definition() {
        global $CFG;
        $mform =& $this->_form;
        
        $customdata = $this->_customdata;
       
        $rolerecord = \local_adsafe\utils::get_record_from_roleid($customdata['roleid']);
        
        // When user made one or multiple wrong choosen for this page, show this string to them
        $strrequired = get_string('required');
        
        $mform->addElement('hidden', 'roleid', 'roleid');
        $mform->setType('roleid', PARAM_INT);
        $mform->setDefault('roleid', $customdata['roleid']);

        $mform->addElement('text', 'rolenamedisplay', get_string('selectarole', 'local_adsafe'), array('size' => 50, 'disabled' => 'disabled'));
        $mform->setType('rolenamedisplay', PARAM_TEXT);
        $mform->setDefault('rolenamedisplay', $rolerecord->rolename);
        
        $mform->addElement('hidden', 'rolenamehidden', get_string('selectarole', 'local_adsafe'));
        $mform->setType('rolenamehidden', PARAM_TEXT);
        $mform->setDefault('rolenamehidden', $rolerecord->rolename);
       
        $mform->addElement('text', 'locationnamedisplay', get_string('selectmainlocationforrole', 'local_adsafe'), array('size' => 50, 'disabled' => 'disabled'));
        $mform->setType('locationnamedisplay', PARAM_TEXT);
        $mform->setDefault('locationnamedisplay', $rolerecord->locationname);
        
        $mform->addElement('hidden', 'locationnamehidden', get_string('selectmainlocationforrole', 'local_adsafe'));
        $mform->setType('locationnamehidden', PARAM_TEXT);
        $mform->setDefault('locationnamehidden', $rolerecord->locationname);
        
        $activatecheckbox = $mform->addElement('advcheckbox', 'activatecheckbox', get_string('activated', 'local_adsafe'),null);
        if ($rolerecord->active > 0) {
            $activatecheckbox->setChecked(true);
        } else {
            $activatecheckbox->setChecked(false);
        }
        
        /*$mform->addElement('date_selector', 'startdate', get_string('starttime', 'local_adsafe'));
        $mform->addRule('startdate', $strrequired, 'required', null, 'client');
        $mform->setDefault('startdate', $rolerecord->startdate);
        
        $mform->addElement('date_selector', 'enddate', get_string('endtime', 'local_adsafe'));
        $mform->addRule('enddate', $strrequired, 'required', null, 'client');
        $mform->setDefault('enddate', $rolerecord->enddate);*/
        
      
        
        //$mform->addElement('hidden', 'action', 'edit');
        //$mform->setType('action', PARAM_ALPHA);
        

        $this->add_action_buttons(true, get_string('save', 'local_adsafe'));
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
        return parent::validation($data, $files);
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