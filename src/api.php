<?php
header("Content-Type: application/json");

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/models/Student.php';
require_once __DIR__ . '/models/Teacher.php';
require_once __DIR__ . '/models/Admin.php';
require_once __DIR__ . '/models/Course.php';
require_once __DIR__ . '/models/Assignment.php';

function respond(array $payload, int $code = 200): void {
    http_response_code($code);
    echo json_encode($payload);
    exit;
}

function fetchRequired(PDO $db, string $sql, string $what): array {
    $row = $db->query($sql)->fetch();
    if (!$row) {
        respond([
            "status"  => "error",
            "message" => "Missing {$what} in the database. Import schema.sql first."
        ], 500);
    }
    return $row;
}

try {
    $db = (new Database())->getConnection();

    // Load default records from MySQL
    $teacherRow = fetchRequired($db, "SELECT * FROM users WHERE user_id = 'U-101'", "teacher U-101");
    $studentRow = fetchRequired($db, "SELECT * FROM users WHERE user_id = 'U-202'", "student U-202");
    $courseRow  = fetchRequired($db, "SELECT * FROM courses WHERE course_id = 'C-301'", "course C-301");
    $assignRow  = fetchRequired($db, "SELECT * FROM assignments WHERE assignment_id = 'A-401'", "assignment A-401");

    $teacher    = new Teacher($teacherRow['user_id'], $teacherRow['name'], $teacherRow['email'], 'T-01', $db);
    $student    = new Student($studentRow['user_id'], $studentRow['name'], $studentRow['email'], 'S-2026', $db);
    $course     = new Course($courseRow['course_id'], $courseRow['title'], $courseRow['course_code'], $db);
    $assignment = new Assignment($assignRow['assignment_id'], $assignRow['title'], $assignRow['due_date']);

    // ---------- POST actions ----------
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input  = json_decode(file_get_contents("php://input"), true) ?? $_POST;
        $action = $input['action'] ?? '';

        switch ($action) {
            case 'join_class':
                $code = trim($input['course_code'] ?? '');
                if ($code === '') {
                    respond(["status" => "error", "message" => "Error: Course code cannot be empty."], 400);
                }
                $message = $student->joinClass($code);
                $ok = strpos($message, 'Error:') !== 0;
                respond(["status" => $ok ? "success" : "error", "message" => $message], $ok ? 200 : 404);

            case 'submit_assignment':
                $fileUrl = trim($input['file_url'] ?? '');
                if ($fileUrl === '') {
                    respond(["status" => "error", "message" => "Error: File URL cannot be empty."], 400);
                }
                $submission = $student->submitAssignment($assignment->getAssignmentId(), $fileUrl);
                respond([
                    "status"  => "success",
                    "message" => "Submission saved to database (ID: " . $submission->getSubmissionId() . ")."
                ]);

            case 'grade_submission':
                $subId    = trim($input['submission_id'] ?? '');
                $score    = $input['score'] ?? '';
                $feedback = trim($input['feedback'] ?? '');
                if ($subId === '' || !is_numeric($score) || $feedback === '') {
                    respond(["status" => "error", "message" => "Error: Submission, score and feedback are required."], 400);
                }
                $teacher->gradeSubmission($subId, (float) $score, $feedback);
                respond(["status" => "success", "message" => "Grade saved and released to student."]);

            default:
                respond(["status" => "error", "message" => "Unknown action."], 400);
        }
    }

    // ---------- GET: dashboard state ----------
    $enrollCheck = $db->prepare("SELECT 1 FROM enrollments WHERE student_id = ? AND course_id = ?");
    $enrollCheck->execute([$student->getUserId(), $course->getCourseId()]);
    $isEnrolled = (bool) $enrollCheck->fetch();

    $submissions = $db->query("
        SELECT s.submission_id, s.submission_date, s.file_url, u.name AS student_name,
               g.score, g.feedback
        FROM submissions s
        JOIN users u ON s.student_id = u.user_id
        LEFT JOIN grades g ON s.submission_id = g.submission_id
        ORDER BY s.submission_date DESC
    ")->fetchAll();

    respond([
        "course" => [
            "title" => $course->getTitle(),
            "code"  => $course->getCourseCode()
        ],
        "assignment" => [
            "title"   => $assignment->getTitle(),
            "dueDate" => $assignment->getDueDate()
        ],
        "student" => [
            "name"       => $student->getName(),
            "isEnrolled" => $isEnrolled
        ],
        "teacher" => [
            "name" => $teacher->getName()
        ],
        "submissions" => $submissions
    ]);

} catch (InvalidArgumentException | RuntimeException $e) {
    // Expected, user-facing problems (bad score, not enrolled, DB connection...)
    respond(["status" => "error", "message" => $e->getMessage()], 400);
} catch (Throwable $e) {
    respond(["status" => "error", "message" => "Server error: " . $e->getMessage()], 500);
}
