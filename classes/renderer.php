<?php
/**
 * ADSAFE
 *
 * Output renderers.
 *
 * @package    local_adsafe
 * @author     Ken Chang (@link kenc@pukunui.com)
 * @copyright  2018 Pukunui (@link https://pukunui.com/)
 * @license    https://www/gnu.org/copyleft/copyleft/gpl.html GNU GPL v3 or later
 */
 
defined('MOODLE_INTERNAL') || die();

/**
 * Define output renderers for this plugin.
 *
 * @package    local_adsafe
 * @author     Ken Chang (@link kenc@pukunui.com)
 * @copyright  2018 Pukunui (@link https://pukunui.com/)
 * @license    https://www/gnu.org/copyleft/copyleft/gpl.html GNU GPL v3 or later
 */
 
class local_adsafe_renderer extends plugin_renderer_base {
    
    /**
     * My Church and events list screen.
     * Refer to 3.1.2 of specification.
     *
     * @return string
     */
     
    public function church_event_list($userid) {
        global $CFG, $DB,$PAGE;
        
        $churchoreventurl = new moodle_url('/local/adsafe/churchoreventlist.php');
        
        // Add new Church and event form.
        $out = self::heading(get_string('addmetoachurchorevent', 'local_adsafe'));
        
        $PAGE->requires->js(new moodle_url('/local/adsafe/main.js'));
        $addnewchurchandeventform = new \local_adsafe\forms\churchsandeventsaddform();
        
        if ($data = $addnewchurchandeventform->get_data()) {
               // var_dump($data);
                //redirect($churchoreventurl);
        }
        
        ob_start();
        $addnewchurchandeventform->display();
        $out .= ob_get_contents();
        ob_end_clean();
        
        // Existing Church event table.
        $out .= self::heading(get_string('mychurchesandevents', 'local_adsafe'));
        
        
        /*$planssql = "SELECT lam.*,lac.name 
                     FROM {local_adsafe_member} lam
                     JOIN {local_adsafe_location} lac
                     ON lac.id = lam.locationid
                     WHERE lam.userid = $userid
                     ORDER BY lam.main DESC,lac.name ASC";*/
        $currenttime = time();
                  
        $planssql = "SELECT lam.*,lac.name,x.locationname,x.cooruserid,x.username,x.firstname,x.lastname,x.emailaddress
                     FROM {local_adsafe_member} lam
                     JOIN {local_adsafe_location} lac
                     ON lac.id = lam.locationid
                     LEFT JOIN (SELECT lac.locationid,lal.name as locationname,lac.userid as cooruserid,CONCAT(u2.lastname,', ',u2.firstname) as username,u2.firstname,u2.lastname, u2.email as emailaddress
                     FROM {local_adsafe_coordinators} lac
                     JOIN {local_adsafe_location} lal
                     ON lal.id = lac.locationid
                     JOIN mdl_user u2
                     ON u2.id = lac.userid
                     WHERE (lac.starttime <= $currenttime
                     AND lac.endtime > $currenttime)
                     OR lac.endtime = NULL) AS x ON x.locationid = lam.locationid
                     WHERE lam.userid = $userid
                     ORDER BY lam.main DESC,lac.name ASC"; 
        
        
        if ($getplans = $DB->get_records_sql($planssql,null)) {
            $table = new html_table();
            $table->head = array(get_string('churchslashevent', 'local_adsafe'),
                                 get_string('started', 'local_adsafe'),
                                 get_string('main', 'local_adsafe'),
                                 get_string('activated', 'local_adsafe'),
                                 get_string('action', 'local_adsafe'));
            $table->align = array('left', 'center', 'center', 'center', 'center');
            $table->id = 'churchandeventslist';
            $table->data = array();
            
            foreach ($getplans as $plan) {
                $row = new html_table_row();
                
                // Delete icon and link.
                $delete = html_writer::link(
                    new moodle_url('/local/adsafe/churchoreventlist.php', array('memid' => $plan->id,
                                                                      'action' => 'del')),
                    self::pix_icon('t/delete',
                        get_string('delete', 'local_adsafe'),
                        'moodle',
                        array('class' => 'iconsmall'))
                );
                
                $edit = html_writer::link(
                    new moodle_url('/local/adsafe/churchoreventedit.php', array('memid' => $plan->id,
                                                                      'action' => 'edi',
                                                                      'sesskey' => sesskey())),
                    self::pix_icon('t/edit',
                        get_string('edit', 'local_adsafe'),
                        'moodle',
                        array('class' => 'iconsmall'))
                );
                
                // Plan edit link.
                /*$planlink = html_writer::link(
                    new moodle_url('/local/adsafe/churchoreventlist.php', array('id' => $plan->id)),
                    $plan->name);*/
                $planlink = html_writer::link(
                    new moodle_url('/local/adsafe/churchoreventedit.php', array('memid' => $plan->id,
                                                                      'action' => 'edi',
                                                                      'sesskey' => sesskey())),
                    $plan->name);    
                
                    
                // Plan active checkbox.
                $activelink = $CFG->wwwroot.'/local/adsafe/churchoreventlist.php?memid='.$plan->id.'&action=active';
                $activecheckbox = html_writer::checkbox('active_'.$plan->id,
                                                        1,
                                                        ($plan->activated == 1),
                                                        '',
                                                        array('onclick' => "window.location='".$activelink."'"));
                
                // Plan main checkbox.
                $mainlink = $CFG->wwwroot.'/local/adsafe/churchoreventlist.php?memid='.$plan->id.'&action=main';
                $maincheckbox = html_writer::checkbox('main_'.$plan->id,
                                                        1,
                                                        ($plan->main == 1),
                                                        '',
                                                        array('onclick' => "window.location='".$mainlink."'")); //array('disabled' => true)
                                                        
                
                // Put the row together.
                $row->cells = array($planlink,
                                    userdate($plan->starttime, get_string('strftimedate', 'langconfig')),
                                    $maincheckbox,
                                    $activecheckbox,
                                    $edit.$delete);
                $table->data[] = $row;
            }
            $out .= html_writer::table($table);
        } 
        else {
            
        }
        
        
        $churchandeventbuttonform = new \local_adsafe\forms\churchsandeventsbuttonform();
        
        if($churchandeventbuttonform->is_cancelled()) {

        }
            
        if ($data = $churchandeventbuttonform->get_data()) {
            
           
        }

        ob_start();
        $churchandeventbuttonform->display();
        $out .= ob_get_contents();
        ob_end_clean();
  
        return $out;
    }
    
