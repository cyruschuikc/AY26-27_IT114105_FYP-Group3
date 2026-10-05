-- TeamBox_db.sql
-- 一個用於創建專案資料庫、資料表以及初始化資料的 SQL 腳本
-- 父表：Users (儲存所有用戶的帳號基本資訊)
-- 子表：Teachers (儲存教師專屬資訊) [1-Teacher ... 1-User] [1-Teacher _教導_ 1..*-Student]
--       Students (儲存學生專屬資訊) [1-Student ... 1-User] [1-Student _受教於_ 1..*-Teacher]

-- 1. 創建並使用資料庫
CREATE DATABASE IF NOT EXISTS TeamBox;
USE TeamBox;

-- 2. 創建 Users 表
CREATE TABLE Users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL, -- 註：移除 UNIQUE 约束，因為同名（如 Esther Yuen）在不同角色時會重複
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('teacher', 'student', 'admin') NOT NULL, -- 區分用戶身份
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 插入 Users 資料 (自增 ID 將會是 1 至 10)
INSERT INTO Users (username, email, password_hash, role) VALUES
    ('Esther Yuen', 'estherey@sp.fyp03.iit.vtc.hk', 'FYP03-ey*2627', 'admin'),         -- ID: 1
    ('Thomas Chan', 'thomaschy2627@dev.fyp03.iit.vtc.hk', 'FYP03-chy014543$', 'admin'), -- ID: 2
    ('Cyrus Chui', 'cyrusckc2627@dev.fyp03.iit.vtc.hk', 'FYP03-ckc193995$', 'admin'),   -- ID: 3
    ('Miao Tung', 'mt2627@dev.fyp03.iit.vtc.hk', 'FYP03-mt025281$', 'admin'),          -- ID: 4
    ('Savio Lai', 'saviolky2627@dev.fyp03.iit.vtc.hk', 'FYP03-lky350618$', 'admin'),    -- ID: 5
    ('Esther Yuen', 'wyyuen@vtc.edu.hk', 'FYP03-ewy!', 'teacher'),                     -- ID: 6
    ('Thomas Chan', '250014543@stu.vtc.edu.hk', 'FYP03-chy014543/s', 'student'),       -- ID: 7
    ('Cyrus Chui', '250193995@stu.vtc.edu.hk', 'FYP03-ckc193995/s', 'student'),         -- ID: 8
    ('Miao Tung', '250025281@stu.vtc.edu.hk', 'FYP03-mt025281/s', 'student'),         -- ID: 9
    ('Savio Lai', '250350618@stu.vtc.edu.hk', 'FYP03-lky350618/s', 'student');         -- ID: 10

-- 3. 創建 Teachers 表
CREATE TABLE Teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE, -- 外鍵，UNIQUE (重要)，1...1 對應 Users
    hire_date DATE,
    department VARCHAR(50),
    title VARCHAR(50), 
    FOREIGN KEY (user_id) REFERENCES Users(id) ON DELETE CASCADE
);

-- 插入 Teachers 資料 (此教師的自增 id 將會是 1)
INSERT INTO Teachers (user_id, hire_date, department, title) VALUES
    (6, '2022-09-01', 'Information Technology', 'Senior Lecturer'); -- 對應 Users ID: 6

-- 4. 創建 Students 表
CREATE TABLE Students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE, -- 外鍵，UNIQUE (重要)，1...1 對應 Users
    student_number VARCHAR(20) NOT NULL UNIQUE,
    major VARCHAR(50),
    grade_level INT, -- 例如：14 -> High-Lv. / U Year-2
    FOREIGN KEY (user_id) REFERENCES Users(id) ON DELETE CASCADE
);

-- 插入 Students 資料 (自增 id 將會是 1, 2, 3, 4)
INSERT INTO Students (user_id, student_number, major, grade_level) VALUES
    (7, '20260001', 'Software Engineering', 14),   -- 對應 Users ID: 7  -> Students ID: 1
    (8, '20260002', 'Information Technology', 14), -- 對應 Users ID: 8  -> Students ID: 2
    (9, '20260003', 'Software Engineering', 14),   -- 對應 Users ID: 9  -> Students ID: 3
    (10, '20260004', 'Cloud Computing', 14);       -- 對應 Users ID: 10 -> Students ID: 4

-- 5. 創建 Teacher_Student 關聯表 (多對多)
CREATE TABLE Teacher_Student (
    teacher_id INT NOT NULL,
    student_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (teacher_id, student_id),
    FOREIGN KEY (teacher_id) REFERENCES Teachers(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES Students(id) ON DELETE CASCADE
);

-- 插入 Teacher_Student 關聯資料
-- 註：此處必須對應 Teachers 表和 Students 表的「主鍵 id」，而非 Users 表的 id。
-- 教師 Esther Yuen 在 Teachers 表的 id 為 1
-- 四位學生在 Students 表的 id 分別為 1, 2, 3, 4
INSERT INTO Teacher_Student (teacher_id, student_id) VALUES
    (1, 1), -- 教師(1) 教導 學生 Thomas(1)
    (1, 2), -- 教師(1) 教導 學生 Cyrus(2)
    (1, 3), -- 教師(1) 教導 學生 Miao(3)
    (1, 4); -- 教師(1) 教導 學生 Savio(4)
