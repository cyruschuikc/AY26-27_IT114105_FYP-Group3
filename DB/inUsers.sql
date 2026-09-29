START TRANSACTION;

-- =========================================================
-- 1. add 1 Teacher (User + Teacher Record)
-- =========================================================
INSERT INTO Users (username, email, password_hash, role) 
VALUES ('YUEN Wai Ying', 'estherywy@tea.vtc.edu.hk', 'Eywy8582@vtchk', 'teacher');

-- 取得剛剛產生的 User ID，並寫入 Teachers 表
SET @teacher_user_id = LAST_INSERT_ID();

INSERT INTO Teachers (user_id, hire_date, department, title) 
VALUES (@teacher_user_id, '2020-09-01', 'Computer Science', 'Associate Professor');

-- 取得剛產生的 Teacher ID
SET @teacher_id = LAST_INSERT_ID();


-- =========================================================
-- 2. 新增 1 位 Student (User + Student Record)
-- =========================================================
INSERT INTO Users (username, email, password_hash, role) 
VALUES ('alice_wong', 'alice.wong@student.edu', '$2b$10$e8.yK...hashedpasswordexample...', 'student');

-- 取得剛剛產生的 User ID，並寫入 Students 表
SET @student_user_id = LAST_INSERT_ID();

INSERT INTO Students (user_id, student_number, major, grade_level) 
VALUES (@student_user_id, 'ST1009823', 'Computer Science', 10); -- 10 代表 S4 / DVE-Y1

-- 取得剛產生的 Student ID
SET @student_id = LAST_INSERT_ID();


-- =========================================================
-- 3. 綁定 師生關係 (Teacher_Student)
-- =========================================================
INSERT INTO Teacher_Student (teacher_id, student_id) 
VALUES (@teacher_id, @student_id);

COMMIT;
