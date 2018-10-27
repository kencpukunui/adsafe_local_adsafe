<?php
/*
 * ADSAFE
 *
 * Insert the state records to local_adsafe_state if got no records in it.
 *
 * @package    : local_adsafe
 * @copyright  : 2018 Pukunui
 * @author     : Ken Chang, Pukunui {@link http://pukunui.com}
 * @license    : http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$countstaterecords = $DB->count_records('local_adsafe_state',null);

if($countstaterecords <= 0) { //insert right record in to state table
    // id	name	shortname	captial
    
    $record1 = new stdClass();
    $record1->name       = 'New South Wales';
    $record1->shortname  = 'NSW';
    $record1->captial    = 'Sydney';
    
    $record2 = new stdClass();
    $record2->name       = 'Queensland';
    $record2->shortname  = 'QLD';
    $record2->captial    = 'Brisbane';
    
    $record3 = new stdClass();
    $record3->name       = 'South Australia';
    $record3->shortname  = 'SA';
    $record3->captial    = 'Adelaide';
    
    $record4 = new stdClass();
    $record4->name       = 'Tasmania';
    $record4->shortname  = 'TAS';
    $record4->captial    = 'Hobart';
    
    $record5 = new stdClass();
    $record5->name       = 'Victoria';
    $record5->shortname  = 'VIC';
    $record5->captial    = 'Melbourne';
    
    $record6 = new stdClass();
    $record6->name       = 'Western Australia';
    $record6->shortname  = 'WA';
    $record6->captial    = 'Perth';
    
    $record6 = new stdClass();
    $record6->name       = 'Northern Territory';
    $record6->shortname  = 'NT';
    $record6->captial    = 'Darwin';
    
    $record6 = new stdClass();
    $record6->name       = 'Australian Capital Territory';
    $record6->shortname  = 'ACT';
    $record6->captial    = 'Canberra';
    
    $record6 = new stdClass();
    $record6->name       = 'New Zealand';
    $record6->shortname  = 'NZ';
    $record6->captial    = 'Wellington';
    
    $record6 = new stdClass();
    $record6->name       = 'Not AU/NZ';
    $record6->shortname  = 'non';
    $record6->captial    = 'Other';
    
    $records = array($record1, $record2, $record3, $record4, $record5, $record6);
    $lastinsertid = $DB->insert_records('local_adsafe_state', $records);
}