<?php
/**
 * ADSAFE
 *
 * Core library functions.
 *
 * @package    local_adsafe
 * @author     Ken Change (@link kenc@pukunui.com)
 * @copyright  2018 Pukunui (@link https://pukunui.com/)
 * @license    https://www/gnu.org/copyleft/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Extend the global navigation.
 *
 * @param global_navigation $nav
 */
function local_adsafe_extend_navigation(global_navigation $nav) {
    $parentnode = navigation_node::create(
        get_string('churchoreventnav', 'local_adsafe'),
        null,
        navigation_node::TYPE_CUSTOM,
        null,
        null,
        new pix_icon('i/folder', ''));

    /*if (has_capability('local/evtp:manage', context_system::instance())) {
        $listurl = new moodle_url('/local/evtp/templateplanlist.php');
        $listnode = navigation_node::create(
            get_string('templateplanlist', 'local_evtp'),
            $listurl,
            navigation_node::NODETYPE_LEAF,
            null,
            null,
            new pix_icon('i/settings', ''));
        $parentnode->add_node($listnode);
    }*/

    $newaccounturl = new moodle_url('/local/adsafe/churchoreventlist.php');
    $newaccountnode = navigation_node::create(
        get_string('myaccount', 'local_adsafe'),
        $newaccounturl,
        navigation_node::NODETYPE_LEAF,
        null,
        null,
        new pix_icon('i/settings', ''));
    $parentnode->add_node($newaccountnode);

    $codashboardurl = new moodle_url('/local/adsafe/coordinatordashboardlist.php');
    $codashboardnode = navigation_node::create(
        get_string('coordinatordashboard', 'local_adsafe'),
        $codashboardurl,
        navigation_node::NODETYPE_LEAF,
        null,
        null,
        new pix_icon('t/groupv', ''));
    $parentnode->add_node($codashboardnode);

    /*$searchurl = new moodle_url('/local/evtp/plansearch.php');
    $searchnode = navigation_node::create(
        get_string('trainingplansearch', 'local_evtp'),
        $searchurl,
        navigation_node::NODETYPE_LEAF,
        null,
        null,
        new pix_icon('i/settings', ''));
    $parentnode->add_node($searchnode);*/

    $nav->add_node($parentnode);
}
