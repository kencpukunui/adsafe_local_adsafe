<?php
/**
 * ADSAFE Church and event
 *
 * Utility functions.
 *
 * @package    local_adsafe
 * @author     Ken Chang (@link kenc@pukunui.com)
 * @copyright  2018 Pukunui (@link https://pukunui.com/)
 * @license    https://www/gnu.org/copyleft/copyleft/gpl.html GNU GPL v3 or later
 */
 
namespace local_adsafe;

defined('MOODLE_INTERNAL') || die();

/**
 * Utility functions.
 *
 * @package    local_adsafe
 * @author     Ken Chang (@link kenc@pukunui.com)
 * @copyright  2018 Pukunui (@link https://pukunui.com/)
 * @license    https://www/gnu.org/copyleft/copyleft/gpl.html GNU GPL v3 or later
 */
 
class utils {
    /**
     * @const confirmation options Please select
     */
    const OPTION_PLEASE_SELECT = -1;
    
    /**
     * @const confirmation options Does not attend this church
     */
    const OPTION_DOES_NOT_ATTEND_THIS_CHURCH = 1;
    
    /**
     * @const confirmation options Attends this church
     */
    const OPTION_ATTENDS_THIS_CHURCH = 2;
    
    /**
     * @const confirmation options Unknown need to check
     */
    const OPTION_UNKNOWN_NEED_TO_CHECK = 0;
    
    
    /**
     * @const role confirmed Please select
     */
    const OPTION_ROLE_CONFIRMED_PLEASE_SELECT = -2;
    
    /**
     * @const role confirmed Unknown need to check
     */
    const OPTION_ROLE_UNKNOWN_NEED_TO_CHECK = 0;
    
    /**
     * @const role confirmed Role active in this role
     */
    const OPTION_ROLE_ACTIVE_IN_THIS_ROLE = 2;
    
    /**
     * @const role confirmed Member not in this role
     */
    const OPTION_MEMBER_NOT_IN_THIS_ROLE = 1;
    
    
    
    
    /**
     * @const role options Please select
     */
    const OPTION_ROLE_PLEASE_SELECT = 0;
    
    /**
     * @const role options ADRA
     */
    const OPTION_ROLE_ADRA = 1;
    
    /**
     * @const role options Adventures
     */
    const OPTION_ROLE_ADVENTURERS = 2;
    
    /**
     * @const role options Board member
     */
    const OPTION_ROLE_BOARD_MEMBER = 3;
    
    /**
     * @const role options Chaplain
     */
    const OPTION_ROLE_CHAPLAIN = 4;
    
    /**
     * @const role options Childrens ministries
     */
    const OPTION_ROLE_CHILDRENS_MINISTRIES = 5;


    /**
     * @const verification outcome option Please select of wwc
     */
    const OPTION_OUTCOME_PLEASE_SELECT = 0;

    /**
     * @const verification outcome option Application in Progress of wwc
     */
    const OPTION_OUTCOME_APPLICATION_IN_PROGRESS = 1;
    
    /**
     * @const verification outcome option Cleared of wwc
     */
    const OPTION_OUTCOME_CLEARED = 2;
    
    /**
     * @const verification outcome option Barred of wwc
     */
    const OPTION_OUTCOME_BARRED = 3;
    
    /**
     * @const verification outcome option Interim bar of wwc
     */
    const OPTION_OUTCOME_INTERIM_BAR = 4;
    

    /**
     * Save information for new church and event.
     * We get the data from the submitted form.
     *
     * @return boolean
     */
    public static function church_event_add($userid) {
        global $DB;
        $getmain = new \stdClass; 
        $addnewchurchandeventform = new \local_adsafe\forms\churchsandeventsaddform();
        
        if ($data = $addnewchurchandeventform->get_data() and confirm_sesskey()) {
                //var_dump($data);
            /*return (boolean)$DB->insert_record('local_adsafe_member', array('locationid' => $data->locid,
                                                                            'userid'     => $userid,
                                                                            'starttime'  => $data->startdate,
                                                                            'confirmed'  => 0,
                                                                            'main'       => $data->main
                                                                            )
                                              );*/

            
            $cntduplicatesql = "SELECT COUNT(*) AS CNT
                                FROM mdl_local_adsafe_member lam
                                WHERE lam.userid = $userid
                                AND lam.locationid = $data->locid";
            
            $getcntcntrecord = $DB->get_field_sql($cntduplicatesql,null);
            
            if($getcntcntrecord > 0) {
                return false;
            } else {
                return (boolean)$newrecord = $DB->insert_record('local_adsafe_member', array('locationid' => $data->locid,
                                                                                         'userid'     => $userid,
                                                                                         'starttime'  => $data->startdate,
                                                                                         'confirmed'  => 0,
                                                                                         'main'       => $data->mainchurch
                                                                                        )
                                                           );                               
                // use the new max id above as the condition to retrive another member id in member table                                  
                $sql = "SELECT lam.id,lam.main
                        FROM mdl_local_adsafe_member lam
                        JOIN (SELECT MAX(lam2.id) as id
                        FROM mdl_local_adsafe_member lam2
                        WHERE lam2.main <> 0
                        AND lam2.userid = $userid) as x
                        ON x.id <> lam.id
                        WHERE lam.main <> 0
                        AND lam.userid = $userid";
            
                if ($record = $DB->get_records_sql($sql,null)) {
                    foreach ($record as $rec) {
                         $DB->update_record('local_adsafe_member', array('id' => $rec->id, 'main' => 0));
                    }
                    return true;
                   //return $DB->update_record('local_adsafe_member', array('id' => $memid, 'main' => 1));
                }
            }
            
            
            
                                             
                                              
        }
        //return false;
    }

    /**
     * Return an array of the human readable item status.
     *
     * @return array
     */
    public static function get_all_conferences() {
        global $DB;
        
        $sql = "SELECT  lac.id, lac.name
                FROM mdl_local_adsafe_conference lac
                ORDER BY lac.name";
        if($getsql = $DB->get_records_sql_menu($sql,null)) {
                foreach ($getsql as $id => $name) {
                    $conferencemenu["$id"] = $name;
                }
        }
        return $conferencemenu;
    }
    
