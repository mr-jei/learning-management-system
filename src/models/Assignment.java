import java.util.Date;

public class Assignment {
    private String assignmentId;
    private String title;
    private Date dueDate;

    public Assignment(String assignmentId, String title, Date dueDate) {
        this.assignmentId = assignmentId;
        this.title = title;
        this.dueDate = dueDate;
    }

    public String getAssignmentId() { return assignmentId; }
    public String getTitle() { return title; }
    public Date getDueDate() { return dueDate; }
}