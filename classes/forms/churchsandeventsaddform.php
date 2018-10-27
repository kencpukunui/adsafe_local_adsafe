<?php
/**
 * ADSAFE Churches and Events
 *
 * churchsandeventsaddform form definition.
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
 * Form to add a church or event
 *
 * @package    local_adsafe
 * @author     Ken Chang (@link kenc@pukunui.com)
 * @copyright  2018 Pukunui (@link https://pukunui.com/)
 * @license    https://www/gnu.org/copyleft/copyleft/gpl.html GNU GPL v3 or later
 */
 
class churchsandeventsaddform extends \moodleform {
    
    /**
     * Form definition.
     *
     * @return void
     */
    public function definition() {
        global $CFG;
        $mform =& $this->_form;
        
        // Get data dynamically based on the selection from the dropdown
        //var_dump($CFG->dirroot .'/local/adsafe/main.js');
        //var_dump($PAGE->requires->js(new moodle_url($CFG->dirroot . '/local/adsafe/main.js')));

        // When user made one or multiple wrong choosen for this page, show this string to them
        $strrequired = get_string('required');
        
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
                 
        $mform->addElement('advcheckbox', 'mainchurch', get_string('mymainchurch', 'local_adsafe'));
        
        $mform->addElement('hidden', 'action', 'add');
        $mform->setType('action', PARAM_ALPHA);
        
        $this->add_action_buttons(false, get_string('addmetothechurchslashevent', 'local_adsafe'));
        
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
    
    
    
    function get_data() {
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
    }
}