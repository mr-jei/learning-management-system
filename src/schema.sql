-- LMS database schema + seed data
-- Import in phpMyAdmin (Import tab) or run:  mysql -u root -p < schema.sql

CREATE DATABASE IF NOT EXISTS lms_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lms_db;

CREATE TABLE IF NOT EXISTS users (
    user_id VARCHAR(20)  NOT NULL PRIMARY KEY,
    name    VARCHAR(100) NOT NULL,
    email   VARCHAR(150) NOT NULL UNIQUE,
    role    ENUM('admin', 'teacher', 'student') NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS courses (
    course_id   VARCHAR(20)  NOT NULL PRIMARY KEY,
    course_code VARCHAR(20)  NOT NULL UNIQUE,
    title       VARCHAR(150) NOT NULL,
    teacher_id  VARCHAR(20)  NULL,
    FOREIGN KEY (teacher_id) REFERENCES users(user_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS assignments (
    assignment_id VARCHAR(20)  NOT NULL PRIMARY KEY,
    course_id     VARCHAR(20)  NOT NULL,
    title         VARCHAR(150) NOT NULL,
    due_date      DATE         NOT NULL,
    FOREIGN KEY (course_id) REFERENCES courses(course_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS enrollments (
    student_id VARCHAR(20) NOT NULL,
    course_id  VARCHAR(20) NOT NULL,
    PRIMARY KEY (student_id, course_id),
    FOREIGN KEY (student_id) REFERENCES users(user_id),
    FOREIGN KEY (course_id)  REFERENCES courses(course_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS submissions (
    submission_id   VARCHAR(40)  NOT NULL PRIMARY KEY,
    assignment_id   VARCHAR(20)  NOT NULL,
    student_id      VARCHAR(20)  NOT NULL,
    submission_date DATETIME     NOT NULL,
    file_url        VARCHAR(500) NOT NULL,
    FOREIGN KEY (assignment_id) REFERENCES assignments(assignment_id),
    FOREIGN KEY (student_id)    REFERENCES users(user_id)
) ENGINE=InnoDB;

-- submission_id is UNIQUE so "ON DUPLICATE KEY UPDATE" lets a teacher re-grade
CREATE TABLE IF NOT EXISTS grades (
    grade_id      VARCHAR(40)  NOT NULL PRIMARY KEY,
    submission_id VARCHAR(40)  NOT NULL UNIQUE,
    score         DECIMAL(5,2) NOT NULL,
    feedback      TEXT         NOT NULL,
    FOREIGN KEY (submission_id) REFERENCES submissions(submission_id)
) ENGINE=InnoDB;

-- Seed data (the IDs the app looks up)
INSERT IGNORE INTO users (user_id, name, email, role) VALUES
    ('U-100', 'System Admin',      'admin@lms.test',   'admin'),
    ('U-101', 'Prof. Dana Reyes',  'dreyes@lms.test',  'teacher'),
    ('U-202', 'Alex Santos',       'asantos@lms.test', 'student');

INSERT IGNORE INTO courses (course_id, course_code, title, teacher_id) VALUES
    ('C-301', 'CS301', 'Software Engineering Capstone', 'U-101');

INSERT IGNORE INTO assignments (assignment_id, course_id, title, due_date) VALUES
    ('A-401', 'C-301', 'Capstone Proposal', '2026-12-01');