    public static function get_regarding_church_event_from_conferenceid($conid) {
        global $DB;
        $today = time();
        $sql = "SELECT lal.id,lal.name
                FROM {local_adsafe_location} lal
                WHERE lal.conferenceid = $conid
                AND lal.endtime > $today
                ORDER BY lal.name";
        $getsql = $DB->get_records_sql($sql,null);
        return $getsql;   
        /*if($getsql = $DB->get_records_sql_menu($sql,null)) {
                foreach ($getsql as $id => $name) {
                    $churcheventmenu["$id"] = $name;
                }
        }
        return $churcheventmenu;*/
        
    }
    
    /**
     * Delete the given church / event.
     *
     * @param int $memid
     * @return boolean
     */
    public static function church_event_delete($memid) {
        global $DB;
        if (confirm_sesskey()) {
            $getsql = $DB->get_record('local_adsafe_member', array('id' => $memid));
            if($getsql) {
                return $DB->delete_records('local_adsafe_member', array('id' => $memid));
            } else {
                return false;
            }
        }
        return false;
    }
    
    /**
     * Change the active status of a church / event.
     *
     * @param int $memid
     * @return boolean
     */
    public static function church_event_active_change_status($memid) {
        global $DB;

        if ($record = $DB->get_record('local_adsafe_member', array('id' => $memid), 'id, activated, preactivated')) {
            $record->activated = (($record->activated + 1) % 2);
            
            $record->preactivated = 0;
            return $DB->update_record('local_adsafe_member', $record);
        }
        return false;
    }
    
    /**
     * Change the main status of a church / event.
     *
     * @param int $memid
     * @return boolean
     */
    public static function church_event_main_change_status($memid) {
        global $DB;
        
        $sql = "SELECT lam.id,lam.main
                FROM {local_adsafe_member} lam
                WHERE lam.main <> 0
                AND lam.id <> $memid";
        
        if ($record = $DB->get_records_sql($sql,null)) {
            foreach ($record as $rec) {
                $DB->update_record('local_adsafe_member', array('id' => $rec->id, 'main' => 0));
            }
            
            return $DB->update_record('local_adsafe_member', array('id' => $memid, 'main' => 1));
        } else {
            if ($record = $DB->get_record('local_adsafe_member', array('id' => $memid), 'id, main')) {
                $record->main = (($record->main + 1) % 2);
                return $DB->update_record('local_adsafe_member', $record);
            }
        }
    }
    
    
    public static function get_record_from_memberid($memid) {
        global $DB;
        
        $sql = "SELECT lac.name as conferencename, lal.name, lam.starttime, lam.main, lam.activated
                FROM {local_adsafe_member} lam
                JOIN {local_adsafe_location} lal
                ON lam.locationid = lal.id
                JOIN {local_adsafe_conference} lac
                ON lal.conferenceid = lac.id
                WHERE lam.id = $memid";
        $getsql = $DB->get_record_sql($sql,null);
        return $getsql;  
    }
    
    public static function update_record_through_memberid($data) {
        global $DB;
        
        if($data) {
            
            // Only one main church
            if($data->maincheckbox == 1) {
                $sql = "SELECT lam.id,lam.main
    			        FROM {local_adsafe_member} lam
    			        WHERE lam.main <> 0
    			        AND lam.id <> $data->memid";
                if ($record = $DB->get_records_sql($sql,null)) {
            		foreach ($record as $rec) {
            			$DB->update_record('local_adsafe_member', array('id' => $rec->id, 'main' => 0));
            		}
        	    }
            }
            
            $dbdata = new \stdClass;
            $dbdata->id = $data->memid;
            $dbdata->starttime = $data->starttime;
            $dbdata->activated = $data->activatecheckbox;
            $dbdata->main = $data->maincheckbox;
            
            return (boolean)$DB->update_record('local_adsafe_member', $dbdata);
        }
        return null;
    }
    
    public static function get_all_roles() {
        global $DB;
        
        $sql = "SELECT  lar.id, lar.name
                FROM mdl_local_adsafe_role lar
                WHERE lar.active = 1
                ORDER BY lar.name";
        if($getsql = $DB->get_records_sql_menu($sql,null)) {
                foreach ($getsql as $id => $name) {
                    $rolemenu["$id"] = $name;
                }
        }
        return $rolemenu;
    }
   
    public static function get_all_location($userid) {
        global $DB;
        $sql = "SELECT lal.id,lal.name
                FROM {local_adsafe_location} lal
                JOIN {local_adsafe_member} lam
                ON lam.locationid = lal.id
                WHERE lam.userid = $userid";

        if($getsql = $DB->get_records_sql_menu($sql,null)) {
            foreach ($getsql as $id => $name) {
                $locationmenu["$id"] = $name;
            }
        }
        return $locationmenu;
    }
    
    /**
     * Save information for new church and event.
     * We get the data from the submitted form.
     *
     * @return boolean
     */
    public static function church_role_add($userid) {
        global $DB;

        $addroleform = new \local_adsafe\forms\churchroleform('',array('userid'=>$userid));
        
        if ($data = $addroleform->get_data() and confirm_sesskey()) {
                //var_dump($data);
            /*return (boolean)$DB->insert_record('local_adsafe_member', array('locationid' => $data->locid,
                                                                            'userid'     => $userid,
                                                                            'starttime'  => $data->startdate,
                                                                            'confirmed'  => 0,
                                                                            'main'       => $data->main
                                                                            )
                                              );*/
            
            if($DB->get_record('local_adsafe_member_role', array('userid'     => $userid, 
                                                                 'roleid'     => $data->roleid, 
                                                                 'locationid' => $data->locid))) {
               return false;
            } else {
                return (boolean)$DB->insert_record('local_adsafe_member_role', array('userid'           => $userid,
                                                                                     'roleid'           => $data->roleid,
                                                                                     'locationid'       => $data->locid,
                                                                                     'active'           => $data->activecheckbox,
                                                                                     'startdate'        => time(),
                                                                                     'enddate'          => NULL,
                                                                                     'confirmed'        => 0,
                                                                                     'wwcneededindex'   => 0,
                                                                                     'dateupdated'      => NULL,
                                                                                     'confirmeduserid'  => NULL,
                                                                                     'comments'         => NULL
                                                                                    )
                                                  );
            }
        }
        return false;
    }
    
