<?php
/*
 * ADSAFE
 *
 * Form to display conference list page.
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
 * Class conferencelist_form extends moodleform
 */
class conferencelist_form extends moodleform {
    /*
     *Function Definition to define Form elements
     */
    public function definition() {
        global $CFG, $DB;
        $mform =& $this->_form;
        $mform->addElement('html', $this->local_adsafe_get_conferlist_table());
        //$mform->addElement('html', "<div class=addcon align=center valign=center>");
        $mform->addElement('submit', 'addnewcon', get_string('addconference', 'local_adsafe')); //'addprofgroup'
        //$mform->addElement('html', "</div>");
    }

    /**
     * Function generates the conference list in table format fro admin users.
     *
     * @uses $DB, $CFG
     * @return $table object.
     */
    public function local_adsafe_get_conferlist_table() {
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
        $cell->text = get_string('state', 'local_adsafe');
        $cell->style = 'text-align:left';
        $row->cells[] = $cell;
        // Create the cell.
        $cell = new html_table_cell();
        $cell->header = true;
        $cell->text = get_string('conference', 'local_adsafe');
        $cell->style = 'text-align:left';
        $row->cells[] = $cell;
        // Create the cell.
        $cell = new html_table_cell();
        $cell->header = true;
        $cell->text = get_string('action', 'local_adsafe');
        $cell->style = 'text-align:left';
        $row->cells[] = $cell;
        $table->data[] = $row;
        $sql = "SELECT lac.id, lac.name, las.shortname, CONCAT(u.lastname,', ',u.firstname) as fullname
                FROM mdl_local_adsafe_conference lac
                JOIN mdl_local_adsafe_state las
                ON las.id = lac.stateid
                JOIN mdl_user u
                ON u.id = lac.conferenceuserid
                ORDER BY 2";
        $conferencerecords = $DB->get_records_sql($sql);
        foreach ($conferencerecords as $cff) {
            $editlink = "<a href='$CFG->wwwroot/local/adsafe/conferenceedit_index.php?id=$cff->id&action=edit'>"
            . get_string('edit', 'local_adsafe')."</a>";//this page haven't create yet 20062018
            $deletelink = "<a href='$CFG->wwwroot/local/adsafe/conferencelist_index.php?id=$cff->id&action=delete'>"
            . get_string('delete', 'local_adsafe')."</a>";
            // Set the row heading object.
            $row = new html_table_row();
            // Create the cell.
            $cell = new html_table_cell();
            $cell->header = true;
            $cell->text = $cff->name;
            $row->cells[] = $cell;
            // Create the cell.
            $cell = new html_table_cell();
            $cell->header = true;
            $cell->text = $cff->shortname;
            $row->cells[] = $cell;
            // Create the cell.
            $cell = new html_table_cell();
            $cell->header = true;
            $cell->text = $cff->fullname;
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
}