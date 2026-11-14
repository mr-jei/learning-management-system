<?php

class Submission {
    private string $submissionId;
    private string $submissionDate;
    private string $fileUrl;

    public function __construct(string $submissionId, string $submissionDate, string $fileUrl) {
        $this->submissionId = $submissionId;
        $this->submissionDate = $submissionDate;
        $this->fileUrl = $fileUrl;
    }

    public function getSubmissionId(): string { return $this->submissionId; }
    public function getSubmissionDate(): string { return $this->submissionDate; }
    public function getFileUrl(): string { return $this->fileUrl; }
}