    /**
     * Delete the given roles.
     *
     * @param int $roleid
     * @return boolean
     */
    public static function my_roles_delete($roleid) {
        global $DB;
        if (confirm_sesskey()) {
            $getsql = $DB->get_record('local_adsafe_member_role', array('id' => $roleid));
            if($getsql) {
                return $DB->delete_records('local_adsafe_member_role', array('id' => $roleid));
            } else {
                return false;
            }
        }
        return false;
    }
    
    /**
     * Change the active status of my roles.
     *
     * @param int $roleid
     * @return boolean
     */
    public static function my_role_active_change_status($roleid) {
        global $DB;

        if ($record = $DB->get_record('local_adsafe_member_role', array('id' => $roleid), 'id, active')) {
            $record->active = (($record->active + 1) % 2);
            return $DB->update_record('local_adsafe_member_role', $record);
        }
        return false;
    }
    
    public static function update_record_through_roleid($data) {
        global $DB;
        if($data) {
            
            $dbdata = new \stdClass;
            $dbdata->id = $data->roleid;
           // $dbdata->startdate= $data->startdate;
          //  $dbdata->enddate= $data->enddate;
            $dbdata->active = $data->activatecheckbox;
            
            return (boolean)$DB->update_record('local_adsafe_member_role', $dbdata);
        }
    }
    
    public static function get_record_from_roleid($roleid) {
        global $DB;
        
        $sql = "SELECT lamr.id, lar.name as rolename, lal.name as locationname,lamr.startdate,lamr.enddate,lamr.active
                FROM {local_adsafe_member_role} lamr
                JOIN {local_adsafe_role} lar
                ON lamr.roleid = lar.id
                JOIN {local_adsafe_location} lal
                ON lamr.locationid = lal.id
                WHERE lamr.id = $roleid";

        $getsql = $DB->get_record_sql($sql,null);
        return $getsql;  
    }
    
    public static function get_wwc_record_from_userid($userid) {
        global $DB;
        
        $sql = "SELECT lasu.id as id, lasu.spsuserid as spsuserid, lasu.wwcindicator as wwcindicator, lasu.stateid, lasu.nameoncard, lasu.dateofbirth, lasu.cardnumber, lasu.expirydate, lasu.timecreated
                FROM {local_adsafe_sps_user} lasu
                JOIN {local_adsafe_state} las
                ON lasu.stateid = las.id
                WHERE lasu.spsuserid = $userid";

        $getsql = $DB->get_record_sql($sql,null);
        return $getsql; 
    }
    
    public static function get_all_state() {
        global $DB;
        
        $sql = "SELECT las.id, las.shortname
                FROM {local_adsafe_state} las
                ORDER BY las.name";
        if($getsql = $DB->get_records_sql_menu($sql,null)) {
                foreach ($getsql as $id => $name) {
                    $statemenu["$id"] = $name;
                }
        }
        return $statemenu;
    }
    
