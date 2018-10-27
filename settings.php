<?php
/*
 * TELL CENTRE
 *
 * Plugin settings.
 *
 * @package    : local_tellcent
 * @copyright  : 2014 Pukunui
 * @author     : Priya Ramakrishnan, Pukunui {@link http://pukunui.com}
 * @license    : http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

$ADMIN->add('root', new admin_category('local_adsafe', get_string('pluginname', 'local_adsafe')));

$ADMIN->add('local_adsafe', new admin_externalpage('conference', get_string('conferencelist', 'local_adsafe'),
            $CFG->wwwroot."/local/adsafe/conferencelist_index.php",
                        'local/adsafe:conferencelistview'));
$ADMIN->add('local_adsafe', new admin_externalpage('member', get_string('memberlist', 'local_adsafe'),
            $CFG->wwwroot."/local/adsafe/memberlist_index.php",
                        'local/adsafe:conferencelistview'));
$ADMIN->add('local_adsafe', new admin_externalpage('coordinator', get_string('coordinatorlist', 'local_adsafe'),
            $CFG->wwwroot."/local/adsafe/coordinatorlist_index.php",
                        'local/adsafe:conferencelistview'));
