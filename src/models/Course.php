<?php
require_once __DIR__ . '/Assignment.php';

class Course {
    private string $courseId;
    private string $title;
    private string $courseCode;
    private array $assignments = [];
    private ?PDO $db;

    public function __construct(string $courseId, string $title, string $courseCode, ?PDO $db = null) {
        $this->courseId = $courseId;
        $this->title = $title;
        $this->courseCode = $courseCode;
        $this->db = $db;
    }

    public function addAssignment(Assignment $assignment): void {
        $this->assignments[] = $assignment;

        if ($this->db !== null) {
            $stmt = $this->db->prepare(
                "INSERT IGNORE INTO assignments (assignment_id, course_id, title, due_date)
                 VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([
                $assignment->getAssignmentId(),
                $this->courseId,
                $assignment->getTitle(),
                $assignment->getDueDate()
            ]);
        }
    }

    public function getCourseId(): string { return $this->courseId; }
    public function getTitle(): string { return $this->title; }
    public function getCourseCode(): string { return $this->courseCode; }
    public function getAssignments(): array { return $this->assignments; }
}