    public static function update_record_through_userid($data) {
        global $DB;
        /*object(stdClass)#5614 (8) 
        { ["id"]=> string(3) "106" 
          ["wwcindicator"]=> string(1) "1" 
          ["state"]=> string(1) "2" 
          ["nameoncard"]=> string(9) "Ken Chang"
          ["dateofbirth"]=> string(10) "1538013507"
          ["cardnumber"]=> string(10) "554356824" 
          ["expirydate"]=> int(1537372800) 
          ["timecreated"]=> string(10) "1538013507" 
          ["submitbutton"]=> string(4) "Save" 
        }*/

        if($data) { /*&& $data->submitbutton == 'Save'*/

            $wwcsql = "SELECT lasu.*
                       FROM mdl_local_adsafe_sps_user lasu
                       WHERE lasu.spsuserid = $data->spsuserid";
            
            $getwwcrecord = $DB->get_record_sql($wwcsql,null);

            if($getwwcrecord) {
                
                
                
                $presql = "SELECT lam.id,lam.locationid,lam.activated,lam.preactivated,COUNT(lam.id) as differentstatus
                           FROM mdl_local_adsafe_member lam
                           WHERE lam.activated = 1
                           AND lam.activated<>lam.preactivated
                           AND lam.userid = $data->spsuserid";
                $getcounter = $DB->get_record_sql($presql,null);
    
                if($getcounter->differentstatus > 0) {
                    // compare any changed fields
                    if(($getwwcrecord->wwcindicator != $data->wwcindicator) ||
                       ($getwwcrecord->stateid      != $data->state)        ||
                       ($getwwcrecord->nameoncard   != $data->nameoncard)   ||
                       ($getwwcrecord->dateofbirth  != $data->dateofbirth)  ||
                       ($getwwcrecord->cardnumber   != $data->cardnumber)   ||
                       ($getwwcrecord->expirydate   != $data->expirydate)   || 
                        $getcounter->differentstatus > 0) {
                            
                        // update existing record
                        $update = new \stdClass;
                        $update->id           = $data->id;
                        $update->spsuserid    = $data->spsuserid;
                        $update->wwcindicator = $data->wwcindicator;
                        $update->stateid      = $data->state;
                        $update->nameoncard   = $data->nameoncard;
                        $update->dateofbirth  = $data->dateofbirth;
                        $update->cardnumber   = $data->cardnumber;
                        $update->expirydate   = $data->expirydate;
                        $update->timecreated  = time();
                        $DB->update_record('local_adsafe_sps_user', $update);
    
                        // wwc verified
                        $memsql = "SELECT lam.id,lam.locationid
                                   FROM mdl_local_adsafe_member lam
                                   WHERE lam.activated = 1
                                   AND lam.activated<>lam.preactivated
                                   AND lam.userid = $data->spsuserid";
    
                        if($getsql = $DB->get_records_sql_menu($memsql,null)) {
                            foreach ($getsql as $id => $locationid) {
                                // Snapshot for table mdl_local_adsafe_wwc_verify
                                $snapdata = new \stdClass;
                                $snapdata->spsid         = $data->spsuserid;
                                $snapdata->wwcindicator  = $data->wwcindicator;
                                $snapdata->stateid       = $data->state;
                                $snapdata->nameoncard    = $data->nameoncard;
                                $snapdata->dateofbirth   = $data->dateofbirth;
                                $snapdata->cardnumber    = $data->cardnumber;
                                $snapdata->expirydate    = $data->expirydate;
                                $snapdata->locationid    = $locationid;
                                $snapdata->timecreated   = time();
                                $DB->insert_record('local_adsafe_wwc_verify', $snapdata);
                            }
                        }
                        
                            $updatememsql = "SELECT lam.id,lam.preactivated
                                             FROM {local_adsafe_member} lam
                                             WHERE lam.activated = 1
                                             AND lam.activated<>lam.preactivated
                                             AND lam.userid = $data->spsuserid";
          
                            if ($record = $DB->get_records_sql($updatememsql,null)) {
                                foreach ($record as $rec) {
                                    $DB->update_record('local_adsafe_member', array('id' => $rec->id, 'preactivated' => 1));
                                }
                            }
                    } else {
                        // do nothing
                        //echo('DOOOOO NOTHING');
                    }
                } else if ($getcounter->differentstatus == NULL || $getcounter->differentstatus == 0) {
                    // update existing record
                    $update = new \stdClass;
                    $update->id           = $data->id;
                    $update->spsuserid    = $data->spsuserid;
                    $update->wwcindicator = $data->wwcindicator;
                    $update->stateid      = $data->state;
                    $update->nameoncard   = $data->nameoncard;
                    $update->dateofbirth  = $data->dateofbirth;
                    $update->cardnumber   = $data->cardnumber;
                    $update->expirydate   = $data->expirydate;
                    $update->timecreated  = time();
                    $DB->update_record('local_adsafe_sps_user', $update);

                    // wwc verified
                    $memsql = "SELECT lam.id,lam.locationid
                               FROM mdl_local_adsafe_member lam
                               WHERE lam.activated = 1
                               AND lam.activated<>lam.preactivated
                               AND lam.userid = $data->spsuserid";
                    
                    /*$memsql = "SELECT lam.id,lam.locationid
                               FROM mdl_local_adsafe_member lam
                               WHERE lam.activated = 1
                               
                               AND lam.userid = $data->spsuserid";*/


                    if($getsql = $DB->get_records_sql_menu($memsql,null)) {
                        foreach ($getsql as $id => $locationid) {
                            // Snapshot for table mdl_local_adsafe_wwc_verify
                            $snapdata = new \stdClass;
                            $snapdata->spsid         = $data->spsuserid;
                            $snapdata->wwcindicator  = $data->wwcindicator;
                            $snapdata->stateid       = $data->state;
                            $snapdata->nameoncard    = $data->nameoncard;
                            $snapdata->dateofbirth   = $data->dateofbirth;
                            $snapdata->cardnumber    = $data->cardnumber;
                            $snapdata->expirydate    = $data->expirydate;
                            $snapdata->locationid    = $locationid;
                            $snapdata->timecreated   = time();
                            $DB->insert_record('local_adsafe_wwc_verify', $snapdata);
                        }
                    }
                    
                        /*$updatememsql = "SELECT lam.id,lam.preactivated
                                         FROM {local_adsafe_member} lam
                                         WHERE lam.activated = 1
                                         AND lam.activated<>lam.preactivated
                                         AND lam.userid = $data->spsuserid";
      
                        if ($record = $DB->get_records_sql($updatememsql,null)) {
                            foreach ($record as $rec) {
                                $DB->update_record('local_adsafe_member', array('id' => $rec->id, 'preactivated' => 1));
                            }
                        
                      
                        }*/
                   
                }
                
                
                                    // compare any changed fields
                    if(($getwwcrecord->wwcindicator != $data->wwcindicator) ||
                       ($getwwcrecord->stateid      != $data->state)        ||
                       ($getwwcrecord->nameoncard   != $data->nameoncard)   ||
                       ($getwwcrecord->dateofbirth  != $data->dateofbirth)  ||
                       ($getwwcrecord->cardnumber   != $data->cardnumber)   ||
                       ($getwwcrecord->expirydate   != $data->expirydate)) {
                            
                        // update existing record
                        $update = new \stdClass;
                        $update->id           = $data->id;
                        $update->spsuserid    = $data->spsuserid;
                        $update->wwcindicator = $data->wwcindicator;
                        $update->stateid      = $data->state;
                        $update->nameoncard   = $data->nameoncard;
                        $update->dateofbirth  = $data->dateofbirth;
                        $update->cardnumber   = $data->cardnumber;
                        $update->expirydate   = $data->expirydate;
                        $update->timecreated  = time();
                        $DB->update_record('local_adsafe_sps_user', $update);
    
                        // wwc verified
                        $memsql = "SELECT lam.id,lam.locationid
                                   FROM mdl_local_adsafe_member lam
                                   WHERE lam.activated = 1
                                   AND lam.userid = $data->spsuserid";
    
                        if($getsql = $DB->get_records_sql_menu($memsql,null)) {
                            foreach ($getsql as $id => $locationid) {
                                // Snapshot for table mdl_local_adsafe_wwc_verify
                                $snapdata = new \stdClass;
                                $snapdata->spsid         = $data->spsuserid;
                                $snapdata->wwcindicator  = $data->wwcindicator;
                                $snapdata->stateid       = $data->state;
                                $snapdata->nameoncard    = $data->nameoncard;
                                $snapdata->dateofbirth   = $data->dateofbirth;
                                $snapdata->cardnumber    = $data->cardnumber;
                                $snapdata->expirydate    = $data->expirydate;
                                $snapdata->locationid    = $locationid;
                                $snapdata->timecreated   = time();
                                $DB->insert_record('local_adsafe_wwc_verify', $snapdata);
                            }
                        }
                        
                            $updatememsql = "SELECT lam.id,lam.preactivated
                                             FROM {local_adsafe_member} lam
                                             WHERE lam.activated = 1
                                             AND lam.activated<>lam.preactivated
                                             AND lam.userid = $data->spsuserid";
          
                            if ($record = $DB->get_records_sql($updatememsql,null)) {
                                foreach ($record as $rec) {
                                    $DB->update_record('local_adsafe_member', array('id' => $rec->id, 'preactivated' => 1));
                                }
                            }
                    } else {
                        // do nothing
                        //echo('DOOOOO NOTHING');
                    }
    
             

            } else {
                // insert new record
                $bbdata = new \stdClass;
                $bbdata->spsuserid    = $data->spsuserid;
                $bbdata->wwcindicator = $data->wwcindicator;
                $bbdata->stateid      = $data->state;
                $bbdata->nameoncard   = $data->nameoncard;
                $bbdata->dateofbirth  = $data->dateofbirth;
                $bbdata->cardnumber   = $data->cardnumber;
                $bbdata->expirydate   = $data->expirydate;
                $bbdata->timecreated  = time();
                $DB->insert_record('local_adsafe_sps_user', $bbdata);

                // wwc verified
                $memsql = "SELECT lam.id,lam.locationid
                           FROM mdl_local_adsafe_member lam
                           WHERE lam.activated = 1
                           AND lam.userid = $data->spsuserid";
                if($getsql = $DB->get_records_sql_menu($memsql,null)) {
                    foreach ($getsql as $id => $locationid) {
                        
                        // Snapshot for table mdl_local_adsafe_wwc_verify
                        $snapdata = new \stdClass;
                        $snapdata->spsid         = $data->spsuserid;
                        $snapdata->wwcindicator  = $data->wwcindicator;
                        $snapdata->stateid       = $data->state;
                        $snapdata->nameoncard    = $data->nameoncard;
                        $snapdata->dateofbirth   = $data->dateofbirth;
                        $snapdata->cardnumber    = $data->cardnumber;
                        $snapdata->expirydate    = $data->expirydate;
                        $snapdata->locationid    = $locationid;
                        $snapdata->timecreated   = time();
                        $DB->insert_record('local_adsafe_wwc_verify', $snapdata);
                    }
                }
            }
        }
        return null;
    }

