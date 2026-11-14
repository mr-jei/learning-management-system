<?php
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/Grade.php';

class Teacher extends User {
    private string $teacherId;
    private PDO $db;

    public function __construct(string $userId, string $name, string $email, string $teacherId, PDO $db) {
        parent::__construct($userId, $name, $email);
        $this->teacherId = $teacherId;
        $this->db = $db;
    }

    public function uploadMaterials(string $courseId, string $content): string {
        return "Uploaded learning material to course {$courseId}: {$content}";
    }

    /**
     * @throws InvalidArgumentException on a bad score or unknown submission
     */
    public function gradeSubmission(string $submissionId, float $score, string $feedback): Grade {
        if ($score < 0 || $score > 100) {
            throw new InvalidArgumentException("Score must be between 0 and 100.");
        }

        $check = $this->db->prepare("SELECT 1 FROM submissions WHERE submission_id = ?");
        $check->execute([$submissionId]);
        if (!$check->fetch()) {
            throw new InvalidArgumentException("Submission '{$submissionId}' not found.");
        }

        $gradeId = "GRD-" . strtoupper(bin2hex(random_bytes(5)));

        // submission_id is UNIQUE in `grades`, so re-grading updates the existing row
        $stmt = $this->db->prepare(
            "INSERT INTO grades (grade_id, submission_id, score, feedback)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE score = VALUES(score), feedback = VALUES(feedback)"
        );
        $stmt->execute([$gradeId, $submissionId, $score, $feedback]);

        return new Grade($gradeId, $score, $feedback);
    }

    public function getTeacherId(): string { return $this->teacherId; }
}