    public function edit_church_event_list($memid) {
        global $CFG, $DB,$PAGE;
        //$url     = new moodle_url('/local/adsafe/churchoreventedit.php'.'?memid='.$memid.'&action='.$action.'&sesskey='.$sesskey);
        //$churchoreventediturl = new moodle_url('/local/adsafe/churchoreventedit.php');
        //$churchoreventediturl = new moodle_url('/local/adsafe/churchoreventedit.php'.'?memid='.$memid.'&action='.$action.'&sesskey='.$sesskey);
        
        // Add new Church and event form.
        $out = self::heading(get_string('vieweditthechurchsandevents', 'local_adsafe'));
        
        //$memberrecord = \local_adsafe\utils::get_record_from_memberid($memid);
        
        /*$editchurchandeventform = new \local_adsafe\forms\churchsandeventseditform('', array('conferencename' => $memberrecord->conferencename,
                                                                                               'name' => $memberrecord->name,
                                                                                               'starttime' => $memberrecord->starttime,
                                                                                               'main' => $memberrecord->main,
                                                                                               'activated' => $memberrecord->activated
        ));*/
        
        
        
        $editchurchandeventform = new \local_adsafe\forms\churchsandeventseditform('',array('memid'=>$memid));
        
        if($editchurchandeventform->is_cancelled()) {
            
           // redirect(new moodle_url("/local/adsafe/churchoreventlist.php"));
            //exit;
        }
        
        if ($data = $editchurchandeventform->get_data() and confirm_sesskey()) {
            
            /*object(stdClass)#5616 (10) 
            { ["memid"]=> int(40) 
              ["conferencenamedisplay"]=> string(25) "West Australia Conference" 
              ["conferencenamehidden"]=> string(25) "West Australia Conference" 
              ["locationnamedisplay"]=> string(16) "2_Paradox Church" 
              ["locationnamehidden"]=> string(16) "2_Paradox Church" 
              ["starttime"]=> int(1536249600) 
              ["maincheckbox"]=> string(1) "1" 
              ["activatecheckbox"]=> string(1) "1" 
              ["action"]=> string(4) "edit" 
              ["submitbutton"]=> string(4) "Save" } */
            
            //var_dump($data);
            
            if(\local_adsafe\utils::update_record_through_memberid($data)){
                //redirect(new moodle_url("/local/adsafe/churchoreventlist.php"));
            } else {
                echo('error');
            }
            
            
           // $editchurchandeventform = new \local_adsafe\forms\churchsandeventseditform('',array('memid'=>$data->memid));
            
           // redirect($CFG->wwwroot."/local/adsafe/churchoreventedit.php?action=redir");
            
          

                 
            
                //redirect($churchoreventurl);
                
        }
        
        ob_start();
        $editchurchandeventform->display();
        $out .= ob_get_contents();
        ob_end_clean();
        
        return $out;
    }
    