    public static function check_coordinator_user_through_userid($userid) {
        global $DB;
        $cosql = "SELECT COUNT(*) AS CNT
                  FROM {local_adsafe_coordinators} lac
                  WHERE lac.userid = $userid
                  AND (UNIX_TIMESTAMP() >= lac.starttime) 
                  AND ((UNIX_TIMESTAMP() <= (lac.endtime + 86400)) OR (lac.endtime IS NULL))";
        $getcosql = $DB->get_field_sql($cosql);
        return $getcosql;
    }
    
    public static function get_location_record_from_userid_and_role($userid,$role){
        global $DB;
        /* //This line will let location pre-loading become invalid, don't do this unless thay want us to do...
        $locationmenu = array('0' => get_string('selectchurchoreven', 'local_adsafe'));
        */
        switch ($role) {
            case 'admin':
            $locsql = "SELECT lal.id,lal.name
                       FROM {local_adsafe_location} lal
                       ORDER BY lal.name";
            if($getlocsql = $DB->get_records_sql_menu($locsql,null)) {
                foreach ($getlocsql as $id => $name) {
                    $locationmenu["$id"] = $name;
                }
            }
            return $locationmenu;
            break;
            case 'Pastor':
            $locsql = "SELECT lal.id,lal.name
                       FROM {local_adsafe_location} lal
                       WHERE lal.pastoruserid = $userid
                       AND (UNIX_TIMESTAMP() >= lal.starttime) 
                       AND ((UNIX_TIMESTAMP() <= (lal.endtime + 86400)) OR (lal.endtime IS NULL))
                       ORDER BY lal.name"; //ORDER BY lal.id
            if($getlocsql = $DB->get_records_sql_menu($locsql,null)) {
                foreach ($getlocsql as $id => $name) {
                    $locationmenu["$id"] = $name;
                }
            }
            return $locationmenu;
            break;
            case 'Co-ordinator':
            $locsql = "SELECT lal.id, lal.name
                       FROM {local_adsafe_coordinators} lac
                       JOIN {local_adsafe_location} lal
                       ON lal.id = lac.locationid
                       WHERE lac.userid = $userid
                       AND (UNIX_TIMESTAMP() >= lac.starttime) 
                       AND ((UNIX_TIMESTAMP() <= (lac.endtime + 86400)) OR (lac.endtime IS NULL))
                       ORDER BY lal.name";
           if($getlocsql = $DB->get_records_sql_menu($locsql,null)) {
                foreach ($getlocsql as $id => $name) {
                    $locationmenu["$id"] = $name;
                }
            }
            return $locationmenu;
            break;
        }
    }
    
