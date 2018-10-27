<?php
/*
 * ADSAFE
 *
 * Form to add the location detail activity records.
 *
 * @package    : local_adsafe
 * @copyright  : 2018 Pukunui
 * @author     : Ken Chang, Pukunui {@link http://pukunui.com}
 * @license    : http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/formslib.php');
/*
 * Class locationdetail_form extends moodleform
 */
class locationdetail_form extends moodleform {
    /*
     *Function Definition to define Form elements
     */
    public function definition() {
        global $CFG, $DB;
        $mform =& $this->_form;
        $locationid = $this->_customdata['locationid'];
        $confid     = $this->_customdata['confid'];

        $strrequired = get_string('required');

        $confname = $DB->get_field('local_adsafe_conference', 'name', array('id' => $confid));
        
        $mform->addElement('hidden', 'confid', $confid);
        $mform->setType('confid', PARAM_RAW);
        
        $mform->addElement('hidden', 'locationid', $locationid);
        $mform->setType('locationid', PARAM_RAW);
        
        $mform->addElement('text', 'confname', get_string('conferencename', 'local_adsafe'),
        array('size' => 50, 'disabled' => 'disabled'));
        $mform->setType('confname', PARAM_RAW);
        $mform->setDefault('confname', $confname);

        
        $mform->addElement('text', 'locationname', get_string('location', 'local_adsafe'));
        $mform->setType('locationname', PARAM_RAW);
        $mform->addRule('locationname', $strrequired, 'required', null, 'client');

        $mform->addElement('text', 'address', get_string('address', 'local_adsafe'), array('size' => 50));
        $mform->setType('address', PARAM_RAW);
        $mform->addRule('address', $strrequired, 'required', null, 'client');
        
        /*$mform->addElement('text', 'pastorname', get_string('pastorname', 'local_adsafe'), array('size' => 50));
        $mform->setType('pastorname', PARAM_RAW);
        $mform->addRule('pastorname', $strrequired, 'required', null, 'client');*/
        
        /*$mform->addElement('text', 'pastorname', get_string('pastorname', 'local_adsafe'), array('size' => 50));
        $mform->setType('pastorname', PARAM_RAW);
        $mform->addRule('pastorname', $strrequired, 'required', null, 'client');*/
        
        $pastormenu = array('0' => get_string('selectapastor', 'local_adsafe'));//"Select a pastor user"
        $passql = "SELECT u.id, CONCAT( u.lastname,', ', u.firstname) as Pastoruser                     
                   FROM {context} con         
                   JOIN {role_assignments} ra
                   ON con.id = ra.contextid AND con.contextlevel = 10
                   JOIN {role} r
                   ON ra.roleid = r.id          
                   JOIN {user} u 
                   ON u.id = ra.userid               
                   WHERE r.id = 11
                   order by 2";
        if ($getpastoridfields = $DB->get_records_sql_menu($passql, null)) {
            foreach ($getpastoridfields as $pastorid => $pastorname) {
                $pastormenu["$pastorid"] = $pastorname;
            }
        }       
        $pastorselect = $mform->addElement('select', 'pastoruserid', get_string('pastor', 'local_adsafe'), $pastormenu);
        $mform->addRule('pastoruserid', $strrequired, 'required', null, 'client');
        $pastorselect->setSelected(0);
 
        $mform->addElement('date_selector', 'starttime', get_string('starttime', 'local_adsafe'));
        $mform->addRule('starttime', $strrequired, 'required', null, 'client');
        
        $mform->addElement('date_selector', 'endtime', get_string('endtime', 'local_adsafe'));
        $mform->addRule('endtime', $strrequired, 'required', null, 'client');

        if ($locationid) {
          
            
            //$locationdetail = $DB->get_record('local_adsafe_location', array('id' => $locationid));
            
            $locsql = "SELECT lal.name, lal.address, lal.pastoruserid, lal.starttime, lal.endtime
                       FROM {local_adsafe_location} lal
                       WHERE lal.id = :plocationid";
            $locationdetails = $DB->get_records_sql($locsql, array('plocationid' => $locationid));
            
            /*$sql = "SELECT lal.name,lal.address,lacos.starttime,lacos.endtime
                    FROM {local_adsafe_location} lal
                    INNER JOIN {local_adsafe_conference} lac
                    ON lac.id = lal.conferenceid
                    INNER JOIN {local_adsafe_coordenators} lacos
                    ON lacos.locationid = lac.id
                    WHERE lac.id = :locationid";
            $locationdetails = $DB->get_records_sql($sql, array('locationid' => $locationdetail->conferenceid));*/
           
            foreach ($locationdetails as $lds) {
                
                //$confname = $DB->get_field('local_adsafe_conference', 'name',array('id' => $locationdetail->conferenceid));
                $mform->setDefault('locationname', $lds->name);
                $mform->setDefault('address', $lds->address);
                //$mform->setDefault('pastorname', $lds->pastoruserid);
                $pastorselect->setSelected($lds->pastoruserid);
                $mform->setDefault('starttime', $lds->starttime);
                $mform->setDefault('endtime', $lds->endtime);

            }
            
        }
        
        //$mform->addElement('html', "<div class=locdet align=center>");
        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'save', get_string('save', 'local_adsafe'));
        $buttonarray[] = &$mform->createElement('cancel', 'cancel', get_string('cancel', 'local_adsafe'));
        $mform->addGroup($buttonarray, 'buttonar', '&nbsp;', array(''), false);
        //$mform->addElement('html', "</div>");
    }

    /**
     * Function generates the activity courses list in table format.
     *
     * @uses $DB, $CFG
     * @param $locationid integer holds the activityid
     * @return $table object.
     */
    public function local_tellcent_get_course_list($locationid) {
        global $CFG, $DB;
        $sql = "SELECT c.fullname as coursename
                FROM {course} c
                INNER JOIN {local_tellcent_acvtcourse} ac
                ON ac.courseid = c.id
                WHERE ac.activityid = :activityid";
        $activitycourses = $DB->get_records_sql($sql, array('activityid' => $locationid));

        // Create the table headings.
        $table = new html_table();
        $table->width = '100%';
        // Set the row heading object.
        $row = new html_table_row();
        // Create the cell.
        $cell = new html_table_cell();
        $cell->header = true;
        $cell->text = get_string('courselist', 'local_tellcent');
        $cell->style = 'text-align:left';
        $row->cells[] = $cell;
        $table->data[] = $row;

        foreach ($activitycourses as $atc) {
            // Set the row heading object.
            $row = new html_table_row();
            // Create the cell.
            $cell = new html_table_cell();
            $cell->header = true;
            $cell->text = $atc->coursename;
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
        
        /*if($data['locationid']){
            if (!$DB->count_records('local_adsafe_location', array('name' => $data['locationname'], 'id' => $data['locationid']))
            && ($DB->count_records('local_adsafe_location', array('name' => $data['locationname'])))) {
                $errors['locationname'] = get_string('error_duplicatepastor', 'local_adsafe');
            }
        } else {
            if ($DB->count_records('local_adsafe_location', array('name' => $data['locationname']))) {
                $errors['locationname'] = get_string('error_duplicatepastor', 'local_adsafe');
            }
        }*/
        
        if($data['locationid']) { //edit
            /*if($DB->count_records('local_adsafe_location', array('conferenceid' => $data['confid'], 'name' => trim($data['locationname'])))) {
                $errors['locationname'] = get_string('error_duplicatelocationname', 'local_adsafe');
            }*/
            if (empty($data['locationname'])) {
                $errors['locationname'] = get_string('error_emptylocationname', 'local_adsafe');
            }
            
            $nameid = $data['locationid'];
            $namefield = $data['locationname'];
            
            $namesql = "SELECT lal.name
                        FROM {local_adsafe_location} lal
                        WHERE lal.name = '$namefield'
                        AND lal.id <> '$nameid'";
            $namedetails = $DB->get_records_sql($namesql, array('id' => $nameid,'name' => $namefield));
            if($namedetails) {
                $errors['locationname'] = get_string('error_duplicatelocationname', 'local_adsafe');
            }
 
        } else { //New
            if (empty($data['locationname'])) {
                $errors['locationname'] = get_string('error_emptylocationname', 'local_adsafe');
            }
            if($DB->count_records('local_adsafe_location', array('conferenceid' => $data['confid'], 'name' => trim($data['locationname'])))) {
                $errors['locationname'] = get_string('error_duplicatelocationname', 'local_adsafe');
            }
        }

        if ($data['pastoruserid'] <= 0) {
            $errors['pastoruserid'] = get_string('error_invalidpastoruserid','local_adsafe');
        }
        
        if ($data['starttime'] >= $data['endtime']) {
            $errors['starttime'] = get_string('error_starttimemustlessthanendtime','local_adsafe');
        }

        return $errors;
    }
}