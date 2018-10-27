<?php
/**
 * ADSAFE Churches and Events buttons
 *
 * churchsandeventsbuttonform form definition.
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
 
class churchsandeventsbuttonform extends \moodleform {
    
    /**
     * Form definition.
     *
     * @return void
     */
    public function definition() {
        global $CFG;
        $mform =& $this->_form;
        $this->add_action_buttons(true, get_string('nextmyroles', 'local_adsafe'));
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
}