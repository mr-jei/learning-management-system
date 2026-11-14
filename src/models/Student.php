<?php
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/Submission.php';

class Student extends User {
    private string $studentId;
    private PDO $db;

    public function __construct(string $userId, string $name, string $email, string $studentId, PDO $db) {
        parent::__construct($userId, $name, $email);
        $this->studentId = $studentId;
        $this->db = $db;
    }

    public function joinClass(string $courseCode): string {
        $stmt = $this->db->prepare("SELECT course_id, title FROM courses WHERE course_code = ?");
        $stmt->execute([$courseCode]);
        $course = $stmt->fetch();

        if (!$course) {
            return "Error: Course code '{$courseCode}' not found.";
        }

        $enroll = $this->db->prepare("INSERT IGNORE INTO enrollments (student_id, course_id) VALUES (?, ?)");
        $enroll->execute([$this->userId, $course['course_id']]);

        return "Student {$this->name} enrolled in {$course['title']} ({$courseCode}).";
    }

    public function isEnrolledInAssignmentCourse(string $assignmentId): bool {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM assignments a
             JOIN enrollments e ON e.course_id = a.course_id
             WHERE a.assignment_id = ? AND e.student_id = ?"
        );
        $stmt->execute([$assignmentId, $this->userId]);
        return (bool) $stmt->fetch();
    }

    /**
     * @throws RuntimeException if the student is not enrolled in the assignment's course
     */
    public function submitAssignment(string $assignmentId, string $fileUrl): Submission {
        if (!$this->isEnrolledInAssignmentCourse($assignmentId)) {
            throw new RuntimeException("You must join the class before submitting an assignment.");
        }

        // Unique even when two submissions land in the same second
        $subId = "SUB-" . strtoupper(bin2hex(random_bytes(5)));
        $date = date("Y-m-d H:i:s");

        $stmt = $this->db->prepare(
            "INSERT INTO submissions (submission_id, assignment_id, student_id, submission_date, file_url)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$subId, $assignmentId, $this->userId, $date, $fileUrl]);

        return new Submission($subId, $date, $fileUrl);
    }

    public function getStudentId(): string { return $this->studentId; }
}
