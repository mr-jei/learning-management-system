public class Grade {
    private String gradeId;
    private float score;
    private String feedback;

    public Grade(String gradeId, float score, String feedback) {
        this.gradeId = gradeId;
        this.score = score;
        this.feedback = feedback;
    }

    public String getGradeId() { return gradeId; }
    public float getScore() { return score; }
    public String getFeedback() { return feedback; }
}