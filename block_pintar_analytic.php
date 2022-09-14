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

// require_once('../../config.php'); //disesuaikan path nya

require_once($CFG->dirroot.'/completion/classes/external.php');

//require_once($CFG->wwwroot.'/completion/classes/external.php');
// require_once dirname(dirname(dirname(FILE))).'/completion/classes/external.php);
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
    global $COURSE, $DB, $OUTPUT;

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
            $this->content->text .= 'Course Analytics<br>';

            // Hitung completion
            $courseid = $COURSE->id;
            $this->content->text .= 'Course id'.$courseid.'<br>';
            $coursecontext = context_course::instance($courseid);
            $enrolledstudents = get_enrolled_users($coursecontext, 'moodle/course:isincompletionreports');
            $totalenrolledstudents = count($enrolledstudents);
            $already70=0;
            $still30=0;
            foreach ($enrolledstudents as $user) {
              //  $course_user_stat = core_completion_external::get_activities_completion_status($course->id,$user->id);
                $course_user_stat = $this->custom_get_user_course_completion($courseid,$user->id);
                $activities = $course_user_stat['statuses'];
                // $activities = $DB->get_records('course_modules', array('course' => $courseid, 'completion' => '1'));
                $totalactivities = count($activities);
                $completed = 0;
                $iscomplete = false;
                foreach($activities as $activity){
                    // $ccinfo = new completion_info($activity);
                    // var_dump($ccinfo);
                    // die();
                    // $iscomplete = $ccinfo->is_course_complete($user->id);
                    if($activity['timecompleted']!=0)$completed+=1;
                    // if($iscomplete)$completed+=1;
                }
                $studentcompletion=($completed/$totalactivities)*100;
                if($studentcompletion>69)$already70+=1;
                else $still30 +=1;

            }

            
            $this->content->text .= 'Total students:'.$totalenrolledstudents."<br>";
            $this->content->text .= 'Total activities:'.$totalactivities."<br>";
            $this->content->text .= 'Diatas 70%:'.$already70."<br>";
            $this->content->text .= 'Dibawah 30%:'.$still30."<br>";
            
            // Nilai Prosentase


            // End on nilai prosentase
            
            // End of Hitung Completion

            // Membuat chart

            # $context = context_course::instance($courseid);
            $chart = new core\chart_bar();
            $serie2 = new core\chart_series('Penyelesaian >70%', [22, 6, 9,20]);
            $serie1 = new core\chart_series('Penyelesaian <30%', [65, 94, 80,71]);
            # $serie3 = new core\chart_series('Penugasan >90%', [16, 8.5,7.6,20.3 ]);

            $chart->set_title('Keterlibatan dan Keaktifan Peserta');
            $chart->add_series($serie1);
            $chart->add_series($serie2);
            # $chart->add_series($serie3);
            # # $chart->add_series($serie4);
            # $chart->set_labels(['PTM Kepsek', 'PJJ-SMP', 'PJJ-SD', 'PJJ-Kepsek']);
            
            //Proses render nya masih mentok-tok
        
            $viewchart = $OUTPUT->render($chart);
            $this->content->text .= $viewchart;  

            // End of Membuat Chart

            // Gather content for block on regular course.
            // if (!$this->prepare_course_content($barinstances)) {
            //     return $this->content;
            // }
            
            //self::siapasaja_enroled_users(null);
            // $url = $CFG->wwwroot;
            $url = new moodle_url('/blocks/pintar_analytic/overview1.php');    
            $this->content->text .= '<a href="'.$url.'">Analytics block</a>';
            $url = new moodle_url('/local/pintar_analytics/overview.php');    
            $this->content->text .= '<a href="'.$url.'">Analytics local</a>';
            
        }
// Notice: Trying to get property 'wwwroot' of non-object in /var/www/lms.digitos.id/blocks/pintar_analytic/block_pintar_analytic.php on line 135
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

    public static function custom_get_user_course_completion($courseid,$userid){
        $course = get_course($courseid);
        $user = core_user::get_user($userid, '*', MUST_EXIST);
        core_user::require_active_user($user);

        $completion = new completion_info($course);
        $activities = $completion->get_activities();
        $result = array();
        foreach ($activities as $activity) {

        $cmcompletion = \core_completion\cm_completion_details::get_instance($activity, $user->id);
        $cmcompletiondetails = $cmcompletion->get_details();

        $details = [];
        foreach ($cmcompletiondetails as $rulename => $rulevalue) {
            $details[] = [
                'rulename' => $rulename,
                'rulevalue' => (array)$rulevalue,
            ];
        }
        $result[]=[
            'state'         => $cmcompletion->get_overall_completion(),
            'timecompleted' => $cmcompletion->get_timemodified(),
            'overrideby'    => $cmcompletion->overridden_by(),
            'hascompletion'    => $cmcompletion->has_completion(),
            'isautomatic'      => $cmcompletion->is_automatic(),
            'istrackeduser'    => $cmcompletion->is_tracked_user(),
            'overallstatus'    => $cmcompletion->get_overall_completion(),
            'details'          => $details,
        ];
    }
    $results = array(
        'statuses' => $result,
    );
    return $results;

   }    
    
}
