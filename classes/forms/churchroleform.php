<?php
/**
 * ADSAFE Select a church role form
 *
 * churchroleform form definition.
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
 * Form to add a role for church
 *
 * @package    local_adsafe
 * @author     Ken Chang (@link kenc@pukunui.com)
 * @copyright  2018 Pukunui (@link https://pukunui.com/)
 * @license    https://www/gnu.org/copyleft/copyleft/gpl.html GNU GPL v3 or later
 */
 
class churchroleform extends \moodleform {
    
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
        
        //$positioncheckbox = $mform->addElement('advcheckbox', 'positioncheckbox', get_string('doyouholdanyposition', 'local_adsafe'),null);
        //$mform->addRule('positioncheckbox', $strrequired, 'required', null, 'client');

        $rolemenu = \local_adsafe\utils::get_all_roles();
        // select role drop-down box
        $roleselect = $mform->addElement('select', 'roleid', get_string('selectarole', 'local_adsafe'), $rolemenu);
        $mform->addRule('roleid', $strrequired, 'required', null, 'client');
        $mform->setType('roleid', PARAM_INT);
        
        $locationmenu = \local_adsafe\utils::get_all_location($customdata['userid']);
        $locationselect = $mform->addElement('select', 'locid', get_string('selectmainlocationforrole', 'local_adsafe'), $locationmenu);
        $mform->addRule('locid', $strrequired, 'required', null, 'client');
        $mform->setType('locid', PARAM_INT);
        
        $activecheckbox = $mform->addElement('advcheckbox', 'activecheckbox', get_string('activerole', 'local_adsafe'),null);
        $mform->addRule('activecheckbox', $strrequired, 'required', null, 'client');
        
        /*$thisyear = (int)date("Y");
        $mform->addElement('date_selector', 'startdate', get_string('starttime', 'local_adsafe'),
            array('startyear' => $thisyear,
                  'stopyear'  => ($thisyear + 10),
                  'timezone'  => 99,
                  'optional'  => false,
                 ));
        $mform->addRule('startdate', $strrequired, 'required', null, 'client');
        
        $thisyear2 = (int)date("Y");
        $mform->addElement('date_selector', 'enddate', get_string('endtime', 'local_adsafe'),
        array('startyear' => $thisyear2,
              'stopyear'  => ($thisyear2 + 10),
              'timezone'  => 99,
              'optional'  => true,
             ));*/

        $mform->addElement('hidden', 'action', 'add');
        $mform->setType('action', PARAM_ALPHA);
        
        /*$buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'savemyrole', get_string('savemyrole', 'local_adsafe'));
        $buttonarray[] = &$mform->createElement('cancel', 'nextcancel', get_string('cancel', 'local_adsafe'));
        $mform->addGroup($buttonarray, 'buttonar', '&nbsp;', array(''), true);*/
        
        $this->add_action_buttons(false, get_string('savemyrole', 'local_adsafe'));
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