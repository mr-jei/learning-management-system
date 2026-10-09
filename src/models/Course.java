import java.util.ArrayList;
import java.util.List;

public class Course {
    private String courseId;
    private String title;
    private String courseCode;
    private List<Assignment> assignments;

    public Course(String courseId, String title, String courseCode) {
        this.courseId = courseId;
        this.title = title;
        this.courseCode = courseCode;
        this.assignments = new ArrayList<>();
    }

    public void addAssignment(Assignment assignment) {
        assignments.add(assignment);
        System.out.println("Assignment '" + assignment.getTitle() + "' added to course: " + title);
    }

    // Getters
    public String getCourseId() { return courseId; }
    public String getTitle() { return title; }
    public String getCourseCode() { return courseCode; }
    public List<Assignment> getAssignments() { return assignments; }
}