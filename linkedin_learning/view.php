<?php

require_once('../../config.php');
echo 1;
$obj = new \block_linkedin_learning_core();
$obj->get_popular_courses();
$obj->get_topic_popular_courses('diversity-and-inclusion');
echo 23;