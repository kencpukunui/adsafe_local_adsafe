<?php
/*
 * ADSAFE
 *
 * Form to display, edit and delete the coordinators.
 *
 * @package    : local_adsafe
 * @copyright  : 2018 Pukunui
 * @author     : Ken Chang, Pukunui {@link http://pukunui.com}
 * @license    : http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/outputrenderers.php');
/*
 * Class coordinatorlist_form extends moodleform
 */
class coordinatorlist_form extends moodleform {
    /*
     *Function Definition to define Form elements
     */
    public function definition() {
        //defined global parameter
        global $CFG, $DB;
        
        // Form initial
        $mform =& $this->_form;
        
        // When user made one or multiple wrong choosen for this page, show this string to them
        $strrequired = get_string('required');
        
        // Get outside values when the coordinatorlist.php call function to new this form
        $id   = $this->_customdata['id'];
        $role = $this->_customdata['role'];
        
        // Get passed value from URLS (ex: if URLS contained a string locid=8, then $locid will get a interger value 8)
        $locid = optional_param('locid', '', PARAM_INT);
        
        // Create an invisible textbox field (this field is used to determine display page with effective userid or non userid)
        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_RAW);

        // defined an array to store the preview location id
        $copylocationindex = array();

        // Check the $role passed from outside
        if ($role == 'Pastor' || $role == 'Co-ordinator') {
            // Check this user belongs to one or multiple churchs (he may be a pastor in church-A also a coordinator in church-B)
            // when user choose endtime at 01/08/2018 on the screen, the stored timestamp will be 01/08/2018 00:00:00, so I add 86400 secs
            // the timestamp will imcrease to 02/08/2018 00:00:00, and it's exactly the right time
            $locationsql = "SELECT lal1.id,lal1.name
                            FROM {local_adsafe_location} lal1
                            JOIN
                            (SELECT lal.id
                             FROM {local_adsafe_location} lal
                             WHERE lal.pastoruserid = $id
                             AND (UNIX_TIMESTAMP() >= lal.starttime) 
                             AND ((UNIX_TIMESTAMP() <= lal.endtime) OR (lal.endtime IS NULL))
                             UNION ALL
                             SELECT lac.locationid
                             FROM {local_adsafe_coordinators} lac
                             WHERE lac.userid = $id
                             AND (UNIX_TIMESTAMP() >= lac.starttime) 
                             AND ((UNIX_TIMESTAMP() <= (lac.endtime + 86400)) OR (lac.endtime IS NULL)))
                             AS A ON A.id = lal1.id
                             GROUP BY 1,2
                             ORDER BY 2";
            if ($getlocationidfields = $DB->get_records_sql_menu($locationsql,null)) {
                foreach ($getlocationidfields as $locationid => $locationname) {
                    $locationmenu["$locationid"] = $locationname;
                    // put the location id into the array for this page pre-loading
                    $copylocationindex[] = $locationid;
                }
            }
        } else if ($role == 'admin'){
            // Get all church id because it's admin user
            $locationsql = "SELECT lal.id, lal.name
                            FROM {local_adsafe_location} lal
                            ORDER BY 2";
            if($getlocationidfields = $DB->get_records_sql_menu($locationsql,null)) {
                foreach ($getlocationidfields as $locationid => $locationname) {
                    $locationmenu["$locationid"] = $locationname;
                    // put the location id into the array for this page pre-loading
                    $copylocationindex[] = $locationid;
                }
            }
        }
        
        // Location drop-down box
        $locationselect = $mform->addElement('select', 'locationid', get_string('location', 'local_adsafe'), $locationmenu);
        $mform->addRule('locationid', $strrequired, 'required', null, 'client');
        $mform->setType('locationid', PARAM_RAW);