    public static function get_record_from_member_table_through_memid($memid) {
        global $DB;
        
        
        $memsql = "SELECT lam.id as memid,
                          lal.name as location,
                          lam.locationid,
                          CONCAT(u.lastname,', ', u.firstname) as member,
                          lam.userid,
                          lam.starttime,
                          lam.endtime,
                          lam.activated as active, 
                          lam.confirmed, 
                          lam.dateupdated,
                          lam.confirmeduserid,
                          (CASE WHEN u3.id IS NULL THEN 'No one confirmed yet' 
                                ELSE CONCAT(u3.lastname, ', ', u3.firstname)
                           END) AS confirmedusername,
                           lam.comments
                   FROM mdl_local_adsafe_member lam
                   JOIN mdl_local_adsafe_location lal
                   ON lam.locationid = lal.id
                   JOIN mdl_user u
                   ON lam.userid = u.id
				   LEFT JOIN mdl_user u3
				   ON u3.id = lam.confirmeduserid
                   WHERE lam.id = $memid";
        
        
       /* $memsql = "SELECT lam.id as memid,
                          lal.name as location,
                          lam.locationid,
                          CONCAT(u.lastname,' ', u.firstname) as member,
                          lam.userid,
                          lam.starttime,
                          lam.endtime,
                          lam.activated as active, 
                          lam.confirmed, 
                          lam.dateupdated, 
                          (CASE WHEN lam.confirmeduserid IS NULL THEN 'No one confirmed yet' 
                                ELSE (SELECT CONCAT(u2.lastname, ' ', u2.firstname)
                                      FROM mdl_local_adsafe_member lam2
                                      JOIN mdl_user u2
                                      ON u2.id = lam2.confirmeduserid)
                           END) AS confirmedusername,
                           lam.comments
                   FROM mdl_local_adsafe_member lam
                   JOIN mdl_local_adsafe_location lal
                   ON lam.locationid = lal.id
                   JOIN mdl_user u
                   ON lam.userid = u.id
                   WHERE lam.id = $memid";*/
        $memberrecord = $DB->get_record_sql($memsql,null);

        return $memberrecord; 
    }
    
    /**
     * Return an array of the member confirmation statuses
     *
     * @return array
     */
    public static function get_confirmation_statuses() {
        return array(
            self::OPTION_PLEASE_SELECT               => get_string('pleaseselect', 'local_adsafe'),
            self::OPTION_DOES_NOT_ATTEND_THIS_CHURCH => get_string('doesnotattendthischurch', 'local_adsafe'),
            self::OPTION_ATTENDS_THIS_CHURCH         => get_string('attendsthischurch', 'local_adsafe'),
            self::OPTION_UNKNOWN_NEED_TO_CHECK       => get_string('unknownneedtocheck', 'local_adsafe'),
        );
    }
    
    
    /**
     * Return an array of the member confirmation statuses
     *
     * @return array
     */
    public static function get_role_confirmation_statuses() {
        return array(
            self::OPTION_ROLE_CONFIRMED_PLEASE_SELECT => get_string('pleaseselect', 'local_adsafe'),
            self::OPTION_ROLE_UNKNOWN_NEED_TO_CHECK   => get_string('unknownneedtocheck', 'local_adsafe'),
            self::OPTION_ROLE_ACTIVE_IN_THIS_ROLE     => get_string('activeinthisrole', 'local_adsafe'),
            self::OPTION_MEMBER_NOT_IN_THIS_ROLE      => get_string('membernotinthisrole', 'local_adsafe'),
        );
    }
    
    
    public static function update_member_record_through_memid($data) {
    global $DB;
            /* object(stdClass)#5622 (12) 
            { ["hiddenmemid"]=> int(39) 
              ["location"]=> string(16) "1_Freeway Church" 
              ["hiddenlocationid"]=> string(1) "1" 
              ["member"]=> string(14) "support moodle" 
              ["hiddenmemberid"]=> string(3) "106" 
              ["status"]=> string(1) "0" 
              ["dateupdated"]=> int(1539014400) 
              ["confirmeduserid"]=> string(20) "No one confirmed yet" 
              ["hiddenconfirmeduserid"]=> string(3) "106" 
              ["commentarea"]=> string(13) "test comments" 
              ["activecheckbox"]=> string(1) "1" 
              ["submitbutton"]=> string(4) "Save" }
            */
        
        
        if($data && $data->submitbutton == 'Save') {
            
            $memsql = "SELECT lam.*
                       FROM {local_adsafe_member} lam
                       WHERE lam.id = $data->hiddenmemid";
            
            $getmemrecord = $DB->get_record_sql($memsql,null);
            
            if($getmemrecord) {
                
                // update existing record
                $update = new \stdClass;
                $update->id               = $data->hiddenmemid;
                $update->confirmed        = $data->status;
                $update->dateupdated      = $data->dateupdated;
                $update->confirmeduserid  = $data->hiddenconfirmeduserid;
                $update->comments         = $data->commentarea;
                $update->activated        = $data->activecheckbox;
               return (boolean)$DB->update_record('local_adsafe_member', $update);
            } 
        }
        return null;
    }
    
    
    public static function get_record_from_member_role_table_through_roleindex($roleindex,$locationid) {
        global $DB;
    
        //var_dump($roleindex);
        if($roleindex=='NO ROLE INDEX') {
            
        } else {
            $rolesql = "SELECT 
                        lamr.id,
                        lamr.userid AS userid,
                        lamr.locationid AS locationid,
                        lal.name as location, 
                        CONCAT(u.lastname,', ',u.firstname) as member,
                        lamr.roleid AS roleid,
                        lar.name as crole,
                        lamr.active,
                        lamr.startdate AS startdate,
                        lamr.enddate AS enddate,
                        lamr.wwcneededindex AS wwcneededindex,
                        lamr.dateupdated AS dateupdated,
                        lamr.confirmed AS confirmed,
                        lamr.confirmeduserid AS confirmeduserid,
                        (CASE WHEN u3.id IS NULL THEN 'No one confirmed yet' 
                              ELSE CONCAT(u3.lastname, ', ', u3.firstname)
                        END) AS confirmedusername,
                        lamr.comments AS comments
                        
                        FROM mdl_local_adsafe_member_role lamr
                        LEFT JOIN mdl_local_adsafe_location lal
                        ON lamr.locationid = lal.id
                        LEFT JOIN mdl_local_adsafe_role lar
                        ON lar.id = lamr.roleid
                        JOIN mdl_user u
                        ON u.id = lamr.userid
                        LEFT JOIN mdl_user u3
                        ON u3.id = lamr.confirmeduserid
                        WHERE lamr.locationid = $locationid
                        AND lamr.id = $roleindex";
            $rolerecord = $DB->get_record_sql($rolesql,null);
            return $rolerecord; 
        }
    }
   
