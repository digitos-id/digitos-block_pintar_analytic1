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
 * Pintar Analytic Dashboard block definition
 *
 * @package    block_pintar_analytic
 * @copyright  2022 Prihantoosa
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 use block_pintar_analytic\pintar_analytic;
 use block_pintar_analytic\defaults;

 /**
 * Pintar Analytic Dashboard block class
 *
 * @copyright 2022 Priihantoosa
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_pintar_analytic extends block_base {

    /**
     * Sets the block title
     *
     * @return void
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_pintar_analytic');
    }

    /**
     *  we have global config/settings data
     *
     * @return bool
     */
    public function has_config() {
        return true;
    }

    /**
     * Controls the block title based on instance configuration
     *
     * @return bool
     */
    public function specialization() {
        if (isset($this->config->progressTitle) && trim($this->config->progressTitle) != '') {
            $this->title = format_string($this->config->progressTitle);
        }
    }

    /**
     * Controls whether multiple instances of the block are allowed on a page
     *
     * @return bool
     */
    public function instance_allow_multiple() {
        return !self::on_site_page($this->page);
    }

    /**
     * Controls whether the block is configurable
     *
     * @return bool
     */
    public function instance_allow_config() {
        return !self::on_site_page($this->page);
    }

    /**
     * Defines where the block can be added
     *
     * @return array
     */
    public function applicable_formats() {
        return array(
            'course-view'    => true,
            'site'           => true,
            'mod'            => false,
            'my'             => true
        );
    }

    /**
     * Creates the blocks main content
     *
     * @return string
     */
    public function get_content() {
        // If content has already been generated, don't waste time generating it again.
        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';
        // $barinstances = array();

        // Guests do not have any progress. Don't show them the block.
        if (!isloggedin() or isguestuser()) {
            return $this->content;
        }

        if (self::on_site_page($this->page)) {
            // Draw the multi-bar content for the Dashboard and Front page.
            // if (!$this->prepare_dashboard_content($barinstances)) {
            //     return $this->content;
            // }
            // Rencana untuk memilih category
            $this->content->text .= '<em>Report by Category</em><br><ul><li>Cat 1</li><li>Cat 2</li></ul>';

            return $this->content;
        } else {
            // Gather content for block on regular course.
            // if (!$this->prepare_course_content($barinstances)) {
            //     return $this->content;
            // }
            $this->content->text .= 'Course Analytics<br>';
            self::siapasaja_enroled_users(null);
            $url = $_SERVER['SERVER_NAME'];    
            $this->content->text .= '<a href='.$url.'/local/pintar_analytics/">Analytics 1</a> | ';
            $this->content->text .= '<a href='.$url.'/block/pintar_analytic/overview1.php">Analytics 2</a>';
            
        }

        return $this->content;
    }

/**
     * Checks whether the given page is site-level (Dashboard or Front page) or not.
     *
     * @param moodle_page $page the page to check, or the current page if not passed.
     * @return boolean True when on the Dashboard or Site home page.
     */
    public static function on_site_page($page = null) {
        global $PAGE;   // phpcs:ignore moodle.PHP.ForbiddenGlobalUse.BadGlobal

        $page = $page ?? $PAGE; // phpcs:ignore moodle.PHP.ForbiddenGlobalUse.BadGlobal
        $context = $page->context ?? null;

        if (!$page || !$context) {
            return false;
        } else if ($context->contextlevel === CONTEXT_SYSTEM && $page->requestorigin === 'restore') {
            return false; // When restoring from a backup, pretend the page is course-level.
        } else if ($context->contextlevel === CONTEXT_COURSE && $context->instanceid == SITEID) {
            return true;  // Front page.
        } else if ($context->contextlevel < CONTEXT_COURSE) {
            return true;  // System, user (i.e. dashboard), course category.
        } else {
            return false;
        }
    }

/**
     * Menampilkan user-user yang enroled pada course tersebut.
     *
     * @return list_of_enrolled_userid.
     *   # Enroled users
     *   # by Toosa
     *  di awal hanya menampilkan course id nya saja
     */
    public static function siapasaja_enroled_users($courseid = null) {
        global $COURSE;   // 
      
        if ($courseid=null) {
            $courseid = $COURSE->id;
        } else {
            return true;
        }

        $context_course = context_course::instance($courseid);
        $enrolled_users = get_enrolled_users($context_course,'',0,'*');
        foreach ($enrolled_users as $enrolled_user) {
                $this->content->text .= $enrolled_user->id.'<br>';
                #echo "$enrolled_user";
            }

        
            # end of Enroled users
        
        return true;
        
    }    
    
}
