<?php
/**
 * Generate Attendance SQL Data
 * Creates attendance records from January 1 to February 12, 2026
 * For 20 students, subject_id=1, period_number=1
 */

// Students who are frequently absent (4 students)
$frequently_absent = [5, 10, 15, 20];

// Generate dates from Jan 1 to Feb 12, 2026 (weekdays only)
$start_date = new DateTime('2026-01-01');
$end_date = new DateTime('2026-02-12');
$attendance_id = 100;

$sql_output = "";

while ($start_date <= $end_date) {
    $day_of_week = $start_date->format('l');
    
    // Skip weekends
    if ($day_of_week !== 'Saturday' && $day_of_week !== 'Sunday') {
        $date_str = $start_date->format('Y-m-d');
        
        // Mark attendance for all 20 students
        for ($student_id = 1; $student_id <= 20; $student_id++) {
            // Students 5, 10, 15, 20 are absent
            $status = in_array($student_id, $frequently_absent) ? 'absent' : 'present';
            
            $sql_output .= "INSERT INTO attendance (id, student_id, subject_id, period_number, date, day_of_week, status, marked_by) VALUES ";
            $sql_output .= "($attendance_id, $student_id, 1, 1, '$date_str', '$day_of_week', '$status', 10);\n";
            
            $attendance_id++;
        }
    }
    
    $start_date->modify('+1 day');
}

// Write to file
$header = "-- ================================================================\n";
$header .= "-- Attendance Records: January 1 - February 12, 2026\n";
$header .= "-- Subject ID: 1 (Programming in C), Period: 1\n";
$header .= "-- Teacher ID: 10 (teacher_fine)\n";
$header .= "-- Total Records: " . ($attendance_id - 100) . "\n";
$header .= "-- ================================================================\n\n";

file_put_contents('attendance_records.sql', $header . $sql_output);

echo "✅ Generated " . ($attendance_id - 100) . " attendance records!\n";
echo "📁 File saved: attendance_records.sql\n";
echo "\nNow concatenating with sample_data_with_attendance.sql...\n";

// Combine files
$sample_data = file_get_contents('sample_data_with_attendance.sql');
$final_sql = $sample_data . "\n" . $header . $sql_output;

file_put_contents('complete_sample_data.sql', $final_sql);
echo "✅ Complete file created: complete_sample_data.sql\n";
echo "\n🎉 Ready to import!\n";
?>
