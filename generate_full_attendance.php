<?php
/**
 * Generate Complete Attendance Data
 * Creates attendance records for ALL 5 subjects
 * From January 1 to February 12, 2026
 * For 20 students
 */

// Timetable structure (based on complete_semester_data.sql)
$timetable = [
    'Monday' => [
        1 => 1,  // IT101 - Programming Fundamentals
        2 => 3,  // IT103 - Mathematics I
        3 => 2,  // IT102 - Digital Electronics
        4 => 4,  // IT104 - Communication Skills
        5 => 5,  // IT105 - Computer Organization
        6 => 1   // IT101 - Programming Fundamentals
    ],
    'Tuesday' => [
        1 => 2,  // IT102
        2 => 1,  // IT101
        3 => 4,  // IT104
        4 => 3,  // IT103
        5 => 2,  // IT102
        6 => 5   // IT105
    ],
    'Wednesday' => [
        1 => 3,  // IT103
        2 => 4,  // IT104
        3 => 5,  // IT105
        4 => 1,  // IT101
        5 => 3,  // IT103
        6 => 2   // IT102
    ],
    'Thursday' => [
        1 => 4,  // IT104
        2 => 5,  // IT105
        3 => 1,  // IT101
        4 => 2,  // IT102
        5 => 4,  // IT104
        6 => 3   // IT103
    ],
    'Friday' => [
        1 => 1,  // IT101
        2 => 3,  // IT103
        3 => 2,  // IT102
        4 => 5,  // IT105
        5 => 4,  // IT104
        6 => 1   // IT101
    ]
];

// Teacher IDs for each subject
$subject_teachers = [
    1 => 10,  // IT101 -> teacher1 (Fine Teacher or teacher_fine)
    2 => 11,  // IT102 -> teacher2
    3 => 12,  // IT103 -> teacher3
    4 => 13,  // IT104 -> teacher4
    5 => 14   // IT105 -> teacher5
];

// Students who are frequently absent (specifically for student_ids 5, 10, 15, 20)
$frequently_absent = [5, 10, 15, 20];

// Generate dates from Jan 1 to Feb 12, 2026 (weekdays only)
$start_date = new DateTime('2026-01-01');
$end_date = new DateTime('2026-02-12');
$attendance_id = 100;

$sql_output = "";
$total_records = 0;

while ($start_date <= $end_date) {
    $day_of_week = $start_date->format('l');
    
    // Skip weekends
    if ($day_of_week !== 'Saturday' && $day_of_week !== 'Sunday') {
        $date_str = $start_date->format('Y-m-d');
        
        // Check if timetable exists for this day
        if (isset($timetable[$day_of_week])) {
            // For each period in the day
            foreach ($timetable[$day_of_week] as $period_number => $subject_id) {
                $teacher_id = $subject_teachers[$subject_id];
                
                // Mark attendance for all 20 students
                for ($student_id = 1; $student_id <= 20; $student_id++) {
                    // Students 5, 10, 15, 20 are absent
                    $status = in_array($student_id, $frequently_absent) ? 'absent' : 'present';
                    
                    $sql_output .= "INSERT INTO attendance (id, student_id, subject_id, period_number, date, day_of_week, status, marked_by) VALUES ";
                    $sql_output .= "($attendance_id, $student_id, $subject_id, $period_number, '$date_str', '$day_of_week', '$status', $teacher_id);\n";
                    
                    $attendance_id++;
                    $total_records++;
                }
            }
        }
    }
    
    $start_date->modify('+1 day');
}

// Write to file
$header = "-- ================================================================\n";
$header .= "-- Complete Attendance Records: January 1 - February 12, 2026\n";
$header .= "-- ALL 5 SUBJECTS (IT101, IT102, IT103, IT104, IT105)\n";
$header .= "-- 20 Students, All Periods\n";
$header .= "-- Total Records: $total_records\n";
$header .= "-- ================================================================\n\n";

file_put_contents('full_attendance_records.sql', $header . $sql_output);

echo "✅ Generated $total_records attendance records!\n";
echo "📊 Breakdown:\n";
echo "   - 5 subjects\n";
echo "   - 6 periods per day\n";
echo "   - 20 students per period\n";
echo "   - 31 weekdays (Jan 1 - Feb 12)\n";
echo "   - Pattern: Students 5, 10, 15, 20 always absent\n";
echo "\n📁 File saved: full_attendance_records.sql\n";

// Now create the complete file with user data
echo "\nCreating complete sample data file...\n";

// Read the sample data header (users and student enrollment)
$sample_header = file_get_contents('sample_data_with_attendance.sql');

// Combine everything
$final_sql = $sample_header . "\n\n" . $header . $sql_output;

file_put_contents('complete_full_attendance.sql', $final_sql);

echo "✅ Complete file created: complete_full_attendance.sql\n";
echo "   Includes:\n";
echo "   - 1 teacher (teacher_fine) + student data\n";
echo "   - 20 students enrolled in BSc IT Sem 1\n";
echo "   - $total_records attendance records across all 5 subjects\n";
echo "\n🎉 Ready to import!\n";
?>