        // Put two button in same group will display them at same line
        //$mform->addElement('html', "<div class=buttongup align=center>");
        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'searchlocation', get_string('search', 'local_adsafe'));
        $buttonarray[] = &$mform->createElement('submit', 'memberpage', get_string('member', 'local_adsafe'));
        $mform->addGroup($buttonarray, 'buttonar', '&nbsp;', array(''), false);

        // if the user first time get into this page $locid will return false, so I will pre-loading the first location for them
        if($copylocationindex[0] && !$locid) {
            $locationselect->setSelected($copylocationindex[0]); // set the right location for dropdown box
            $mform->addElement('html', $this->local_adsafe_get_coordinatorlist_table($copylocationindex[0])); // display coordinator list
            $mform->addElement('submit', 'newcoordinator', get_string('newcoordinator', 'local_adsafe'));
            //echo("First get in for coordinatorlist");
        } 
        // When they choose a location and press the search button, the $locid will return true
        else if($copylocationindex[0] && $locid) {
            $locationselect->setSelected($locid); // set the right location for dropdown box
            $mform->addElement('html', $this->local_adsafe_get_coordinatorlist_table($locid)); // display coordinator list
            $mform->addElement('submit', 'newcoordinator', get_string('newcoordinator', 'local_adsafe'));
            //echo("After pressed Search button for coordinatorlist");
        }
        //$mform->addElement('html', "</div>");
    }
    /**
     * Function generates the coordinator list in table format for users.
     *
     * @uses $DB, $CFG
     * @return $table object.
     */
    public function local_adsafe_get_coordinatorlist_table($locationid) {
        global $DB, $CFG;
        // Create the table headings.
        $table = new html_table();
        $table->width = '100%';
        // Set the row heading object.
        $row = new html_table_row();
        // Create the cell.
        $cell = new html_table_cell();
        $cell->header = true;
        $cell->text = get_string('name', 'local_adsafe');
        $cell->style = 'text-align:left';
        $row->cells[] = $cell;
        // Create the cell.
        $cell = new html_table_cell();
        $cell->header = true;
        $cell->text = get_string('starttime', 'local_adsafe');
        $cell->style = 'text-align:left';
        $row->cells[] = $cell;
        // Create the cell.
        $cell = new html_table_cell();
        $cell->header = true;
        $cell->text = get_string('endtime', 'local_adsafe');
        $cell->style = 'text-align:left';
        $row->cells[] = $cell;
        // Create the cell.
        $cell = new html_table_cell();
        $cell->header = true;
        $cell->text = get_string('action', 'local_adsafe');
        $cell->style = 'text-align:left';
        $row->cells[] = $cell;
        $table->data[] = $row;
        // get the right coordinator records from database when location id is match
        $coordinatorsql = "SELECT lac.id,lac.locationid,lac.userid,
                           DATE_FORMAT(FROM_UNIXTIME(lac.starttime), '%d-%m-%Y') as starttime,
                           (CASE WHEN lac.endtime IS NULL THEN 'NULL' 
                            ELSE DATE_FORMAT(FROM_UNIXTIME(lac.endtime), '%d-%m-%Y') 
                            END) AS endtime,
                           CONCAT(u.lastname, ', ', u.firstname) as name
                           FROM {local_adsafe_coordinators} lac
                           JOIN {user} u
                           ON u.id = lac.userid
                           WHERE lac.locationid = $locationid
                           ORDER BY 2";
        $coordinatorrecords = $DB->get_records_sql($coordinatorsql);
        foreach ($coordinatorrecords as $crs) {
            $editlink = "<a href='$CFG->wwwroot/local/adsafe/coordinatoredit_index.php?locid=$crs->locationid&id=$crs->id&action=edit'>"
            . get_string('edit', 'local_adsafe')."</a>";
            $deletelink = "<a href='$CFG->wwwroot/local/adsafe/coordinatorlist_index.php?locid=$crs->locationid&id=$crs->id&action=delete'>"
            . get_string('delete', 'local_adsafe')."</a>";
            // Set the row heading object.
            $row = new html_table_row();
            // Create the cell.
            $cell = new html_table_cell();
            $cell->header = true;
            $cell->text = $crs->name;
            $row->cells[] = $cell;
            // Create the cell.
            $cell = new html_table_cell();
            $cell->header = true;
            //$cell->text = date('d-m-Y',$crs->starttime);
            $cell->text = $crs->starttime;
            $row->cells[] = $cell;
            // Create the cell.
            $cell = new html_table_cell();
            $cell->header = true;
            $cell->text = $crs->endtime;
            $row->cells[] = $cell;
            // Create the cell.
            $cell = new html_table_cell();
            $cell->header = true;
            $cell->text = $editlink .' '. $deletelink;
            $row->cells[] = $cell;
            // Add header to the table.
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
     public function validation($data,$files) {
        global $DB;
        $errors = parent::validation($data,$files);
        // This error won't happen because all users can not select the not-exists options of the location area on the screen
        if ($data['locationid'] <= 0) { //Non selected a valid location
            $errors['locationid'] = get_string('error_nonselectlocation', 'local_adsafe');
        }
        return $errors;
    }
}