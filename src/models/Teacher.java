public class Teacher extends User {
    private String teacherId;

    public Teacher(String userId, String name, String email, String teacherId) {
        super(userId, name, email);
        this.teacherId = teacherId;
    }

    public void uploadMaterials(String courseId, String content) {
        System.out.println("Uploaded learning material to course " + courseId + ": " + content);
    }

    public Grade gradeSubmission(String submissionId, float score, String feedback) {
        System.out.println("Graded submission " + submissionId + " with score: " + score);
        return new Grade("GRD-" + System.currentTimeMillis(), score, feedback);
    }

    public String getTeacherId() { return teacherId; }
}