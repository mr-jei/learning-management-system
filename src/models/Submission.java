import java.util.Date;

public class Submission {
    private String submissionId;
    private Date submissionDate;
    private String fileUrl;

    public Submission(String submissionId, Date submissionDate, String fileUrl) {
        this.submissionId = submissionId;
        this.submissionDate = submissionDate;
        this.fileUrl = fileUrl;
    }

    public String getSubmissionId() { return submissionId; }
    public Date getSubmissionDate() { return submissionDate; }
    public String getFileUrl() { return fileUrl; }
}