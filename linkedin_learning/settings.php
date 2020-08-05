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
 * Handles displaying the calendar block.
 *
 * @package    block_linkedin_learning
 * @copyright  2020 Walmart
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_heading('linkedin_learning_heading', 
                                                get_string('linkedin_setting_title', 'block_linkedin_learning'),
                                                get_string('linkedin_setting_description', 'block_linkedin_learning')
                                            ));

    $settings->add(new admin_setting_configtext('linkedin_client_id', new lang_string('linkedin_client_id', 'block_linkedin_learning'),'','',PARAM_ALPHANUMEXT));
    $settings->add(new admin_setting_configtext('linkedin_client_secret', new lang_string('linkedin_client_secret', 'block_linkedin_learning'),'','',PARAM_ALPHANUMEXT));
	
	$settings->add(new admin_setting_configtext('linkedin_client_carousel', new lang_string('linkedin_client_carousel_title', 'block_linkedin_learning'), new lang_string('linkedin_client_carousel_description', 'block_linkedin_learning'), 4, PARAM_INT));
	
	$options = array(1 => 'Once in a Day', 2 => 'Once in a week', 3 => 'Once in a month');
    $settings->add(new admin_setting_configselect('linkedin_cron_run', new lang_string('linkedin_cron_title', 'block_linkedin_learning'),new lang_string('linkedin_cron_description', 'block_linkedin_learning'), 0, $options));
}
