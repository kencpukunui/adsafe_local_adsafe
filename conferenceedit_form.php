<?php
/*
 * ADSAFE
 *
 * Form to add and edit the conference records.
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
class conferenceedit_form extends moodleform {
    /*
     *Function Definition to define Form elements
     */
    public function definition() {
        global $CFG, $DB;
        $mform =& $this->_form;

        $confid = $this->_customdata['confid'];
        $strrequired = get_string('required');

        $mform->addElement('hidden', 'id', $confid);
        $mform->setType('id', PARAM_RAW);
        
        $mform->addElement('text', 'name', get_string('name', 'local_adsafe'), array('size' => 50));
        $mform->setType('name', PARAM_RAW);
        $mform->addRule('name', $strrequired, 'required', null, 'client');
        
        
        $statemenu = array('0' => get_string('selectastate', 'local_adsafe'));//"Select a state"
        $statesql = "SELECT las.id,las.shortname
                     FROM {local_adsafe_state} las
                     ORDER BY las.name";
        if($getstateidfields = $DB->get_records_sql_menu($statesql,null)) {
            foreach ($getstateidfields as $statefieldid => $stateshortname) {
                $statemenu["$statefieldid"] = $stateshortname;
            }
        }
        $stateselect = $mform->addElement('select', 'stateid', get_string('state', 'local_adsafe'), $statemenu);
        $mform->addRule('stateid', $strrequired, 'required', null, 'client');
        $mform->setType('stateid', PARAM_RAW);
        $stateselect->setSelected(0);
        
       /* $stateoptions = array();
        $stateoptions[0] = get_string('sa', 'local_adsafe');
        $stateoptions[1] = get_string('qld', 'local_adsafe');
        $stateoptions[2] = get_string('act', 'local_adsafe');
        $stateoptions[3] = get_string('nt', 'local_adsafe');
        $stateoptions[4] = get_string('tas', 'local_adsafe');
        $stateoptions[5] = get_string('vic', 'local_adsafe');
        $stateoptions[6] = get_string('wa', 'local_adsafe');
        $stateoptions[7] = get_string('nsw', 'local_adsafe');
        $mform->addElement('select', 'state', get_string('state', 'local_adsafe'), $stateoptions, 'align="center"');
        $mform->setType('state', PARAM_ALPHANUMEXT);*/
        
        /*$mform->addElement('text', 'state', get_string('state', 'local_adsafe'), array('size' => 50));
        $mform->setType('state', PARAM_RAW);
        $mform->addRule('state', $strrequired, 'required', null, 'client');*/
        /*$mform->addElement('text', 'conferenceuserid', get_string('conferenceuserid', 'local_adsafe'));
        $mform->setType('conferenceuserid', PARAM_RAW);
        $mform->addRule('conferenceuserid', $strrequired, 'required', null, 'client');*/
        
        $conferencemenu = array('0' => get_string('selectaconferenceleader', 'local_adsafe'));//"Select a conference leader"
        /*$sql = "SELECT u.id, CONCAT(u.firstname,u.lastname)
                FROM mdl_user u
                JOIN mdl_local_adsafe_conference lac
                ON u.id = lac.conferenceuserid";*/
        
        //Get all the conference user from database
        $consql = "SELECT u.id, CONCAT( u.lastname,', ', u.firstname) as ConferenceLeader                     
                   FROM {context} con                         
                   JOIN {role_assignments} ra 
                   ON con.id = ra.contextid 
                   AND con.contextlevel = 10                      
                   JOIN {role} r 
                   ON ra.roleid = r.id                      
                   JOIN {user} u 
                   ON u.id = ra.userid                      
                   WHERE r.id = 10
                   order by 2";
        if ($getconferenceidfields = $DB->get_records_sql_menu($consql, null)) {
            foreach ($getconferenceidfields as $confieldid => $conferencename) {
                $conferencemenu["$confieldid"] = $conferencename;
            }
        }       
        $conferenceselect = $mform->addElement('select', 'conferenceuserid', get_string('conferenceleader', 'local_adsafe'), $conferencemenu);
        $mform->addRule('conferenceuserid', $strrequired, 'required', null, 'client');
        $mform->setType('conferenceuserid', PARAM_RAW);
        $conferenceselect->setSelected(0);
        //$mform->addRule('conferenceuserid', $strrequired, 'required', null, 'client');

        //$mform->addElement('html', "<div class=groupcon align=center>");
        if ($confid) {
            $confdetails = $DB->get_record('local_adsafe_conference', array('id' => $confid));
            $mform->setDefault('name', $confdetails->name);
            
            /*for($i=0;$i<count($stateoptions);$i++){
                if($stateoptions[$i] == $confdetails->state){
                    $displaystatestring = $i;
                }
            }*/    

            //$mform->setDefault('state', $displaystatestring);//$confdetails->state
            $stateselect->setSelected($confdetails->stateid);
            $conferenceselect->setSelected($confdetails->conferenceuserid);
            
        }
        
        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'save', get_string('save', 'local_adsafe'));
        $buttonarray[] = &$mform->createElement('cancel', 'cancel', get_string('cancel', 'local_adsafe'));
        $mform->addGroup($buttonarray, 'buttonar', '&nbsp;', array(''), false);
        if ($confid) {
            $mform->addElement('html', $this->local_adsafe_get_conference_detail($confid));
            $mform->addElement('submit', 'newlocation', get_string('newlocation', 'local_adsafe'));
        }
        //$mform->addElement('html', "</div>");
    }
    
    /**
     * Function generates the  activities list in table format.
     *
     * @uses $DB, $CFG
     * @param $confid integer holds the conference user id
     * @return $table object.
     */
     public function local_adsafe_get_conference_detail($confid) {
        global $CFG, $DB;
        // Create the table headings.
        $table = new html_table();
        $table->width = '100%';
        /*// Set the row heading object.
        $row = new html_table_row();
        // Create the cell.
        $cell = new html_table_cell();
        $cell->header = true;
        $cell->text = get_string('locationforaconference', 'local_adsafe');
        $cell->style = 'text-align:left';
        $row->cells[] = $cell;
        // Create the cell.
        $cell = new html_table_cell();
        $cell->header = true;
        $cell->text = " ";
        $cell->style = 'text-align:left';
        $row->cells[] = $cell;
        // Create the cell.
        $cell = new html_table_cell();
        $cell->header = true;
        $cell->text = " ";
        $cell->style = 'text-align:left';
        $row->cells[] = $cell;
        // Create the cell.
        $cell = new html_table_cell();
        $cell->header = true;
        $cell->text = " ";
        $cell->style = 'text-align:left';
        $row->cells[] = $cell;
        $table->data[] = $row;*/
        // Set the row heading object.
        $row = new html_table_row();
        // Create the cell.
        $cell = new html_table_cell();
        $cell->header = true;
        $cell->text = get_string('dchurchslashevent', 'local_adsafe');;
        $cell->style = 'text-align:left';
        $row->cells[] = $cell;
        // Create the cell.
        $cell = new html_table_cell();
        $cell->header = true;
        $cell->text = get_string('address', 'local_adsafe');
        $cell->style = 'text-align:left';
        $row->cells[] = $cell;
        // Create the cell.
        $cell = new html_table_cell();
        $cell->header = true;
        $cell->text = get_string('pastor', 'local_adsafe');
        $cell->style = 'text-align:left';
        $row->cells[] = $cell;
        // Create the cell.
        $cell = new html_table_cell();
        $cell->header = true;
        $cell->text = get_string('action', 'local_adsafe');
        $cell->style = 'text-align:left';
        $row->cells[] = $cell;
        $table->data[] = $row;
       // $conferencerecords = $DB->get_records('local_adsafe_location', array('conferenceid' => $confid));

        $sql = "SELECT lal.id, lal.name, lal.address, CONCAT(u.lastname,', ',u.firstname) as 'pastor', lal.conferenceid
                FROM {local_adsafe_location} lal
                INNER JOIN mdl_user u
                ON lal.pastoruserid = u.id
                WHERE lal.conferenceid = $confid
                ORDER BY lal.name";
        $conferencerecords = $DB->get_records_sql($sql);

        foreach ($conferencerecords as $cfr) {
            $editlink   = "<a href='$CFG->wwwroot/local/adsafe/locationdetail_index.php?id=$cfr->id&pid=$confid&action=edit'>". get_string('edit', 'local_adsafe')."</a>"; //haven't create it yet 21062018
            $deletelink = "<a href='$CFG->wwwroot/local/adsafe/locationdetail_index.php?id=$cfr->id&pid=$confid&action=delete'>". get_string('delete', 'local_adsafe')."</a>"; //haven't create it yet 21062018
  
            // Set the row heading object.
            $row = new html_table_row();
            // Create the cell.
            $cell = new html_table_cell();
            $cell->header = true;
            $cell->text = $cfr->name;
            $cell->style = 'text-align:left';
            $row->cells[] = $cell;
            // Create the cell.
            $cell = new html_table_cell();
            $cell->header = true;
            $cell->text = $cfr->address;
            $cell->style = 'text-align:left';
            $row->cells[] = $cell;
            // Create the cell.
            $cell = new html_table_cell();
            $cell->header = true;
            
            /*$sql = $DB->get_field_sql("SELECT CONCAT(u.lastname,', ',u.firstname) as pastoruserid
                                       FROM mdl_local_adsafe_location lal
                                       JOIN mdl_user u
                                       ON u.id = $cfr->pastoruserid");
            $cell->text = $sql;*/
            $cell->text = $cfr->pastor;
            $cell->style = 'text-align:left';
            $row->cells[] = $cell;
            // Create the cell.
            $cell = new html_table_cell();
            $cell->header = true;
            $cell->text = $editlink.' '.$deletelink;
            $cell->style = 'text-align:left';
            $row->cells[] = $cell;
            $table->data[] = $row;
        }
        // Add to the table.
        return html_writer::table($table);
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
        if (!empty($data['name'])) {
            if ($data['id']) {
                if (!$DB->count_records('local_adsafe_conference', array('name' => $data['name'], 'id' => $data['id']))) {
                    if ($DB->count_records('local_adsafe_conference', array('name' => $data['name']))) {
                        $errors['name'] = get_string('error_duplicateconferencename', 'local_adsafe');
                    }
                }
            } else {
                if ($DB->count_records('local_adsafe_conference', array('name' => $data['name']))) {
                    $errors['name'] = get_string('error_duplicateconferencename', 'local_adsafe');
                }
            }
        }
        if ($data['stateid'] <= 0) { //Non selected state
            $errors['stateid'] = get_string('error_nonselectstate', 'local_adsafe');
        }
        if ($data['conferenceuserid'] <= 0) { //Non selected conferenceuserid
            $errors['conferenceuserid'] = get_string('error_invalidconferenceuserid', 'local_adsafe');
        }
        return $errors;
    }
}