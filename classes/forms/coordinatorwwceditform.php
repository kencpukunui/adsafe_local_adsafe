<?php
/**
 * ADSAFE coordinator verify working with children card of each location
 *
 * coordinatorwwceditform form definition.
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
 * Form for coordinator to view / edit children card
 *
 * @package    local_adsafe
 * @author     Ken Chang (@link kenc@pukunui.com)
 * @copyright  2018 Pukunui (@link https://pukunui.com/)
 * @license    https://www/gnu.org/copyleft/copyleft/gpl.html GNU GPL v3 or later
 */
 
class coordinatorwwceditform extends \moodleform {
    
    /**
     * Form definition.
     *
     * @return void
     */
    public function definition() {
        global $CFG;
        $mform =& $this->_form;
        
        $customdata = $this->_customdata;
        
        
        $wwcverifiedrecord = \local_adsafe\utils::get_record_from_wwc_verify($customdata['userid'],$customdata['locationid']);
        

        //$spsuserrecord = \local_adsafe\utils::get_wwc_record_from_userid($customdata['userid']);
        

        // When user made one or multiple wrong choosen for this page, show this string to them
        $strrequired = get_string('required');
        
        $mform->addElement('hidden', 'hiddenveridiedid', get_string('wwcverifiedid', 'local_adsafe'), array('size' => 50));
        $mform->setType('hiddenveridiedid', PARAM_INT);
        
        
        $mform->addElement('hidden', 'spsid', get_string('id', 'local_adsafe'), array('size' => 50));
        $mform->setType('spsid', PARAM_TEXT);
        $mform->setDefault('spsid', $customdata['userid']);
        
   
        
        $wwccheckbox = $mform->addElement('advcheckbox', 'wwcindicator', get_string('ihaveavaildworkingwithchildrenscard', 'local_adsafe'));
        $wwccheckbox->setChecked(false);
        
        
        
        $statemenu = \local_adsafe\utils::get_all_state();
        $stateselect = $mform->addElement('select', 'state', get_string('stateissued', 'local_adsafe'), $statemenu);
        $mform->setType('state', PARAM_RAW);
        
        
        $mform->addElement('text', 'nameoncard', get_string('nameoncard', 'local_adsafe'),array('size' => 30,'disabled' => 'disabled'));
        $mform->setType('nameoncard', PARAM_TEXT);
        $mform->setDefault('nameoncard', '');
        
        $dob=array();
        $dob[] =& $mform->createElement('text', 'datetext', get_string('dateofbirth','local_adsafe'),array('size' => 10,'disabled' => 'disabled'));
        $mform->addGroup($dob, 'choosedate', get_string('dateofbirth','local_adsafe'), array(' '), false);
        $mform->setType('datetext', PARAM_TEXT);

        
        $mform->addElement('text', 'cardnumber', get_string('cardnumber', 'local_adsafe'), array('size' => 30,'disabled' => 'disabled'));
        $mform->setType('cardnumber', PARAM_TEXT);
        $mform->setDefault('cardnumber', '');
        
        
        $epd=array();
        $epd[] =& $mform->createElement('text', 'expirydate', get_string('expirydate','local_adsafe'),array('size' => 10,'disabled' => 'disabled'));
        $mform->addGroup($epd, 'chooseexpirydate', get_string('expirydate','local_adsafe'), array(' '), false);
        $mform->setType('expirydate', PARAM_TEXT);
   
  
        $mform->addElement('hidden', 'timecreated', get_string('timecreated', 'local_adsafe'), array('size' => 50));
        $mform->setType('timecreated', PARAM_TEXT);
        $mform->setDefault('timecreated', '');
        
        
       
        
        
        // other dropdown header coding method
        /*$mform->addElement('header', 'details', get_string('verification', 'local_adsafe'));
        $renderer =& $this->_form->defaultRenderer();
        $highlightheadertemplate = str_replace('ftoggler', 'ftoggler highlight bold', $renderer->_headerTemplate);
        $renderer->setElementTemplate($highlightheadertemplate , 'details');*/
        
        
        
        
        
        $mform->addElement('html', '<div class="h2header">');
        $mform->addElement('html', '<h2>'.get_string('verification', 'local_adsafe'));
        $mform->addElement('html', '</h2>');
        $mform->addElement('html', "</div>");
        
        
        
        $mform->addElement('hidden', 'hiddenlocationid', get_string('location', 'local_adsafe'), array('size' => 50));
        $mform->setType('hiddenlocationid', PARAM_INT);
        

        $outcomestatuses = \local_adsafe\utils::get_verification_outcome_statuses();
        $stateselect = $mform->addElement('select', 'outcomestate', get_string('verificationoutcome', 'local_adsafe'), $outcomestatuses);
        $mform->addRule('outcomestate', $strrequired, 'required', null, 'client');
        $mform->setType('outcomestate', PARAM_RAW);
        
        $thisyear = (int)date("Y");
        $mform->addElement('date_selector', 'dateverified', get_string('dateverified', 'local_adsafe'),
            array('startyear' => $thisyear,
                  'stopyear'  => ($thisyear + 10),
                  'timezone'  => 99,
                  'optional'  => false,
                 ));
        $mform->addRule('dateverified', $strrequired, 'required', null, 'client');
        
        
        $mform->addElement('text', 'verifiedusername', get_string('verifiedby', 'local_adsafe'), array('size' => 50,'disabled' => 'disabled'));
        $mform->setType('verifiedusername', PARAM_TEXT);
        $mform->setDefault('verifiedusername', '');
        
        $mform->addElement('hidden', 'hiddenverifieduserid', get_string('verifieduserid', 'local_adsafe'), array('size' => 50));
        $mform->setType('hiddenverifieduserid', PARAM_INT);
        
        $mform->addElement('textarea', 'commentarea', get_string("comments", "local_adsafe"), 'wrap="virtual" rows="3" cols="50"');
        $mform->setDefault('commentarea', '');

   
         if($wwcverifiedrecord) {
            
            $mform->setDefault('hiddenveridiedid', $wwcverifiedrecord->id);
            
            $mform->setDefault('spsid', $wwcverifiedrecord->spsid);
            
            if ($wwcverifiedrecord->wwcindicator > 0) {
                $wwccheckbox->setChecked(true);
            } else {
                $wwccheckbox->setChecked(false);
            }
            $mform->disabledIf('wwcindicator','hiddenveridiedid','>0');
            $mform->setDefault('state', $wwcverifiedrecord->stateid);
            $mform->disabledIf('state','hiddenveridiedid','>0');
            
            $mform->setDefault('nameoncard', $wwcverifiedrecord->nameoncard);
            $mform->setDefault('datetext', date('d-M-Y', $wwcverifiedrecord->dateofbirth));
            $mform->setDefault('cardnumber', $wwcverifiedrecord->cardnumber);
            $mform->setDefault('expirydate', date('d-M-Y', $wwcverifiedrecord->expirydate));
            
            
            $mform->setDefault('hiddenlocationid', $wwcverifiedrecord->locationid);
            $mform->setDefault('outcomestate', $wwcverifiedrecord->verified);
            $mform->setDefault('dateverified', $wwcverifiedrecord->dateverified);
            $mform->setDefault('verifiedusername', $wwcverifiedrecord->verifiedusername);
            $mform->setDefault('hiddenverifieduserid', $customdata['verifieduserid']);
            $mform->setDefault('commentarea', $wwcverifiedrecord->comments);
            $mform->setDefault('timecreated', $wwcverifiedrecord->timecreated);
        }
  
        // this method is to prevent non-button pressed value error when form read message
        // display 3 buttons on same line and clean css to prevent other div elements from being affected by the css syntax above
        $mform->addElement('html', "<div class=memlstgup align=center>");
            //save button
            $mform->addElement('html', "<div style='float:left;width: 70px;'>");
            $mform->addElement('submit', 'savebtn', get_string('save', 'local_adsafe'));
            $mform->addElement('html', "</div>");
            //cancel button
            $mform->addElement('html', "<div style='float:left;width: 80px;'>");
            $mform->addElement('cancel', 'cancelbtn', get_string('cancel', 'local_adsafe'));
            $mform->addElement('html', "</div>");
            //delete button
            $mform->addElement('html', "<div style='float:left;width: 70px;'>");
            $mform->addElement('submit', 'newverification', get_string('newverification', 'local_adsafe'));
            $mform->addElement('html', "</div>");
            // clean above css
            $mform->addElement('html', "<div style=clear:both>");
            $mform->addElement('html', "</div>");
        $mform->addElement('html', "</div>");

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
        if ($data['outcomestate'] < 1) { //Non selected a valid location
            $errors['outcomestate'] = get_string('error_nonselectverificationoutcome', 'local_adsafe');
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