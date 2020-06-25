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
 * Version details
 *
 * @package    block_linkedin_learning
 * @copyright  2020 Walmart
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2020062000;        // The current plugin version (Date: YYYYMMDDXX)
$plugin->requires  = 2019051100;        // Requires this Moodle version
$plugin->component = 'block_linkedin_learning'; // Full name of the plugin (used for diagnostics)





<?php
/**
 * LinkdIn Learining API core helper functions and callbacks
 *
 * @package   local_linkdin_learning
 * @copyright 2020 Wal-Mart
 *
 */

require_once(__DIR__.'/../../../config.php');
require_once(__DIR__.'/../../../lib/filelib.php');

class local_linkdin_learning_core {
	
	/** @var string API client id. */
	protected $clientid = "";
	
	/** @var string API client secret. */
	protected $clientsecrect = "";
    
	/**
     * class constructor
     */
	function __construct() {
		global $CFG;
		
		$clientid = $this->clientid = '866zqr78jf0yaq';
		//$this->clientid = $CFG->linkdin_client_id;
		
		$clientsecrect = $this->clientsecrect = 'ls6s9qZ95CD7m61p';
		//$this->clientsecrect = $CFG->linkdin_client_secret;
		
		$accesstoken = $this->accesstoken_generator($clientid, $clientsecrect);
	}
	
	protected function accesstoken_generator(string $clientid, string $clientsecrect) {
		$serverurl = 'https://www.linkedin.com/oauth/v2/accessToken';
		$curl = new curl();
		$curl->setHeader('Content-type: application/x-www-form-urlencoded');
		$resp = $curl->post($serverurl, array(
												'grant_type'=> 'client_credentials',
												'client_id'=> $clientid, 
												'client_secret'=> $clientsecrect));
		print_r($resp);
	}
	
  
}
