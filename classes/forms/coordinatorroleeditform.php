<?php
/**
 * Co-ordinator role edit form
 *
 * coordinatorroleeditform form definition.
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
 * Form to let coordinator / manager / Admin to view or confirm role
 *
 * @package    local_adsafe
 * @author     Ken Chang (@link kenc@pukunui.com)
 * @copyright  2018 Pukunui (@link https://pukunui.com/)
 * @license    https://www/gnu.org/copyleft/copyleft/gpl.html GNU GPL v3 or later
 */
 
class coordinatorroleeditform extends \moodleform {
    
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
    
        $roleindex = $customdata['roleindex'];
        //var_dump($roleindex);
        $locationid = $customdata['locationid'];
        $confirmeduserid = $customdata['userid'];

        $rolerecord = \local_adsafe\utils::get_record_from_member_role_table_through_roleindex($roleindex,$locationid);

        
        $mform->addElement('hidden', 'hiddenroleid', get_string('role', 'local_adsafe'), array('size' => 50));
        $mform->setType('hiddenroleid', PARAM_INT);
        
        
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
        
       

        $rolestatuses = \local_adsafe\utils::get_role_statuses();
        // select role drop-down box
        $roleselect = $mform->addElement('select', 'rolestatus', get_string('role', 'local_adsafe'), $rolestatuses);
        $mform->addRule('rolestatus', $strrequired, 'required', null, 'client');
        $mform->setType('rolestatus', PARAM_INT);
        
        $activecheckbox = $mform->addElement('advcheckbox', 'activecheckbox', get_string('activated', 'local_adsafe'),null);
        $mform->addRule('activecheckbox', $strrequired, 'required', null, 'client');
        $activecheckbox->setChecked(false);
        
        $thisyear = (int)date("Y");
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
              'optional'  => false,
             ));
        $mform->addRule('enddate', $strrequired, 'required', null, 'client');
        
        
        $wwccheckbox = $mform->addElement('advcheckbox', 'wwccheckbox', get_string('doesthisrequireawwccheck', 'local_adsafe'),null);
        $mform->addRule('wwccheckbox', $strrequired, 'required', null, 'client');
        $wwccheckbox->setChecked(false);
        
        
        $confirmationstatus = \local_adsafe\utils::get_role_confirmation_statuses();
        // select confirmation drop-down box
        $comfirmationselect = $mform->addElement('select', 'confirmedstatus', get_string('confirmation', 'local_adsafe'), $confirmationstatus);
        $mform->addRule('confirmedstatus', $strrequired, 'required', null, 'client');
        $mform->setType('confirmedstatus', PARAM_INT);
        
        
        $thisyear3 = (int)date("Y");
        $mform->addElement('date_selector', 'dateupdated', get_string('dateupdated', 'local_adsafe'),
        array('startyear' => $thisyear3,
              'stopyear'  => ($thisyear3 + 10),
              'timezone'  => 99,
              'optional'  => false,
             ));
        $mform->addRule('dateupdated', $strrequired, 'required', null, 'client');
        
   
        $mform->addElement('text', 'confirmedusername', get_string('confirmedby', 'local_adsafe'), array('size' => 50,'disabled' => 'disabled'));
        $mform->setType('confirmedusername', PARAM_TEXT);
        $mform->setDefault('confirmedusername', '');
        
        $mform->addElement('hidden', 'hiddenconfirmeduserid', get_string('confirmedby', 'local_adsafe'), array('size' => 50));
        $mform->setType('hiddenconfirmeduserid', PARAM_TEXT);
        
        $mform->addElement('textarea', 'commentarea', get_string("comments", "local_adsafe"), 'wrap="virtual" rows="3" cols="50"');
        $mform->setDefault('commentarea', '');
  
        if($rolerecord) {
            $mform->setDefault('hiddenroleid', $rolerecord->id);
            
            $mform->setDefault('location', $rolerecord->location);
            
            $mform->setDefault('hiddenlocationid', $rolerecord->locationid);
            
            
            $mform->setDefault('member', $rolerecord->member);
            
            $mform->setDefault('hiddenmemberid', $rolerecord->userid);
            
            
            $mform->setDefault('rolestatus', $rolerecord->roleid);
            
            if($rolerecord->active == 0){
                $activecheckbox->setChecked(false);
            } else {
                $activecheckbox->setChecked(true);
            }
            
            
            $mform->setDefault('startdate', $rolerecord->startdate);
            $mform->setDefault('enddate', $rolerecord->enddate);
            
            if($rolerecord->wwcneededindex == 0){
                $wwccheckbox->setChecked(false);
            } else {
                $wwccheckbox->setChecked(true);
            }
 
            $mform->setDefault('confirmedstatus', $rolerecord->confirmed);
            
            //$mform->setDefault('dateupdated', $rolerecord->dateupdated);
            
            
            $mform->setDefault('confirmedusername', $rolerecord->confirmedusername);
            
            $mform->setDefault('hiddenconfirmeduserid', $confirmeduserid);
            
            $mform->setDefault('commentarea', $rolerecord->comments);
            
            
        }
        
        // $this->add_action_buttons(true, get_string('save', 'local_adsafe')); //moodle original button (save and cancel)
        
        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'savebtn', get_string('save', 'local_adsafe'));
        $buttonarray[] = &$mform->createElement('cancel', 'cancelbtn', get_string('cancel', 'local_adsafe'));
        $buttonarray[] = &$mform->createElement('submit', 'deletebtn', get_string('delete', 'local_adsafe'));
        $mform->addGroup($buttonarray, 'buttonarray', '&nbsp;', array(''), false);
        
        // this method is to prevent non-button pressed value error when form read message
        // display 3 buttons on same line and clean css to prevent other div elements from being affected by the css syntax above
        /*$mform->addElement('html', "<div class=memlstgup align=center>");
            //save button
            //$mform->addElement('html', "<div style=float:left>");
            $mform->addElement('html', "<div style='float:left;width: 70px;'>");
            $mform->addElement('submit', 'saveordelbtn', get_string('save', 'local_adsafe'));
            $mform->addElement('html', "</div>");
            //cancel button
            //$mform->addElement('html', "<div style=float:left>");
            $mform->addElement('html', "<div style='float:left;width: 80px;'>");
            $mform->addElement('cancel', 'cancelbtn', get_string('cancel', 'local_adsafe'));
            $mform->addElement('html', "</div>");
            //delete button
            //$mform->addElement('html', "<div style=float:left>");
            $mform->addElement('html', "<div style='float:left;width: 70px;'>");
            $mform->addElement('submit', 'saveordelbtn', get_string('delete', 'local_adsafe'));
            $mform->addElement('html', "</div>");
            // clean above css
            $mform->addElement('html', "<div style=clear:both>");
            $mform->addElement('html', "</div>");
        $mform->addElement('html', "</div>");*/
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
        if ($data['rolestatus'] < 1) { //Non selected a valid location
            $errors['rolestatus'] = get_string('error_nonselectrole', 'local_adsafe');
        }
        if ($data['startdate'] > $data['enddate']) { //Non selected valid startdate and enddate
            $errors['startdate'] = get_string('error_starttimemustlessthanendtime', 'local_adsafe');
        }
        if ($data['confirmedstatus'] < 0) { //Non selected a valid location
            $errors['confirmedstatus'] = get_string('error_nonselectconfirmation', 'local_adsafe');
        }
        return $errors;
    }
}