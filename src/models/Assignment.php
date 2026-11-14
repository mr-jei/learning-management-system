<?php

class Assignment {
    private string $assignmentId;
    private string $title;
    private string $dueDate;

    public function __construct(string $assignmentId, string $title, string $dueDate) {
        $this->assignmentId = $assignmentId;
        $this->title = $title;
        $this->dueDate = $dueDate;
    }

    public function getAssignmentId(): string { return $this->assignmentId; }
    public function getTitle(): string { return $this->title; }
    public function getDueDate(): string { return $this->dueDate; }
}
