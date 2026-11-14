<?php
// Server-rendered version of the portal (no JavaScript needed).
// The JavaScript version is index.html + api.php. Use either one.
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/models/Student.php';
require_once __DIR__ . '/models/Teacher.php';
require_once __DIR__ . '/models/Course.php';
require_once __DIR__ . '/models/Assignment.php';

try {
    $db = (new Database())->getConnection();
} catch (RuntimeException $e) {
    http_response_code(500);
    exit("<p>" . htmlspecialchars($e->getMessage()) . "</p>");
}

function fetchRequired(PDO $db, string $sql, string $what): array {
    $row = $db->query($sql)->fetch();
    if (!$row) {
        http_response_code(500);
        exit("<p>Missing " . htmlspecialchars($what) . " in the database. Import schema.sql first.</p>");
    }
    return $row;
}

// Load default users from database
$teacherRow = fetchRequired($db, "SELECT * FROM users WHERE user_id = 'U-101'", "teacher U-101");
$studentRow = fetchRequired($db, "SELECT * FROM users WHERE user_id = 'U-202'", "student U-202");
$courseRow  = fetchRequired($db, "SELECT * FROM courses WHERE course_id = 'C-301'", "course C-301");
$assignRow  = fetchRequired($db, "SELECT * FROM assignments WHERE assignment_id = 'A-401'", "assignment A-401");

$teacher    = new Teacher($teacherRow['user_id'], $teacherRow['name'], $teacherRow['email'], 'T-01', $db);
$student    = new Student($studentRow['user_id'], $studentRow['name'], $studentRow['email'], 'S-2026', $db);
$course     = new Course($courseRow['course_id'], $courseRow['title'], $courseRow['course_code'], $db);
$assignment = new Assignment($assignRow['assignment_id'], $assignRow['title'], $assignRow['due_date']);

$statusMessage = "";

// Handle POST Actions (Post/Redirect/Get so refreshing doesn't resubmit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'join_class') {
            $statusMessage = $student->joinClass(trim($_POST['course_code'] ?? ''));
        } elseif ($action === 'submit_assignment') {
            $fileUrl = trim($_POST['file_url'] ?? '');
            if ($fileUrl !== '') {
                $submission = $student->submitAssignment($assignment->getAssignmentId(), $fileUrl);
                $statusMessage = "Submission saved to database (ID: " . $submission->getSubmissionId() . ").";
            } else {
                $statusMessage = "Error: File URL cannot be empty.";
            }
        } elseif ($action === 'grade_submission') {
            $teacher->gradeSubmission(
                trim($_POST['submission_id'] ?? ''),
                (float) ($_POST['score'] ?? 0),
                trim($_POST['feedback'] ?? '')
            );
            $statusMessage = "Grade saved and released to student.";
        }
    } catch (InvalidArgumentException | RuntimeException $e) {
        $statusMessage = "Error: " . $e->getMessage();
    }

    header("Location: index.php?msg=" . urlencode($statusMessage));
    exit;
}

if (isset($_GET['msg'])) {
    $statusMessage = (string) $_GET['msg'];
}

// Fetch all submissions & grades from MySQL
$allSubmissions = $db->query("
    SELECT s.submission_id, s.submission_date, s.file_url, u.name AS student_name,
           g.score, g.feedback
    FROM submissions s
    JOIN users u ON s.student_id = u.user_id
    LEFT JOIN grades g ON s.submission_id = g.submission_id
    ORDER BY s.submission_date DESC
")->fetchAll();

// Check student enrollment status
$enrollCheck = $db->prepare("SELECT 1 FROM enrollments WHERE student_id = ? AND course_id = ?");
$enrollCheck->execute([$student->getUserId(), $course->getCourseId()]);
$isEnrolled = (bool) $enrollCheck->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS - MySQL & PHP Portal</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px auto; max-width: 850px; line-height: 1.6; color: #222; }
        h1, h2 { border-bottom: 1px solid #ccc; padding-bottom: 6px; }
        .card { border: 1px solid #ddd; padding: 20px; margin-bottom: 24px; border-radius: 4px; }
        .alert { background: #f4f4f4; border-left: 4px solid #222; padding: 12px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f9f9f9; }
        label { display: block; font-weight: bold; margin-top: 10px; }
        input[type="text"], input[type="number"], textarea { width: 100%; padding: 8px; margin-top: 4px; box-sizing: border-box; }
        button { margin-top: 10px; padding: 8px 14px; background: #222; color: #fff; border: none; cursor: pointer; }
        button:hover { background: #444; }
    </style>
</head>
<body>

    <h1>Learning Management System (LMS)</h1>
    <p><strong>Course:</strong> <?= htmlspecialchars($course->getTitle()) ?> (<?= htmlspecialchars($course->getCourseCode()) ?>)</p>

    <?php if ($statusMessage): ?>
        <div class="alert"><?= htmlspecialchars($statusMessage) ?></div>
    <?php endif; ?>

    <!-- STUDENT PORTAL -->
    <div class="card">
        <h2>Student Portal — <?= htmlspecialchars($student->getName()) ?></h2>
        <p><strong>Enrollment Status:</strong> <?= $isEnrolled ? "Enrolled in " . htmlspecialchars($course->getCourseCode()) : "Not Enrolled" ?></p>

        <?php if (!$isEnrolled): ?>
            <form method="POST">
                <input type="hidden" name="action" value="join_class">
                <label>Join Class via Course Code</label>
                <input type="text" name="course_code" value="CS301" required>
                <button type="submit">Join Class</button>
            </form>
        <?php else: ?>
            <form method="POST">
                <input type="hidden" name="action" value="submit_assignment">
                <label>Submit Assignment: <?= htmlspecialchars($assignment->getTitle()) ?> (Due: <?= htmlspecialchars($assignment->getDueDate()) ?>)</label>
                <input type="text" name="file_url" placeholder="Enter filename or URL (e.g., capstone_v1.pdf)" required>
                <button type="submit">Submit Assignment</button>
            </form>
        <?php endif; ?>
    </div>

    <!-- TEACHER PORTAL & SUBMISSION RECORDS -->
    <div class="card">
        <h2>Teacher Portal — <?= htmlspecialchars($teacher->getName()) ?></h2>

        <?php if (count($allSubmissions) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student</th>
                        <th>File</th>
                        <th>Submitted At</th>
                        <th>Grade & Feedback</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allSubmissions as $sub): ?>
                        <tr>
                            <td><?= htmlspecialchars($sub['submission_id']) ?></td>
                            <td><?= htmlspecialchars($sub['student_name']) ?></td>
                            <td><?= htmlspecialchars($sub['file_url']) ?></td>
                            <td><?= htmlspecialchars($sub['submission_date']) ?></td>
                            <td>
                                <?php if ($sub['score'] !== null): ?>
                                    <strong>Score:</strong> <?= htmlspecialchars($sub['score']) ?> / 100<br>
                                    <strong>Feedback:</strong> <?= htmlspecialchars($sub['feedback']) ?>
                                <?php else: ?>
                                    <form method="POST">
                                        <input type="hidden" name="action" value="grade_submission">
                                        <input type="hidden" name="submission_id" value="<?= htmlspecialchars($sub['submission_id']) ?>">
                                        <input type="number" name="score" placeholder="Score (0-100)" min="0" max="100" step="0.5" required>
                                        <input type="text" name="feedback" placeholder="Write feedback..." required>
                                        <button type="submit">Release Grade</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No submissions found in the database yet.</p>
        <?php endif; ?>
    </div>

</body>
</html>
