<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Capabilities
 *
 * @package    local
 * @subpackage adsafe
 * @author     Ken Chang, Pukunui {@link http://pukunui.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

$capabilities = array(
                    'local/adsafe:conferencelistview' => array(
                         'riskbitmask' => RISK_DATALOSS,
                         'captype'     => 'read',
                         'contextlevel' => CONTEXT_SYSTEM,
                         'archetypes'  => array(
                             'manager'   => CAP_ALLOW,
                             //'user'   => CAP_ALLOW
                             //'conference'   => CAP_ALLOW,
                             )
                         ),
                    'local/adsafe:conferencelistedit' => array(
                        'riskbitmask' => RISK_DATALOSS,
                        'captype'     => 'write',
                        'contextlevel' => CONTEXT_SYSTEM,
                        'archetypes'  => array(
                            'manager'       => CAP_ALLOW,
                            //'user'   => CAP_ALLOW
                            //'conference'   => CAP_ALLOW,
                            )
                        ),
                    'local/adsafe:locationforaconferencelistview' => array(
                         'riskbitmask' => RISK_DATALOSS,
                         'captype'     => 'read',
                         'contextlevel' => CONTEXT_SYSTEM,
                         'archetypes'  => array(
                             'manager'   => CAP_ALLOW,
                             //'user'   => CAP_ALLOW
                             //'conference'   => CAP_ALLOW,
                             )
                         ),
                    'local/adsafe:locationforaconferencelistedit' => array(
                        'riskbitmask' => RISK_DATALOSS,
                        'captype'     => 'write',
                        'contextlevel' => CONTEXT_SYSTEM,
                        'archetypes'  => array(
                            'manager'       => CAP_ALLOW,
                            //'user'   => CAP_ALLOW
                            //'conference'   => CAP_ALLOW,
                            )
                        ),
                    'local/adsafe:adminmemberlistview' => array(
                         'riskbitmask' => RISK_DATALOSS,
                         'captype'     => 'read',
                         'contextlevel' => CONTEXT_SYSTEM,
                         'archetypes'  => array(
                             'manager'   => CAP_ALLOW,
                             )
                         ),
                    'local/adsafe:pastormemberlistview' => array(
                        'riskbitmask' => RISK_DATALOSS,
                        'captype'     => 'read',
                        'contextlevel' => CONTEXT_SYSTEM,
                        'archetypes'  => array(
                            'user'   => CAP_ALLOW,
                            )
                        ),
                        
                    'local/adsafe:churchandeventlistview' => array(
                    'riskbitmask'  => RISK_PERSONAL,
                    'captype'      => 'read',
                    'contextlevel' => CONTEXT_SYSTEM,
                    'archetypes'  => array(
                            'user'   => CAP_ALLOW,
                            )
                    ),
);