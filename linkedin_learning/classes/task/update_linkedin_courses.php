<?php
/**
 * Background task to update Linkedin Learning courses
 *
 * @package   local_linkdin_learning
 * @copyright 2020 Wal-Mart
 *
 */
 
namespace block_linkedin_learning\task;

require_once(__DIR__.'/../../../../config.php');

class update_linkedin_courses extends \core\task\scheduled_task {
	/**
     * Return the task's name as shown in admin screens.
     *
     * @return string
     */
    public function get_name() {
        return get_string('linkedin_update_course', 'block_linkedin_learning');
    }
 
    /**
     * Execute the task.
     */
    public function execute() {
		$obj = new \block_linkedin_learning_core();
    }
}