   /**
     * Return an array of the role statuses
     *
     * @return array
     */
    public static function get_role_statuses() {
        
        global $DB;
        
        $sql = "SELECT  lar.id, lar.name
                FROM mdl_local_adsafe_role lar
                WHERE lar.active = 1
                ORDER BY lar.name";
        if($getsql = $DB->get_records_sql_menu($sql,null)) {
                foreach ($getsql as $id => $name) {
                    $rolemenu["$id"] = $name;
                }
        }
        return $rolemenu;

        /*return array(
            self::OPTION_ROLE_PLEASE_SELECT               => get_string('pleaseselect', 'local_adsafe'),
            self::OPTION_ROLE_ADRA                        => get_string('adra', 'local_adsafe'),
            self::OPTION_ROLE_ADVENTURERS                 => get_string('adventurers', 'local_adsafe'),
            self::OPTION_ROLE_BOARD_MEMBER                => get_string('boardmember', 'local_adsafe'),
            self::OPTION_ROLE_CHAPLAIN                    => get_string('chaplain', 'local_adsafe'),
            self::OPTION_ROLE_CHILDRENS_MINISTRIES        => get_string('childrensministries', 'local_adsafe'),
        );*/
    }
    
    /**
     * Return a string of the location name
     *
     * @return string
     */
    public static function get_location_name_from_locationid($locationid) {
        global $DB;
        
        $locsql = "SELECT lal.name
                   FROM mdl_local_adsafe_location lal
                   WHERE lal.id = $locationid";
        $locrecord = $DB->get_field_sql($locsql,null);
        return $locrecord;
    }
    
    public static function update_member_role_record_through_roleid($data) {
    global $DB;
            /* object(stdClass)#5635 (16) { 
            ["hiddenroleid"]=> int(6) 
            ["location"]=> string(39) "3_Hillsong Church Melbourne City Campus" 
            ["hiddenlocationid"]=> string(1) "3" 
            ["member"]=> string(14) "support moodle" 
            ["hiddenmemberid"]=> string(3) "106" 
            ["rolestatus"]=> string(1) "5" 
            ["activecheckbox"]=> string(1) "1" 
            ["startdate"]=> int(1517414400) 
            ["enddate"]=> int(1539187200) 
            ["wwccheckbox"]=> string(1) "1" 
            ["confirmedstatus"]=> string(1) "0" 
            ["dateupdated"]=> int(1539187200) 
            ["confirmedusername"]=> string(20) "No one confirmed yet" 
            ["hiddenconfirmeduserid"]=> string(3) "106" 
            ["commentarea"]=> string(0) "" 
            ["save"]=> string(4) "Save" }
            */
        
        
       // if($data->save!=null && $data->save == 'Save') {

            $rolesql = "SELECT lamr.*
                        FROM {local_adsafe_member_role} lamr
                        WHERE lamr.id = $data->hiddenroleid";
            
            $getrolerecord = $DB->get_record_sql($rolesql,null);
            
            if($getrolerecord) {
                
                // update existing record
                $update = new \stdClass;
                $update->id               = $data->hiddenroleid;
                $update->userid           = $data->hiddenmemberid;
                $update->roleid           = $data->rolestatus;
                $update->locationid       = $data->hiddenlocationid;
                $update->active           = $data->activecheckbox;
                $update->startdate        = $data->startdate;
                $update->enddate          = $data->enddate;
                $update->confirmed        = $data->confirmedstatus;
                $update->wwcneededindex   = $data->wwccheckbox;
                $update->dateupdated      = $data->dateupdated;
                $update->confirmeduserid  = $data->hiddenconfirmeduserid;
                $update->comments         = $data->commentarea;
                
               return (boolean)$DB->update_record('local_adsafe_member_role', $update);
            }
       // }
       // return null;
    }
    
    
    /**
     * Delete the role from coordinator dashboard.
     *
     * @param object $data
     * @return boolean
     */
    public static function member_role_delete_through_roleid($roleid) {
        global $DB;
        if (confirm_sesskey()) {
            $getsql = $DB->get_record('local_adsafe_member_role', array('id' => $roleid));
            if($getsql) {
                return (boolean)$DB->delete_records('local_adsafe_member_role', array('id' => $roleid));
            } else {
                return false;
            }
        }
        return false;
    }
    
    
    /**
     * Return an array of the wwc verification outcome statuses
     *
     * @return array
     */
    public static function get_verification_outcome_statuses() {
        return array(
            self::OPTION_OUTCOME_PLEASE_SELECT              => get_string('pleaseselect', 'local_adsafe'),
            self::OPTION_OUTCOME_APPLICATION_IN_PROGRESS    => get_string('applicationinprogress', 'local_adsafe'),
            self::OPTION_OUTCOME_CLEARED                    => get_string('cleared', 'local_adsafe'),
            self::OPTION_OUTCOME_BARRED                     => get_string('barred', 'local_adsafe'),
            self::OPTION_OUTCOME_INTERIM_BAR                => get_string('interimbar', 'local_adsafe'),
        );
    }
    
