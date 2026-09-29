-- inUsers.sql
-- A sql queries file using acts to insert data into Users
-- then throught start a transaction to transfer the UID of in Users to Teacher
-- if the user is teacher, transact to Teachers
-- else, transact to Students
-- This queries statement file is using acts to insert users data, teachers data, students data

START TRANSACTION;

INSERT INTO Users (username, email, password_hash, role) 
VALUES
  ('CHAN Tai Man Bob', 'bobctm@iit.edu.hk', 'ctmb2627@iit.edu.hk', 'teacher'),
  ('CHAN Siu Ming Alice', 'alicesim@stu.iit.edu.hk', 'stu-000001@iit.edu.hk', 'student');

INSERT INTO Teachers (user_id, hire_date, department, title) 
VALUES (
    (SELECT id FROM Users WHERE email = 'bobctm@iit.edu.hk'), '2020-09-01', 'Computer Science', 'Associate Professor');

INSERT INTO Students (user_id, student_number, major, grade_level) 
VALUES (
    (SELECT id FROM Users WHERE email = 'alicesim@stu.iit.edu.hk'), 'ST267000001', 'Computer Science', 13);

INSERT INTO Teacher_Student (teacher_id, student_id) 
VALUES (
    (SELECT t.id FROM Teachers t JOIN Users u ON t.user_id = u.id WHERE u.email = 'bobctm@iit.edu.hk'),
    (SELECT s.id FROM Students s JOIN Users u ON s.user_id = u.id WHERE u.email = 'alicesim@stu.iit.edu.hk')
);

COMMIT;
