<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>;.

/**
 * @package     local_pintar_analytics
 * @copyright   2022 Prihantoosa <toosa@digitos.id> 
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
global $DB;

require_once('../../config.php');
require_once('../../completion/classes/external.php');
require_login();

$courses = get_courses();
# var_dump($courses);
# die();

$courseid = $COURSE->id;
# $courseidx = $_GET('courseidx');
# echo "Test";
# echo $_REQUEST('courseidx');
# echo $courseidx;
# die();

foreach ($courses as $courseid => $course){
#         if($course->id==1)continue;
#         $coursecontext = context_course::instance($course->id);
#         $enrolledstudents = get_enrolled_users($coursecontext, 'moodle/course:isincompletionreports');
#         $already70='';
#         $still30='';
#         foreach ($enrolledstudents as $user) {
#                 $course_user_stat = core_completion_external::get_activities_completion_status($course->id,$user->id);
#                 $activities = $course_user_stat['statuses'];
#                 $totalactivities = count($activities);
#                 $completed = 0;
#                 foreach($activities as $activity){
#                         if($activity['timecompleted']!=0)$completed+=1;
#                 }
#                 $studentcompletion=($completed/$totalactivities)*100;
#                 if($studentcompletion>70)$already70+=1;
#                 else $still30 +=1;
# 
#         }
#         echo $course->fullname." diatas 70%: ".$already70."<br>";
#         echo $course->fullname." dibawah 30%: ".$still30."<br>";
# 
}
# 
# die();

$context = context_course::instance($courseid);
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/block/pintar_analytic/overview1.php'));
$PAGE->set_pagelayout('course');
$PAGE->set_title($SITE->fullname);
# $string['pluginname']='Greetings';
$PAGE->set_heading(get_string('pluginname','local_pintar_analytics'));

echo $OUTPUT->header();

if (isloggedin()) {
    echo '<h2>PIC: ' . fullname($USER) . '</h2>';
} else {
    echo '<h2>Anda belum login</h2>';
}

echo '<h2>Greetings, user</h2>';

// 
// Membaca data yang dikirim melalui URL berupa array yang dikirim menggunakan 
// $url + http_build_query($dataid);
//
$idArray = explode('&',$_SERVER["QUERY_STRING"]);
foreach ($idArray as $index => $avPair) {
 list($ignore, $value) = explode('=',$avPair);
 $id[$index] = $value;
}


$chart = new core\chart_bar();
$serie1 = new core\chart_series('Penyelesaian <30%', [65, 94, 80,71]);
$serie2 = new core\chart_series('Penyelesaian >70%', [22, 6, 9,20]);
$serie3 = new core\chart_series('Penugasan >90%', [16, 8.5,7.6,20.3 ]);
# $serie4 = new core\chart_series('My series title4', [400, 460, 1120]);

$chart->set_title('Keterlibatan dan Keaktifan Peserta');
$chart->add_series($serie1);
$chart->add_series($serie2);
$chart->add_series($serie3);
# $chart->add_series($serie4);
$chart->set_labels(['PTM Kepsek', 'PJJ-SMP', 'PJJ-SD', 'PJJ-Kepsek']);
# $chart->set_labels($labels);

# echo $OUTPUT->render("Test");
echo $OUTPUT->render($chart);

# foreach ($id as $iduser) {
#  echo $OUTPUT->render($iduser);
# }

echo $OUTPUT->footer();
