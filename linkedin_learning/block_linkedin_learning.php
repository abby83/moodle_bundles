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

class block_linkedin_learning extends block_base {

    /**
     * Initialise the block.
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_linkedin_learning');
    }

    /**
     * Enable blocks settings file
     */
    function has_config() {
        return true;
    }


    /**
     * Return the content of this block.
     *
     * @return stdClass the content
     */
    public function get_content() {
        global $CFG, $OUTPUT;
		
		if ($this->content !== null) {
            return $this->content;
        }
		$courses = $this->linkedin_courses();
		$output = $OUTPUT->render_from_template('block_linkedin_learning/linkedin_courses',$courses);
		
		$this->content = new StdClass;

        $this->content->text = $output;
        
        return $this->content;
    }
	
	/**
	 * GET all course data from LinkedIn
	 * 
     * @output: array $params 
     */
	 public function linkedin_courses() : array {
		 global $DB;
		 $params = array();
		 $sql = "SELECT id, urn, title, completiontime, 
						TIME_FORMAT(SEC_TO_TIME(completiontime),'%kh %im') AS timeToCompelete, 
						weblaunchurl, author, image, courselevel
							FROM {linkedin_learning_courses}";
		 $courses = $DB->get_records_sql($sql);
		 if (count($courses) > 0) {
			 $i = 0;
			 foreach($courses AS $key => $val) {
				$params['course'][$i]['title'] = $val->title;
				
				$totalTime_arr = explode(" ", $val->timetocompelete);
				if ($totalTime_arr[0] == '0h') {
					$params['course'][$i]['timetocompelete'] = $totalTime_arr[1];
				} else {
					$params['course'][$i]['timetocompelete'] = $val->timetocompelete;
				}
				
				$params['course'][$i]['weblaunchurl'] = $val->weblaunchurl;
				$params['course'][$i]['author'] = $val->author;
				$params['course'][$i]['image'] = $val->image;
				$params['course'][$i]['courselevel'] = $val->courselevel; 
				$i++;
			 }
		 }
		 return $params;
	 }
}
