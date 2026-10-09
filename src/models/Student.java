import java.util.Date;

public class Student extends User {
    private String studentId;

    public Student(String userId, String name, String email, String studentId) {
        super(userId, name, email);
        this.studentId = studentId;
    }

    public void joinClass(String courseCode) {
        System.out.println("Student " + name + " joined course with code: " + courseCode);
    }

    public Submission submitAssignment(String assignmentId, String fileUrl) {
        System.out.println("Submitting file " + fileUrl + " for assignment: " + assignmentId);
        return new Submission("SUB-" + System.currentTimeMillis(), new Date(), fileUrl);
    }

    public String getStudentId() { return studentId; }
}