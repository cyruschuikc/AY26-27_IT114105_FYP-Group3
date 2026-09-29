-- 1. Parent：Users (store all user account info)
CREATE TABLE Users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('teacher', 'student', 'admin') NOT NULL, -- distinguish user identity
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Child：Teachers (store teacher exclusive info)
CREATE TABLE Teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE, -- Foreign Key, UNIQUE (!IMPROTANT), 1...1
    hire_date DATE,
    department VARCHAR(50),
    title VARCHAR(50), 
    FOREIGN KEY (user_id) REFERENCES Users(id) ON DELETE CASCADE
);

-- 3. Child：Students (store students exclusive info)
CREATE TABLE Students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE, -- Foreign Key，
    student_number VARCHAR(20) NOT NULL UNIQUE,
    major VARCHAR(50),
    grade_level INT, -- eg：1 ~ 6 -> P1 ~ P6 | 7 ~ 9 -> S1 ~ S3 | 10 ~ 12 -> S4/DVE-Y1 ~ S6/DEV-Y3 | 13/+ -> High-Lv. / U 
    FOREIGN KEY (user_id) REFERENCES Users(id) ON DELETE CASCADE
);