    public static function get_record_from_wwc_verify($userid,$locationid) {
        global $DB;
        $wwcverifiedsql = "SELECT lawv.id,
                           lawv.spsid,
                           lawv.wwcindicator as wwcindicator,
                           lawv.stateid,
                           lawv.nameoncard,
                           lawv.dateofbirth,
                           lawv.cardnumber,
                           lawv.expirydate,
                           lawv.locationid,
                           lawv.verified,
                           lawv.dateverified,
                           (CASE WHEN lawv.verifieduserid IS NULL THEN 'No one verified yet' 
                                 ELSE CONCAT(u.lastname, ' ', u.firstname)
                            END) AS verifiedusername,
                           lawv.verifieduserid,
                           lawv.comments,
                           lawv.timecreated
                    FROM {local_adsafe_wwc_verify} lawv
                    JOIN (SELECT MAX(lawv.timecreated) as timecreated
                    FROM {local_adsafe_wwc_verify} lawv
                    WHERE lawv.spsid = $userid
                    AND lawv.locationid = $locationid) AS x ON x.timecreated = lawv.timecreated
                    LEFT JOIN mdl_user u
                    ON u.id = lawv.verifieduserid
                    JOIN {local_adsafe_state} las
                    ON lawv.stateid = las.id
                    WHERE lawv.locationid = $locationid";
        $wwcverifiedrecord = $DB->get_record_sql($wwcverifiedsql,null);
        return $wwcverifiedrecord;
    }
    
    public static function update_wwc_verified_record_through_wwcvid($data) {
        global $DB;
            /* object(stdClass)#5642 (16) { 
                ["spsid"]=> string(3) "106" 
                ["wwcindicator"]=> int(1) 
                ["state"]=> string(1) "2" 
                ["nameoncard"]=> string(12) "Ken Changrrr" 
                ["datetext"]=> string(11) "11-Jul-1955" 
                ["cardnumber"]=> string(10) "0123456155" 
                ["expirydate"]=> string(11) "08-Dec-2018" 
                ["timecreated"]=> string(10) "1539317371" 
                ["hiddenveridiedid"]=> int(29) 
                ["hiddenlocationid"]=> int(1) 
                ["outcomestate"]=> string(1) "1" 
                ["dateverified"]=> int(1539532800) 
                ["verifiedusername"]=> string(19) "No one verified yet" 
                ["hiddenverifieduserid"]=> int(106) 
                ["commentarea"]=> string(0) "" 
                ["savebtn"]=> string(4) "Save" }
            */
        if($data && $data->savebtn == 'Save') {
            
            $wwcverifiedsql = "SELECT lawv.*
                               FROM {local_adsafe_wwc_verify} lawv
                               WHERE lawv.id = $data->hiddenveridiedid";
            
            
            $getwwcverifiedrecord = $DB->get_record_sql($wwcverifiedsql,null);
            
            if($getwwcverifiedrecord) {
                 // update existing record
                $update = new \stdClass;
                $update->id               = $data->hiddenveridiedid;
                $update->verified         = $data->outcomestate;
                $update->dateverified     = $data->dateverified;
                $update->verifieduserid   = $data->hiddenverifieduserid;
                $update->comments         = $data->commentarea;
               return (boolean)$DB->update_record('local_adsafe_wwc_verify', $update);
            }
        }
        //return null;
    }
    
    
    /**
     * duplicate a new wwc verified record through wwcvid
     *
     * @param object $data
     * @return boolean
     */
    public static function duplicate_wwc_verified_record_through_wwcvid($wwcvid) {
        global $DB;

            $wwcverifiedsql = "SELECT lawv.*
                               FROM {local_adsafe_wwc_verify} lawv
                               WHERE lawv.id = $wwcvid";
    
            $getwwcverifiedrecord = $DB->get_record_sql($wwcverifiedsql,null);
            
            if($getwwcverifiedrecord) {
                 // duplicate existing record
                $duplicated = new \stdClass;
                
                $duplicated->spsid             =   $getwwcverifiedrecord->spsid;
                $duplicated->wwcindicator      =   $getwwcverifiedrecord->wwcindicator;
                $duplicated->stateid           =   $getwwcverifiedrecord->stateid;
                $duplicated->nameoncard        =   $getwwcverifiedrecord->nameoncard;
                $duplicated->dateofbirth       =   $getwwcverifiedrecord->dateofbirth;
                $duplicated->cardnumber        =   $getwwcverifiedrecord->cardnumber;
                $duplicated->expirydate        =   $getwwcverifiedrecord->expirydate;
                $duplicated->locationid        =   $getwwcverifiedrecord->locationid;
                $duplicated->dateverified      =   0;
                $duplicated->verifieduserid    =   NULL;
                $duplicated->comments          =   NULL;
                $duplicated->timecreated       =  time();
                return (boolean)$DB->insert_record('local_adsafe_wwc_verify', $duplicated);
            }
        
        //return false;
    }
    
    public static function preloading_location_sorted($userid,$role){
        global $DB;
        // if dispaly page got something wrong, just using admin sql as your test environment to test
         /*$sql = "SELECT lal2.id
                FROM mdl_local_adsafe_location lal2
                WHERE lal2.name = (SELECT MIN(lal.name)
                FROM mdl_local_adsafe_location lal)";*/
        
        if ($role == 'admin') {
            $sql = "SELECT lal2.id
                    FROM mdl_local_adsafe_location lal2
                    WHERE lal2.name = (SELECT MIN(lal.name)
                    FROM mdl_local_adsafe_location lal)";
        }
        if ($role == 'Pastor') {
            $sql = "SELECT lal2.id
                    FROM mdl_local_adsafe_location lal2
                    WHERE lal2.name = (SELECT MIN(lal.name)
                    FROM mdl_local_adsafe_location lal
                    WHERE lal.pastoruserid = $userid)";
        }
        if ($role == 'Co-ordinator') {
        $sql = "SELECT lal2.id
                FROM mdl_local_adsafe_location lal2
                JOIN mdl_local_adsafe_coordinators lac
                ON lac.locationid = lal2.id
                WHERE lal2.name = (SELECT MIN(lal.name)
                                   FROM mdl_local_adsafe_location lal
                                   JOIN mdl_local_adsafe_coordinators lac2
                                   ON lac2.locationid = lal.id)
                AND lac.userid = $userid";
        }

        $getsql = $DB->get_record_sql($sql,null);
        return $getsql;                                     
    }
}