    public function church_role_list($userid) {
        global $CFG, $DB,$PAGE;
        
        // Add new Church and event form.
        $out = self::heading(get_string('selectachurchrole', 'local_adsafe'));
        
        $churchroleform = new \local_adsafe\forms\churchroleform('',array('userid'=>$userid));
        
        
        
        if ($data = $churchroleform->get_data()) {
            
            //var_dump($data);
        }
        
        
        
        ob_start();
        $churchroleform->display();
        $out .= ob_get_contents();
        ob_end_clean();
        
        
        // Existing Church event table.
        $out .= self::heading(get_string('myroles', 'local_adsafe'));
        
        
        $planssql = "SELECT lamr.id, lar.name as rolename, lal.name as churcheventname, lamr.startdate as startdate,lamr.active
                     FROM mdl_local_adsafe_member_role lamr
                     JOIN mdl_local_adsafe_role lar
                     ON lamr.roleid = lar.id
                     JOIN mdl_local_adsafe_location lal
                     ON lamr.locationid = lal.id
                     WHERE lamr.userid = $userid
                     ORDER BY lar.name,lal.name";

        if ($getplans = $DB->get_records_sql($planssql,null)) {
            $table = new html_table();
            $table->head = array(get_string('role', 'local_adsafe'),
                                 get_string('churchslashevent', 'local_adsafe'),
                                 get_string('started', 'local_adsafe'),
                                 get_string('activated', 'local_adsafe'),
                                 get_string('action', 'local_adsafe'));
            $table->align = array('left', 'center', 'center', 'center', 'center');
            $table->id = 'myroleslist';
            $table->data = array();
            
            foreach ($getplans as $plan) {
                $row = new html_table_row();
                
                // Delete icon and link.
                $delete = html_writer::link(
                    new moodle_url('/local/adsafe/churchrolelist.php', array('roleid' => $plan->id,
                                                                      'action' => 'del')),
                    self::pix_icon('t/delete',
                        get_string('delete', 'local_adsafe'),
                        'moodle',
                        array('class' => 'iconsmall'))
                );
                
                $edit = html_writer::link(
                    new moodle_url('/local/adsafe/churchroleedit.php', array('roleid' => $plan->id,
                                                                      'action' => 'edi',
                                                                      'sesskey' => sesskey())),
                    self::pix_icon('t/edit',
                        get_string('edit', 'local_adsafe'),
                        'moodle',
                        array('class' => 'iconsmall'))
                );
                
               
                $planlink = html_writer::link(
                    new moodle_url('/local/adsafe/churchroleedit.php', array('roleid' => $plan->id,
                                                                      'action' => 'edi',
                                                                      'sesskey' => sesskey())),
                    $plan->rolename);    
                
                    
                // Plan active checkbox.
                $activelink = $CFG->wwwroot.'/local/adsafe/churchrolelist.php?roleid='.$plan->id.'&action=active';
                $activecheckbox = html_writer::checkbox('active_'.$plan->id,
                                                        1,
                                                        ($plan->active == 1),
                                                        '',
                                                        array('onclick' => "window.location='".$activelink."'"));
                // Put the row together.
                $row->cells = array($planlink,
                                    $plan->churcheventname,
                                    userdate($plan->startdate, get_string('strftimedate', 'langconfig')),
                                    $activecheckbox,
                                    $edit.$delete);
                $table->data[] = $row;
            }
            $out .= html_writer::table($table);
        } 
        else {
            
        }
        
        
        $churchrolebuttonform = new \local_adsafe\forms\churchrolebuttonform();
        if($churchrolebuttonform->is_cancelled()) {
            //echo('cancel');
            //redirect(new moodle_url("/local/adsafe/churchoreventlist.php"));
        }
        
        if ($data = $churchrolebuttonform->get_data()) {
            
           //var_dump($data);
        
           //if($data->submitbutton) {
               //echo('submit');
               //redirect(new moodle_url("/local/adsafe/workingwithchildrencard.php"));
               //redirect(new moodle_url("/local/adsafe/churchrolelist.php"));
           //} 
        }
        
        ob_start();
        $churchrolebuttonform->display();
        $out .= ob_get_contents();
        ob_end_clean();
        
        return $out;
    }
    
    
    public function edit_my_role_list($roleid) {
        global $CFG, $DB,$PAGE;
        //$url     = new moodle_url('/local/adsafe/churchoreventedit.php'.'?memid='.$memid.'&action='.$action.'&sesskey='.$sesskey);
        //$churchoreventediturl = new moodle_url('/local/adsafe/churchoreventedit.php');
        //$churchoreventediturl = new moodle_url('/local/adsafe/churchoreventedit.php'.'?memid='.$memid.'&action='.$action.'&sesskey='.$sesskey);
        
        // Add new Church and event form.
        $out = self::heading(get_string('vieweditmyroles', 'local_adsafe'));
        
        //$memberrecord = \local_adsafe\utils::get_record_from_memberid($memid);
        
        /*$editchurchandeventform = new \local_adsafe\forms\churchsandeventseditform('', array('conferencename' => $memberrecord->conferencename,
                                                                                               'name' => $memberrecord->name,
                                                                                               'starttime' => $memberrecord->starttime,
                                                                                               'main' => $memberrecord->main,
                                                                                               'activated' => $memberrecord->activated
        ));*/
        
        
        
        $editmyroleform = new \local_adsafe\forms\churchroleeditform('',array('roleid'=>$roleid));
        
        if($editmyroleform->is_cancelled()) {
            redirect(new moodle_url("/local/adsafe/churchrolelist.php"));
            exit;
        }
        
        if ($data = $editmyroleform->get_data() and confirm_sesskey()) {
            
            //var_dump($data);
            
            /*object(stdClass)#5616 (10) 
            { ["memid"]=> int(40) 
              ["conferencenamedisplay"]=> string(25) "West Australia Conference" 
              ["conferencenamehidden"]=> string(25) "West Australia Conference" 
              ["locationnamedisplay"]=> string(16) "2_Paradox Church" 
              ["locationnamehidden"]=> string(16) "2_Paradox Church" 
              ["starttime"]=> int(1536249600) 
              ["maincheckbox"]=> string(1) "1" 
              ["activatecheckbox"]=> string(1) "1" 
              ["action"]=> string(4) "edit" 
              ["submitbutton"]=> string(4) "Save" } */
            
            //var_dump($data);
            
            //if(\local_adsafe\utils::update_record_through_roleid($data)){
               // redirect(new moodle_url("/local/adsafe/churchrolelist.php"));
           // } else {
           //     echo('error');
          //  }
            
            
           // $editchurchandeventform = new \local_adsafe\forms\churchsandeventseditform('',array('memid'=>$data->memid));
            
           // redirect($CFG->wwwroot."/local/adsafe/churchoreventedit.php?action=redir");
            
          

                 
            
                //redirect($churchoreventurl);
                
        }
        
        ob_start();
        $editmyroleform->display();
        $out .= ob_get_contents();
        ob_end_clean();
        
        return $out;
    }
    
    
    public function add_working_with_children_card_list($userid) {
         global $CFG, $DB,$PAGE;
        //$url     = new moodle_url('/local/adsafe/churchoreventedit.php'.'?memid='.$memid.'&action='.$action.'&sesskey='.$sesskey);
        //$churchoreventediturl = new moodle_url('/local/adsafe/churchoreventedit.php');
        //$churchoreventediturl = new moodle_url('/local/adsafe/churchoreventedit.php'.'?memid='.$memid.'&action='.$action.'&sesskey='.$sesskey);
        
        // Add new Church and event form.
        $out = self::heading(get_string('workingwithchildrencard', 'local_adsafe'));
        
        //$memberrecord = \local_adsafe\utils::get_record_from_memberid($memid);
        
        /*$editchurchandeventform = new \local_adsafe\forms\churchsandeventseditform('', array('conferencename' => $memberrecord->conferencename,
                                                                                               'name' => $memberrecord->name,
                                                                                               'starttime' => $memberrecord->starttime,
                                                                                               'main' => $memberrecord->main,
                                                                                               'activated' => $memberrecord->activated
        ));*/
        
        
        
        $workingwithchildrencardform = new \local_adsafe\forms\workingwithchildrencardform('',array('userid'=>$userid));
        
        if($workingwithchildrencardform->is_cancelled()) {
            //redirect(new moodle_url("/local/adsafe/churchrolelist.php"));
            //exit;
        }
        
        if ($data = $workingwithchildrencardform->get_data()) {
            
            //var_dump($data);
            
            /*object(stdClass)#5616 (10) 
            { ["memid"]=> int(40) 
              ["conferencenamedisplay"]=> string(25) "West Australia Conference" 
              ["conferencenamehidden"]=> string(25) "West Australia Conference" 
              ["locationnamedisplay"]=> string(16) "2_Paradox Church" 
              ["locationnamehidden"]=> string(16) "2_Paradox Church" 
              ["starttime"]=> int(1536249600) 
              ["maincheckbox"]=> string(1) "1" 
              ["activatecheckbox"]=> string(1) "1" 
              ["action"]=> string(4) "edit" 
              ["submitbutton"]=> string(4) "Save" } */
            
           // var_dump($data);
            
            //if(\local_adsafe\utils::update_record_through_userid($data)){
                //echo('progress');
                //redirect(new moodle_url("/local/adsafe/workingwithchildrencard.php"));
           // } else {
                //echo('error');
           // }
            
            
           // $editchurchandeventform = new \local_adsafe\forms\churchsandeventseditform('',array('memid'=>$data->memid));
            
           // redirect($CFG->wwwroot."/local/adsafe/churchoreventedit.php?action=redir");
            
          

                 
            
                //redirect($churchoreventurl);
                
        }
        
        ob_start();
        $workingwithchildrencardform->display();
        $out .= ob_get_contents();
        ob_end_clean();
        
        return $out;
    }
    
    
    public function view_coordinator_dashboard_list($userid,$role,$locationid) {
        global $DB,$CFG;
        // Add new Coordinator dashboard form.
        $out = self::heading(get_string('coordinatordashboard', 'local_adsafe'));
  
  
        /*var_dump($userid);
        var_dump($role);
        var_dump($locationid);*/
  
        $coordinatordashboardform = new \local_adsafe\forms\coordinatordashboardform('',array('userid'=>$userid, 'role'=>$role, 'locationid'=>$locationid));
        
        ob_start();
        $coordinatordashboardform->display();
        $out .= ob_get_contents();
        ob_end_clean();
        
        
        $planssql = "SELECT CONCAT(lam.id,lam.userid,(CASE WHEN lamr2.roleid IS NULL 
                                                                   THEN 0
                                                                   ELSE lamr2.roleid
                                                              END)) as conid,
                                    lam.id,
                                    lam.userid as userid,
                                    CONCAT(u.lastname,', ', u.firstname) as name,
                                    (CASE WHEN lamr2.id IS NULL
                                     THEN 'NO ROLE INDEX'
                                     ELSE lamr2.id
                                     END) as roleindex,
                                    (CASE WHEN lamr2.roleid IS NULL
                                     THEN 0
                                     ELSE lamr2.roleid
                                     END) as roleid,
                                    (CASE WHEN lar.name IS NULL
                                     THEN 'NO ROLE'
                                     ELSE lar.name
                                     END) as role,
                                    (CASE WHEN lamr2.locationid IS NULL
                                     THEN 'NO LOCATION ID'
                                     ELSE lamr2.locationid 
                                     END) as locationid,
                                    lam.starttime as started,
                                    lam.confirmed as confirm,
                                    (CASE WHEN lamr2.confirmed IS NULL
                                     THEN -1
                                     ELSE lamr2.confirmed 
                                     END) as crole,
                                    (CASE WHEN y.verified IS NULL
                                     THEN -1
                                     ELSE y.verified
                                     END) as verified,
                                     y.expirydate,
                                     lamr2.active as active,
                                     lamr2.enddate as enddate,
                                     lamr2.wwcneededindex as wwcneededindex
                            FROM mdl_local_adsafe_member lam
                            LEFT JOIN mdl_local_adsafe_member_role lamr2
                            ON lam.locationid = lamr2.locationid
                            AND lam.userid = lamr2.userid
                            LEFT JOIN mdl_local_adsafe_role lar
                            ON lamr2.roleid = lar.id
                            LEFT JOIN
                            (
                             SELECT lawv.id as vid, lawv.spsid, lawv.verified,lawv.expirydate,lawv.locationid,lawv.timecreated
                             FROM mdl_local_adsafe_wwc_verify lawv
                             INNER JOIN 
                             (
                              SELECT MAX(lawv.timecreated) as mts
                              FROM mdl_local_adsafe_wwc_verify lawv
                              WHERE lawv.locationid = $locationid
                            
                              ) as s2 
                              ON lawv.timecreated = s2.mts
                             ) as y 
                              ON y.spsid = lam.userid
                              AND y.locationid = lam.locationid
                            JOIN mdl_user u
                            ON u.id = lam.userid
                            WHERE lam.locationid = $locationid
                            ORDER BY name,role";

        if ($getplans = $DB->get_records_sql($planssql,null)) {
            $table = new html_table();
            $table->head = array(get_string('name', 'local_adsafe'),
                                 get_string('role', 'local_adsafe'),
                                 get_string('started', 'local_adsafe'),
                                 get_string('bconfirm', 'local_adsafe'),
                                 get_string('role', 'local_adsafe'),
                                 get_string('wwc', 'local_adsafe'));
            $table->align = array('left', 'center', 'center', 'center', 'center', 'center');
            $table->id = 'locationofroleslist';
            $table->data = array();
            
            foreach ($getplans as $plan) {
                $row = new html_table_row();
                
                // Delete icon and link.
                $delete = html_writer::link(
                    new moodle_url('/local/adsafe/churchrolelist.php', array('roleid' => $plan->id,
                                                                      'action' => 'del')),
                    self::pix_icon('t/delete',
                        get_string('delete', 'local_adsafe'),
                        'moodle',
                        array('class' => 'iconmedium'))
                );
                
                $planlink = html_writer::link(
                    new moodle_url('/local/adsafe/coordinatormemberedit.php', array('memid'   => $plan->id,
                                                                             'roleid'  => $plan->roleid,
                                                                             'action'  => 'edi',
                                                                             'sesskey' => sesskey())),
                                    $plan->name);    

                $confirm_pic_str = '';
                switch ($plan->confirm) {
                    case -1:
                        $confirm_pic_str = 'image_question_red';
                    break;
                    case 0:
                        $confirm_pic_str = 'image_question_red';
                    break;
                    case 1:
                        $confirm_pic_str = 'image_dash_grey';
                    break;
                    case 2:
                        $confirm_pic_str = 'image_tick_green';
                    break;
                    default :
                        $confirm_pic_str = 'image_question_red';
                }
   
                $confirm = html_writer::link(
                new moodle_url('/local/adsafe/coordinatormemberedit.php', array('memid'   => $plan->id,
                                                                         'roleid'  => $plan->roleid,
                                                                         'action'  => 'edi',
                                                                         'sesskey' => sesskey())),
                //public_html/local/adsafe/pix/image_dash_grey.png
                self::pix_icon($confirm_pic_str,
                               get_string('edit', 'local_adsafe'),
                               'moodle',
                               array('class' => 'iconmedium'))
                );
                
                
                $currenttime = time();
                
                $role_pic_str = '';
                if($plan->crole == -2) {
                    $role_pic_str = 'image_question_red';
                } 
                if($plan->crole == 0) {
                    $role_pic_str = 'image_question_red';
                } 
                else if ($plan->crole == 1) {
                    $role_pic_str = 'image_dash_grey'; 
                }
                else if ($plan->crole ==2){
                    if($plan->active == 1 && $plan->enddate > $currenttime) {
                        if($plan->wwcneededindex == 0) {
                            $role_pic_str = 'image_tick_green';
                        } else if($plan->wwcneededindex == 1){
                            if($plan->verified == 2) {
                                $role_pic_str = 'image_tick_green';
                            } else {
                                $role_pic_str = 'image_dash_grey';
                            }
                        }
                    }
                    if($plan->active == 0 || ($plan->enddate < $currenttime)) {
                        $role_pic_str = 'image_dash_grey';
                    }
                }
                else if ($plan->crole == -1) {
                    $role_pic_str = 'image_dash_grey';
                }
                
                
                if($plan->crole == -1) {
                    $locationname = \local_adsafe\utils::get_location_name_from_locationid($locationid);
                    $role = html_writer::link(null,
                                                 self::pix_icon($role_pic_str,
                                                 get_string('userdoesnthaveroleofthelocation', 'local_adsafe',$locationname),
                                                 'moodle',
                                                 array('class' => 'iconmedium'))
                    );
                } else {
                    $role = html_writer::link(
                        new moodle_url('/local/adsafe/coordinatorroleedit.php', array('roleindex'  => $plan->roleindex,
                                                                                      'userid'     => $plan->userid,
                                                                                      'locationid' => $locationid,
                                                                                      'action'     => 'edi',
                                                                                      'sesskey'    => sesskey())),
                        self::pix_icon($role_pic_str,
                                       get_string('edit', 'local_adsafe'),
                                       'moodle',
                                       array('class' => 'iconmedium'))
                    );
                
                }
                
                
                $wwc_pic_str = '';
                switch ($plan->verified) {
                    case -1:
                        $wwc_pic_str = 'image_dash_grey';
                    break;
                    case 0:
                        $wwc_pic_str = 'image_question_red';
                    break;
                    case 1:
                        $wwc_pic_str = 'image_question_red';
                    break;
                    case 2:
                        if($plan->expirydate < strtotime(date('Y-m-d', time()))) {
                            $wwc_pic_str = 'image_dash_grey';
                        } else {
                            $wwc_pic_str = 'image_tick_green';
                        }
                    break;
                    case 3:
                        $wwc_pic_str = 'image_dash_grey';
                    break;
                    case 4:
                        $wwc_pic_str = 'image_question_red';
                    break;
                    default :
                        $wwc_pic_str = 'image_question_red';
                }
                
                if($plan->verified == -1) {
                    $locationname = \local_adsafe\utils::get_location_name_from_locationid($locationid);
                    $wwc = html_writer::link(null,
                                             self::pix_icon($wwc_pic_str,
                                                            get_string('userdoesnthavewwcvofthelocation', 'local_adsafe',$locationname),
                                                            'moodle',
                                                            array('class' => 'iconmedium'))
                                            );
                } elseif ($plan->verified == 2) {
                    if($plan->expirydate < strtotime(date('Y-m-d', time()))) {
                        $wwc = html_writer::link(
                                                new moodle_url('/local/adsafe/coordinatorwwcedit.php', array('userid'  => $plan->userid,
                                                                                                             'locationid' => $locationid,
                                                                                                             'action' => 'edi',
                                                                                                             'sesskey' => sesskey())),
                                                self::pix_icon($wwc_pic_str,
                                                               get_string('wwcexpired', 'local_adsafe'),
                                                               'moodle',
                                                               array('class' => 'iconmedium'))
                        );
                    } else {
                        $wwc = html_writer::link(
                                                new moodle_url('/local/adsafe/coordinatorwwcedit.php', array('userid'  => $plan->userid,
                                                                                                             'locationid' => $locationid,
                                                                                                             'action' => 'edi',
                                                                                                             'sesskey' => sesskey())),
                                                self::pix_icon($wwc_pic_str,
                                                               get_string('edit', 'local_adsafe'),
                                                               'moodle',
                                                               array('class' => 'iconmedium'))
                        );
                        
                    }
                } 
                else {
                        $wwc = html_writer::link(
                        new moodle_url('/local/adsafe/coordinatorwwcedit.php', array('userid'  => $plan->userid,
                                                                                     'locationid' => $locationid,
                                                                                     'action' => 'edi',
                                                                                     'sesskey' => sesskey())),
                        self::pix_icon($wwc_pic_str,
                                       get_string('edit', 'local_adsafe'),
                                       'moodle',
                                       array('class' => 'iconmedium'))
                    );
                }
                
                
                $row->cells = array($planlink,
                                    $plan->role,
                                    userdate($plan->started, get_string('strftimedate', 'langconfig')),
                                    $confirm,
                                    $role,
                                    $wwc);
                $table->data[] = $row;
            }
            $out .= html_writer::table($table);
        } 
        else {
            
        }


        if($coordinatordashboardform->is_cancelled()) {
            //redirect(new moodle_url("/local/adsafe/churchrolelist.php"));
            //exit;
        }
        if ($data = $coordinatordashboardform->get_data() and confirm_sesskey()) {
     /*
             var_dump($data);
                $planssql = "SELECT CONCAT(lam.id,lam.userid,(CASE WHEN lamr2.roleid IS NULL 
                                                                   THEN 0
                                                                   ELSE lamr2.roleid
                                                              END)) as conid,
                                    lam.id,
                                    lam.userid as userid,
                                    CONCAT(u.lastname,' ', u.firstname) as name,
                                    (CASE WHEN lamr2.id IS NULL
                                     THEN 'NO ROLE INDEX'
                                     ELSE lamr2.id
                                     END) as roleindex,
                                    (CASE WHEN lamr2.roleid IS NULL
                                     THEN 0
                                     ELSE lamr2.roleid
                                     END) as roleid,
                                    (CASE WHEN lar.name IS NULL
                                     THEN 'NO ROLE'
                                     ELSE lar.name
                                     END) as role,
                                    (CASE WHEN lamr2.locationid IS NULL
                                     THEN 'NO LOCATION ID'
                                     ELSE lamr2.locationid 
                                     END) as locationid,
                                    lam.starttime as started,
                                    lam.confirmed as confirm,
                                    (CASE WHEN lamr2.confirmed IS NULL
                                     THEN -1
                                     ELSE lamr2.confirmed 
                                     END) as crole,
                                     y.verified

                            FROM mdl_local_adsafe_member lam
                            LEFT JOIN mdl_local_adsafe_member_role lamr2
                            ON lam.locationid = lamr2.locationid
                            AND lam.userid = lamr2.userid
                            LEFT JOIN mdl_local_adsafe_role lar
                            ON lamr2.roleid = lar.id
                            LEFT JOIN
                            (
                             SELECT lawv.id as vid, lawv.spsid, lawv.verified,lawv.locationid,lawv.timecreated
                             FROM mdl_local_adsafe_wwc_verify lawv
                             INNER JOIN 
                             (
                              SELECT MAX(lawv.timecreated) as mts
                              FROM mdl_local_adsafe_wwc_verify lawv
                              WHERE lawv.locationid = $data->locationid
                            
                              ) as s2 
                              ON lawv.timecreated = s2.mts
                             ) as y 
                              ON y.spsid = lam.userid
                              AND y.locationid = lam.locationid
                            JOIN mdl_user u
                            ON u.id = lam.userid
                            WHERE lam.locationid = $data->locationid";

        if ($getplans = $DB->get_records_sql($planssql,null)) {
            $table = new html_table();
            $table->head = array(get_string('name', 'local_adsafe'),
                                 get_string('role', 'local_adsafe'),
                                 get_string('started', 'local_adsafe'),
                                 get_string('bconfirm', 'local_adsafe'),
                                 get_string('role', 'local_adsafe'),
                                 get_string('wwc', 'local_adsafe'));
            $table->align = array('left', 'center', 'center', 'center', 'center', 'center');
            $table->id = 'locationofroleslist';
            $table->data = array();
            
            foreach ($getplans as $plan) {
                $row = new html_table_row();
                
                // Delete icon and link.
                $delete = html_writer::link(
                    new moodle_url('/local/adsafe/churchrolelist.php', array('roleid' => $plan->id,
                                                                      'action' => 'del')),
                    self::pix_icon('t/delete',
                        get_string('delete', 'local_adsafe'),
                        'moodle',
                        array('class' => 'iconmedium'))
                );
                
                $planlink = html_writer::link(
                    new moodle_url('/local/adsafe/coordinatormemberedit.php', array('memid'   => $plan->id,
                                                                             'roleid'  => $plan->roleid,
                                                                             'action'  => 'edi',
                                                                             'sesskey' => sesskey())),
                                    $plan->name);    

                $confirm_pic_str = '';
                switch ($plan->confirm) {
                    case 0:
                        $confirm_pic_str = 'image_question_red';
                    break;
                    case 1:
                        $confirm_pic_str = 'image_dash_grey';
                    break;
                    case 2:
                        $confirm_pic_str = 'image_tick_green';
                    break;
                    default :
                        $confirm_pic_str = 'image_question_red';
                }
   
                $confirm = html_writer::link(
                new moodle_url('/local/adsafe/coordinatormemberedit.php', array('memid'   => $plan->id,
                                                                         'roleid'  => $plan->roleid,
                                                                         'action'  => 'edi',
                                                                         'sesskey' => sesskey())),
                //public_html/local/adsafe/pix/image_dash_grey.png
                self::pix_icon($confirm_pic_str,
                               get_string('edit', 'local_adsafe'),
                               'moodle',
                               array('class' => 'iconmedium'))
                );
                

                
                $role_pic_str = '';
                if($plan->crole == 0) {
                    $role_pic_str = 'image_question_red';
                } 
                else if ($plan->crole == 1) {
                    $role_pic_str = 'image_dash_grey'; 
                }
                else if ($plan->crole == 2) {
                    $role_pic_str = 'image_tick_green'; 
                }
                else if ($plan->crole == -1) {
                    $role_pic_str = 'image_dash_grey';
                }
                
                if($plan->crole == -1) {
                    $locationname = \local_adsafe\utils::get_location_name_from_locationid($data->locationid);
                    $role = html_writer::link(null,
                                                 self::pix_icon($role_pic_str,
                                                 get_string('userdoesnthaveroleofthelocation', 'local_adsafe',$locationname),
                                                 'moodle',
                                                 array('class' => 'iconmedium'))
                    );
                } else {
                    $role = html_writer::link(
                        new moodle_url('/local/adsafe/coordinatorroleedit.php', array('roleindex'  => $plan->roleindex,
                                                                                      'userid'     => $plan->userid,
                                                                                      'locationid' => $data->locationid,
                                                                                      'action'     => 'edi',
                                                                                      'sesskey'    => sesskey())),
                        self::pix_icon($role_pic_str,
                                       get_string('edit', 'local_adsafe'),
                                       'moodle',
                                       array('class' => 'iconmedium'))
                    );
                
                }
                
                
                $wwc_pic_str = '';
                switch ($plan->verified) {
                    case 0:
                        $wwc_pic_str = 'image_question_red';
                    break;
                    case 1:
                        $wwc_pic_str = 'image_dash_grey';
                    break;
                    case 2:
                        $wwc_pic_str = 'image_tick_green';
                    break;
                    default :
                        $wwc_pic_str = 'image_question_red';
                }
                
                $wwc = html_writer::link(
                    new moodle_url('/local/adsafe/churchroleedit.php', array('roleid' => $plan->id,
                                                                      'action' => 'edi',
                                                                      'sesskey' => sesskey())),
                    self::pix_icon($wwc_pic_str,
                                   get_string('edit', 'local_adsafe'),
                                   'moodle',
                                   array('class' => 'iconmedium'))
                );

                $row->cells = array($planlink,
                                    $plan->role,
                                    userdate($plan->started, get_string('strftimedate', 'langconfig')),
                                    $confirm,
                                    $role,
                                    $wwc);
                $table->data[] = $row;
            }
            $out .= html_writer::table($table);
        } 
        else {
            
        }*/
        
        
        //ob_start();
       // $out .= ob_get_contents();
       // ob_end_clean();
        }
        
        ob_start();
        //$coordinatordashboardform->display();
        $out .= ob_get_contents();
        ob_end_clean();
 
        
        return $out;
    }
    
    
    public function view_coordinator_confirmed_page($memid,$confirmuserid,$locationid) {
     global $CFG, $DB,$PAGE;
    //$url     = new moodle_url('/local/adsafe/churchoreventedit.php'.'?memid='.$memid.'&action='.$action.'&sesskey='.$sesskey);
    //$churchoreventediturl = new moodle_url('/local/adsafe/churchoreventedit.php');
    //$churchoreventediturl = new moodle_url('/local/adsafe/churchoreventedit.php'.'?memid='.$memid.'&action='.$action.'&sesskey='.$sesskey);
    
    // Add new Church and event form.
    $out = self::heading(get_string('membersconfirmation', 'local_adsafe'));
    
    //$memberrecord = \local_adsafe\utils::get_record_from_memberid($memid);
    
    /*$editchurchandeventform = new \local_adsafe\forms\churchsandeventseditform('', array('conferencename' => $memberrecord->conferencename,
                                                                                           'name' => $memberrecord->name,
                                                                                           'starttime' => $memberrecord->starttime,
                                                                                           'main' => $memberrecord->main,
                                                                                           'activated' => $memberrecord->activated
    ));*/
        
        
        
        $coordinatormembereditform = new \local_adsafe\forms\coordinatormembereditform('',array('memid'=>$memid,'userid'=>$confirmuserid));
        
        if($coordinatormembereditform->is_cancelled()) {
           // redirect(new moodle_url("/local/adsafe/coordinatordashboardlist.php",array('locationid'=>$locationid)));
           // exit;
        }
        
        if ($data = $coordinatormembereditform->get_data() and confirm_sesskey()) {
            
           // var_dump($data);
            
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
            
            
            
            //var_dump($data);
            
           // if(\local_adsafe\utils::update_member_record_through_memid($data)){
                //?memid=39&roleid=1&action=edi&sesskey=pJJ7LA3Ya0
                
                //redirect(new moodle_url("/local/adsafe/coordinatormemberedit.php?memid=".$data->hiddenmemid."&roleid=".$data->hiddenlocationid."&action=edi"));
            //    redirect(new moodle_url("/local/adsafe/coordinatordashboardlist.php",array('locationid'=>$data->hiddenlocationid)));
                
          //  } else {
          //      echo('error');
          //  }
            
            
           // $editchurchandeventform = new \local_adsafe\forms\churchsandeventseditform('',array('memid'=>$data->memid));
            
           // redirect($CFG->wwwroot."/local/adsafe/churchoreventedit.php?action=redir");
            
          

                 
            
                //redirect($churchoreventurl);
                
        }
        
        ob_start();
        $coordinatormembereditform->display();
        $out .= ob_get_contents();
        ob_end_clean();
        
        return $out;
    }
    
    public function view_coordinator_role_page($roleindex,$locationid,$confirmuserid) {
     global $CFG, $DB,$PAGE;
    //$url     = new moodle_url('/local/adsafe/churchoreventedit.php'.'?memid='.$memid.'&action='.$action.'&sesskey='.$sesskey);
    //$churchoreventediturl = new moodle_url('/local/adsafe/churchoreventedit.php');
    //$churchoreventediturl = new moodle_url('/local/adsafe/churchoreventedit.php'.'?memid='.$memid.'&action='.$action.'&sesskey='.$sesskey);
    
    // Add new Church and event form.
    $out = self::heading(get_string('membersroleconfirmation', 'local_adsafe'));
    
    //$memberrecord = \local_adsafe\utils::get_record_from_memberid($memid);
    
    /*$editchurchandeventform = new \local_adsafe\forms\churchsandeventseditform('', array('conferencename' => $memberrecord->conferencename,
                                                                                           'name' => $memberrecord->name,
                                                                                           'starttime' => $memberrecord->starttime,
                                                                                           'main' => $memberrecord->main,
                                                                                           'activated' => $memberrecord->activated
    ));*/
        
        
        
        $coordinatorroleeditform = new \local_adsafe\forms\coordinatorroleeditform('',array('roleindex'=>$roleindex,'locationid'=>$locationid,'userid'=>$confirmuserid));
        
        //if($coordinatorroleeditform->is_cancelled()) {
        //    redirect(new moodle_url("/local/adsafe/coordinatordashboardlist.php",array('locationid'=>$locationid)));
        //    exit;
        //}
        
        
        if ($data = $coordinatorroleeditform->get_data() and confirm_sesskey()) {
            
            
           /* 
            
            //var_dump($data);
            
            $redata = new stdClass();
            
            $redata->hiddenroleid            = $data->hiddenroleid;
            $redata->location                = $data->location;
            $redata->hiddenlocationid        = $data->hiddenlocationid;
            $redata->member                  = $data->member;
            $redata->hiddenmemberid          = $data->hiddenmemberid;
            $redata->rolestatus              = $data->rolestatus;
            $redata->activecheckbox          = $data->activecheckbox;
            $redata->startdate               = $data->startdate;
            $redata->enddate                 = $data->enddate;
            $redata->wwccheckbox             = $data->wwccheckbox;
            $redata->confirmedstatus         = $data->confirmedstatus;
            $redata->dateupdated             = $data->dateupdated;
            $redata->confirmedusername       = $data->confirmedusername;
            $redata->hiddenconfirmeduserid   = $data->hiddenconfirmeduserid;
            $redata->commentarea             = $data->commentarea;
          //  $redata->savebtn                 = $data->savebtn; 
           // $redata->deletebtn               = $data->deletebtn;
           // $redata->cancelbtn               = $data->cancelbtn; 
            
            
            if (!empty($data->savebtn)) {
                $redata->savebtn             = $data->savebtn;
                
                if($update = \local_adsafe\utils::update_member_role_record_through_roleid($data)){
                //?memid=39&roleid=1&action=edi&sesskey=pJJ7LA3Ya0
                
                //redirect(new moodle_url("/local/adsafe/coordinatorroleedit.php?roleindex=".$data->hiddenroleid."&userid=".$data->hiddenmemberid."&locationid=".$data->hiddenlocationid."&action=edi"));
                redirect(new moodle_url("/local/adsafe/coordinatordashboardlist.php",array('locationid'=>$locationid)));
                exit;
                } else {
                    //echo('error');
                }
            }
            if (!empty($data->deletebtn)) {
                $redata->deletebtn           = $data->deletebtn;
                //redirect(new moodle_url('/local/evtp/planview.php', array('id'=>$planid)),get_string('addednewregistrarplan', 'local_evtp'));
                redirect(new moodle_url('/local/adsafe/coordinatorroleedit.php', array('roleindex'=>$data->hiddenroleid, 'userid'=>$data->hiddenmemberid, 'locationid'=>$data->hiddenlocationid,'action'=>'del')));
                //redirect(new moodle_url("/local/adsafe/coordinatorroleedit.php?roleindex=".$data->hiddenroleid."&userid=".$data->hiddenmemberid."&locationid=".$data->hiddenlocationid."&action=del"));
                exit;
            }
            
            */
            
/*
            if($redata->btnstatus) {
                if($update = \local_adsafe\utils::update_member_role_record_through_roleid($data)){
                //?memid=39&roleid=1&action=edi&sesskey=pJJ7LA3Ya0
                
                //redirect(new moodle_url("/local/adsafe/coordinatorroleedit.php?roleindex=".$data->hiddenroleid."&userid=".$data->hiddenmemberid."&locationid=".$data->hiddenlocationid."&action=edi"));
                redirect(new moodle_url("/local/adsafe/coordinatordashboardlist.php"));
                exit;
                } else {
                    echo('error');
                }
            }
            
            
            if($redata->btnstatus) {
                 //redirect(new moodle_url('/local/evtp/planview.php', array('id'=>$planid)),get_string('addednewregistrarplan', 'local_evtp'));
                redirect(new moodle_url('/local/adsafe/coordinatorroleedit.php', array('roleindex'=>$data->hiddenroleid, 'userid'=>$data->hiddenmemberid, 'locationid'=>$data->hiddenlocationid,'action'=>'del')));
                //redirect(new moodle_url("/local/adsafe/coordinatorroleedit.php?roleindex=".$data->hiddenroleid."&userid=".$data->hiddenmemberid."&locationid=".$data->hiddenlocationid."&action=del"));
                exit;
            }
            
           // $editchurchandeventform = new \local_adsafe\forms\churchsandeventseditform('',array('memid'=>$data->memid));
            
           // redirect($CFG->wwwroot."/local/adsafe/churchoreventedit.php?action=redir");
            
          */

                
        }
        
        ob_start();
        $coordinatorroleeditform->display();
        $out .= ob_get_contents();
        ob_end_clean();
        
        return $out;
    }
    
    
    
    
    
    /**
     * Church and Events delete confirmation screen.
     * Refer to 3.1 of specification.
     *
     * @param integer $memid  member id
     * @return string|boolean
     */
    public function church_event_delete_confirmation($memid) {
        global $DB;

        $churcheventsql = "SELECT lac.name 
                           FROM mdl_local_adsafe_member lam
                           JOIN mdl_local_adsafe_location lac
                           ON lac.id = lam.locationid
                           WHERE lam.id = $memid";

        if ($getchurcheventsql = $DB->get_field_sql($churcheventsql,null)) {
            $message = get_string('confirmdeletechurchevent', 'local_adsafe', $getchurcheventsql);
            $continue = new moodle_url('/local/adsafe/churchoreventlist.php', array('memid'  => $memid,
                                                                          'action'  => 'del',
                                                                          'confirm' => 1,
                                                                          'sesskey' => sesskey()));
            $cancel = new moodle_url('/local/adsafe/churchoreventlist.php');

            return self::confirm($message, $continue, $cancel);
        }
        return false;
    }
    
    /**
     * My roles delete confirmation screen.
     * Refer to 3.1.3 of specification.
     *
     * @param integer $roleid  role id
     * @return string|boolean
     */
    public function my_role_delete_confirmation($roleid) {
        global $DB;

        $myrolesql = "SELECT lar.name
                      FROM {local_adsafe_member_role} lamr
                      JOIN {local_adsafe_role} lar
                      ON lamr.roleid = lar.id
                      WHERE lamr.id = $roleid";

        if ($getmyrolesql = $DB->get_field_sql($myrolesql,null)) {
            $message = get_string('confirmdeletemyrole', 'local_adsafe', $getmyrolesql);
            $continue = new moodle_url('/local/adsafe/churchrolelist.php', array('roleid'  => $roleid,
                                                                          'action'  => 'del',
                                                                          'confirm' => 1,
                                                                          'sesskey' => sesskey()));
            $cancel = new moodle_url('/local/adsafe/churchrolelist.php');

            return self::confirm($message, $continue, $cancel);
        }
        return false;
    }
    
    
    /**
     * Member role delete confirmation screen.
     * Refer to 3.1.3 of specification.
     *
     * @param integer $roleid  role id
     * @return string|boolean
     */
    public function member_role_delete_confirmation($roleid) {
        global $DB;

        $memberrolesql = "SELECT lamr.id, lamr.locationid, lamr.userid, CONCAT(u.lastname,' ', u.firstname) as username, lar.name as rolename, lal.name as locationname
                          FROM {local_adsafe_member_role} lamr
                          JOIN {local_adsafe_role} lar
                          ON lar.id = lamr.roleid
                          JOIN {local_adsafe_location} lal
                          ON lal.id = lamr.locationid
                          JOIN {user} u
                          ON u.id = lamr.userid
                          WHERE lamr.id = $roleid";

        if ($getmemberrolesql = $DB->get_record_sql($memberrolesql,null)) {
            
            
            
            $message = get_string('confirmdeletememberrole', 'local_adsafe') . 'Name : ' . $getmemberrolesql->username . '<br />Role : ' .$getmemberrolesql->rolename . '<br />Location : ' . $getmemberrolesql->locationname;
            $continue = new moodle_url('/local/adsafe/coordinatorroleedit.php', array('roleindex' => $getmemberrolesql->id,
                                                                                      'userid' => $getmemberrolesql->userid,
                                                                          'locationid'  => $getmemberrolesql->locationid,
                                                                          'action'  => 'del',
                                                                          'confirm' => 1,
                                                                          'sesskey' => sesskey()));
            
            $cancel = new moodle_url('/local/adsafe/coordinatorroleedit.php',array('roleindex' => $getmemberrolesql->id,
                                                                                        'userid' => $getmemberrolesql->userid,
                                                                                        'locationid'  => $getmemberrolesql->locationid,
                                                                                         'action'  => 'edi',
                                                                                         'sesskey' => sesskey()));

            return self::confirm($message, $continue, $cancel);
        }
        return false;
    }
    
    
    public function verify_working_with_children_card_list($userid,$locationid,$verifieduserid) {
         global $CFG, $DB,$PAGE;
        //$url     = new moodle_url('/local/adsafe/churchoreventedit.php'.'?memid='.$memid.'&action='.$action.'&sesskey='.$sesskey);
        //$churchoreventediturl = new moodle_url('/local/adsafe/churchoreventedit.php');
        //$churchoreventediturl = new moodle_url('/local/adsafe/churchoreventedit.php'.'?memid='.$memid.'&action='.$action.'&sesskey='.$sesskey);
        
        // Add new Church and event form.
        $out = self::heading(get_string('workingwithchildrencheck', 'local_adsafe'));
        
        //$memberrecord = \local_adsafe\utils::get_record_from_memberid($memid);
        
        /*$editchurchandeventform = new \local_adsafe\forms\churchsandeventseditform('', array('conferencename' => $memberrecord->conferencename,
                                                                                               'name' => $memberrecord->name,
                                                                                               'starttime' => $memberrecord->starttime,
                                                                                               'main' => $memberrecord->main,
                                                                                               'activated' => $memberrecord->activated
        ));*/
        
        
        
        $coordinatorwwceditform = new \local_adsafe\forms\coordinatorwwceditform('',array('userid'=>$userid,'locationid'=>$locationid, 'verifieduserid'=>$verifieduserid));
        
        /*if($coordinatorwwceditform->is_cancelled()) {
            //redirect(new moodle_url("/local/adsafe/coordinatordashboardlist.php",array('locationid'=>$locationid)));
           // exit;
        }*/
        
        if ($data = $coordinatorwwceditform->get_data() and confirm_sesskey()) {
            
            //var_dump($data);
            

            
            //var_dump($data);
            
            /*if(\local_adsafe\utils::update_wwc_verified_record_through_wwcvid($data)){
                //redirect(new moodle_url("/local/adsafe/coordinatordashboardlist.php"));
                echo('Done');
            } else {
                echo('error');
            }*/
            
            
           // $editchurchandeventform = new \local_adsafe\forms\churchsandeventseditform('',array('memid'=>$data->memid));
            
           // redirect($CFG->wwwroot."/local/adsafe/churchoreventedit.php?action=redir");
            
          

                 
            
                //redirect($churchoreventurl);
                
        }
        
        ob_start();
        $coordinatorwwceditform->display();
        $out .= ob_get_contents();
        ob_end_clean();
        
        return $out;
    }
    
    /**
     * New wwc verification record confirmation screen.
     * Refer to 3.1.3 of specification.
     *
     * @param integer $wwcvid
     * @return string|boolean
     */
    public function new_wwc_verification_record_confirmation($wwcvid) {
        global $DB;
        $newwwcsql = "SELECT lawv.*, CONCAT(u.lastname,' ', u.firstname) AS username, lal.name AS locationname
                      FROM mdl_local_adsafe_wwc_verify lawv
                      JOIN mdl_local_adsafe_location lal
                      ON lal.id = lawv.locationid
                      JOIN mdl_user u
                      ON u.id = lawv.spsid
                      WHERE lawv.id = $wwcvid";
        
        if ($getnewwwcrecord = $DB->get_record_sql($newwwcsql,null)) {
            
            $message = get_string('newwwcverifiedrecordconfirmed', 'local_adsafe') . '<br />Name : ' . $getnewwwcrecord->username .'<br />Location : ' . $getnewwwcrecord->locationname;
            $continue = new moodle_url('/local/adsafe/coordinatorwwcedit.php', array('wwcvid' => $getnewwwcrecord->id,
                                                                                     'userid' => $getnewwwcrecord->spsid,
                                                                                     'locationid' => $getnewwwcrecord->locationid,
                                                                                     'action'  => 'new',
                                                                                     'confirm' => 1,
                                                                                     'sesskey' => sesskey()));
            
            $cancel = new moodle_url('/local/adsafe/coordinatorwwcedit.php',array('userid' => $getnewwwcrecord->spsid,
                                                                                  'locationid'  => $getnewwwcrecord->locationid,
                                                                                  'action'  => 'edi',
                                                                                  'sesskey' => sesskey()));
            return self::confirm($message, $continue, $cancel);
        }
        return false;
    }
    
    
     /**
     * Tell user there is no active church will effect wwc verification of coordinator
     *
     * @param integer $userid  current user id 
     * @return string|boolean
     */
    public function wwc_verification_save_confirmation($userid,$data) {
        global $DB;
        
        
        $sql = "SELECT COUNT(lam.id) as counter
                FROM {local_adsafe_member} lam
                WHERE lam.activated = 1
                AND lam.userid = $userid";
        
        $getsql = $DB->get_field_sql($sql,null);

        if($getsql <= 0) {
            $message = get_string('warningmsgforuser', 'local_adsafe');
            $continue = new moodle_url('/local/adsafe/workingwithchildrencard.php', array('memid'  => $userid,
                                                                                          'action'  => 'tsave',
                                                                                          'confirm' => 1,
                                                                                          'sesskey' => sesskey(),
                                                                                          'spsuserid' =>$data->spsuserid,
                                                                                          'wwcindicator' =>$data->wwcindicator,
                                                                                          'state' =>$data->state,
                                                                                          'nameoncard' =>$data->nameoncard,
                                                                                          'dateofbirth' =>$data->dateofbirth,
                                                                                          'cardnumber' =>$data->cardnumber,
                                                                                          'expirydate' =>$data->expirydate,
                                                                                          'timecreated' =>$data->timecreated));
            /*$continue = new moodle_url('/local/adsafe/churchoreventlist.php');*/                                                                         
            
            $cancel = new moodle_url('/local/adsafe/churchoreventlist.php');

            /*$cancel = new moodle_url('/local/adsafe/workingwithchildrencard.php');*/

            return self::confirm($message, $continue, $cancel);
        }
        return false;
    }
}