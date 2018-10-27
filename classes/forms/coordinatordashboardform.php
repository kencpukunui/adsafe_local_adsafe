<?php
/**
 * Co-ordinator dashboard form
 *
 * coordinatordashboardform form definition.
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
 * Form to view / display roles combinations for a location to the co-ordinator
 *
 * @package    local_adsafe
 * @author     Ken Chang (@link kenc@pukunui.com)
 * @copyright  2018 Pukunui (@link https://pukunui.com/)
 * @license    https://www/gnu.org/copyleft/copyleft/gpl.html GNU GPL v3 or later
 */
 
class coordinatordashboardform extends \moodleform {
    
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
        
        $mform->addElement('hidden', 'id', get_string('id', 'local_adsafe'), array('size' => 50));
        $mform->setType('id', PARAM_TEXT);
        $mform->setDefault('id', $customdata['userid']);
        
        $mform->addElement('hidden', 'role', get_string('role', 'local_adsafe'), array('size' => 50));
        $mform->setType('role', PARAM_TEXT);
        $mform->setDefault('role', $customdata['role']);
        
        $locationrecord = \local_adsafe\utils::get_location_record_from_userid_and_role($customdata['userid'],$customdata['role']);
        

        $untilgroup=array();
        $untilgroup[] = &$mform->createElement('static', '', '', '<label style=font-size:16px;font-weight:bold;>' . get_string('selectchurchoreven', 'local_adsafe') .'</label>');
        $untilgroup[] = &$mform->createElement('static', '', '', '　');
        $untilgroup[] = &$mform->createElement('select', 'locationid', get_string('selectchurchoreven', 'local_adsafe'), $locationrecord);
        $mform->setType('locationid', PARAM_RAW);
        $mform->setDefault('locationid', $customdata['locationid']);
        
        $untilgroup[] = &$mform->createElement('submit', 'display', get_string('display', 'local_adsafe'));
        $mform->addGroup($untilgroup, 'untilgroup', '&nbsp', array(''), false);

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