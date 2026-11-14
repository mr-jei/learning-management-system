<?php

class Grade {
    private string $gradeId;
    private float $score;
    private string $feedback;

    public function __construct(string $gradeId, float $score, string $feedback) {
        $this->gradeId = $gradeId;
        $this->score = $score;
        $this->feedback = $feedback;
    }

    public function getGradeId(): string { return $this->gradeId; }
    public function getScore(): float { return $this->score; }
    public function getFeedback(): string { return $this->feedback; }
}
