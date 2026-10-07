-- =========================================================
-- TeamBox Database
-- =========================================================

DROP DATABASE IF EXISTS teambox;
CREATE DATABASE teambox
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

USE teambox;

-- =========================================================
-- 1. Users
-- =========================================================
CREATE TABLE `Users` (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    username       VARCHAR(100) NOT NULL,
    email          VARCHAR(150) NOT NULL UNIQUE,
    password_hash  VARCHAR(255) NOT NULL,
    role           ENUM('teacher','student','admin') NOT NULL,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO `Users` (username, email, password_hash, role) VALUES
    -- 老师 (id 1~5)
    ('Esther Yuen',       'wyyuen@vtc.edu.hk',         '12345', 'teacher'),  -- 1
    ('Dr. Alan Ho',       'alanho@vtc.edu.hk',         '12345', 'teacher'),  -- 2
    ('Prof. Rebecca Lam', 'rebeccalam@vtc.edu.hk',     '12345', 'teacher'),  -- 3
    ('Dr. Vincent Ng',    'vincentng@vtc.edu.hk',      '12345', 'teacher'),  -- 4
    ('Ms. Karen Tsang',   'karentsang@vtc.edu.hk',     '12345', 'teacher'),  -- 5

    -- 学生 (id 6~30)
    ('CHAN Ho Yin',       '260222111@stu.vtc.edu.hk',  '12345', 'student'),  -- 6
    ('CHUI Ka Chun',      '260222112@stu.vtc.edu.hk',  '12345', 'student'),  -- 7
    ('LAI Ka-Yu Savio',   '260222113@stu.vtc.edu.hk',  '12345', 'student'),  -- 8
    ('MIAO Tung',         '260222114@stu.vtc.edu.hk',  '12345', 'student'),  -- 9
    ('WONG Mei Ling',     '260222115@stu.vtc.edu.hk',  '12345', 'student'),  -- 10
    ('LEE Chun Kit',      '260222116@stu.vtc.edu.hk',  '12345', 'student'),  -- 11
    ('NG Wing Yan',       '260222117@stu.vtc.edu.hk',  '12345', 'student'),  -- 12
    ('TAM Chi Hang',      '260222201@stu.vtc.edu.hk',  '12345', 'student'),  -- 13
    ('HO Wing Sze',       '260222202@stu.vtc.edu.hk',  '12345', 'student'),  -- 14
    ('LAM Ka Ho',         '260222203@stu.vtc.edu.hk',  '12345', 'student'),  -- 15
    ('YIP Man Yee',       '260222204@stu.vtc.edu.hk',  '12345', 'student'),  -- 16
    ('SO Chun Ming',      '260222205@stu.vtc.edu.hk',  '12345', 'student'),  -- 17
    ('CHEUNG Ka Yan',     '260222206@stu.vtc.edu.hk',  '12345', 'student'),  -- 18
    ('CHU Ho Yin',        '260222207@stu.vtc.edu.hk',  '12345', 'student'),  -- 19
    ('FUNG Wing Chi',     '260222208@stu.vtc.edu.hk',  '12345', 'student'),  -- 20
    ('KWOK Chi Wai',      '260222209@stu.vtc.edu.hk',  '12345', 'student'),  -- 21
    ('LEUNG Mei Mei',     '260222210@stu.vtc.edu.hk',  '12345', 'student'),  -- 22
    ('MAK Ho Cheung',     '260222211@stu.vtc.edu.hk',  '12345', 'student'),  -- 23
    ('NGAI Ka Wai',       '260222212@stu.vtc.edu.hk',  '12345', 'student'),  -- 24
    ('POON Yuen Ting',    '260222213@stu.vtc.edu.hk',  '12345', 'student'),  -- 25
    ('SIU Chun Hei',      '260222214@stu.vtc.edu.hk',  '12345', 'student'),  -- 26
    ('TSE Wing Lam',      '260222215@stu.vtc.edu.hk',  '12345', 'student'),  -- 27
    ('WU Ka Man',         '260222216@stu.vtc.edu.hk',  '12345', 'student'),  -- 28
    ('YIU Ho Kwan',       '260222217@stu.vtc.edu.hk',  '12345', 'student'),  -- 29
    ('ZHAO Wei',          '260222218@stu.vtc.edu.hk',  '12345', 'student');  -- 30

-- =========================================================
-- 2. Teachers  (teacher_code = T + 5 位数字)
-- =========================================================
CREATE TABLE `Teachers` (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL UNIQUE,
    teacher_code  VARCHAR(6) NOT NULL UNIQUE,
    hire_date     DATE,
    department    VARCHAR(100),
    title         VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES `Users`(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `Teachers` (user_id, teacher_code, hire_date, department, title) VALUES
    (1, 'T48291', '2022-09-01', 'Information Technology', 'Senior Lecturer'),     -- 1  Esther Yuen
    (2, 'T15942', '2020-09-01', 'Information Technology', 'Lecturer'),            -- 2  Dr. Alan Ho
    (3, 'T73658', '2017-09-01', 'Computer Science',       'Associate Professor'), -- 3  Prof. Rebecca Lam
    (4, 'T20473', '2021-09-01', 'Information Technology', 'Lecturer'),            -- 4  Dr. Vincent Ng
    (5, 'T91826', '2018-09-01', 'Software Engineering',   'Senior Lecturer');     -- 5  Ms. Karen Tsang


-- =========================================================
-- 3. Students  (student_number = S + 8 位数字)
-- =========================================================
CREATE TABLE `Students` (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL UNIQUE,
    student_number  VARCHAR(20) NOT NULL UNIQUE,
    major           VARCHAR(100),
    grade_level     INT,
    FOREIGN KEY (user_id) REFERENCES `Users`(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `Students` (user_id, student_number, major, grade_level) VALUES
    (6,  'S48291537', 'Software Engineering', 14),  -- 1
    (7,  'S15942736', 'Software Engineering', 14),  -- 2
    (8,  'S73658120', 'Software Engineering', 14),  -- 3
    (9,  'S20473819', 'Software Engineering', 14),  -- 4
    (10, 'S91826504', 'Software Engineering', 14),  -- 5
    (11, 'S35718492', 'Software Engineering', 14),  -- 6
    (12, 'S62048571', 'Software Engineering', 14),  -- 7
    (13, 'S17395028', 'Software Engineering', 14),  -- 8
    (14, 'S84926315', 'Software Engineering', 14),  -- 9
    (15, 'S50274186', 'Software Engineering', 14),  -- 10
    (16, 'S31486972', 'Information Technology', 14),-- 11
    (17, 'S76592043', 'Software Engineering', 14),  -- 12
    (18, 'S09851426', 'Cloud Computing', 14),       -- 13
    (19, 'S47203581', 'Software Engineering', 14),  -- 14
    (20, 'S61573924', 'Information Technology', 14),-- 15
    (21, 'S28639174', 'Software Engineering', 14),  -- 16
    (22, 'S93051768', 'Cloud Computing', 14),       -- 17
    (23, 'S34718059', 'Software Engineering', 14),  -- 18
    (24, 'S80245391', 'Information Technology', 14),-- 19
    (25, 'S15972806', 'Software Engineering', 14),  -- 20
    (26, 'S62048913', 'Software Engineering', 14),  -- 21
    (27, 'S47123895', 'Cloud Computing', 14),       -- 22
    (28, 'S90471625', 'Software Engineering', 14),  -- 23
    (29, 'S23850147', 'Information Technology', 14),-- 24
    (30, 'S76093184', 'Software Engineering', 14);  -- 25


-- =========================================================
-- 4. Teacher_Student
-- =========================================================
CREATE TABLE `Teacher_Student` (
    teacher_id  INT NOT NULL,
    student_id  INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (teacher_id, student_id),
    FOREIGN KEY (teacher_id) REFERENCES `Teachers`(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES `Students`(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `Teacher_Student` (teacher_id, student_id) VALUES
    (1, 1), (1, 2), (1, 3), (1, 4), (1, 5), (1, 6), (1, 7), (1, 8), (1, 9), (1, 10), (1, 11), (1, 12),
    (2, 13), (2, 14), (2, 15), (2, 16), (2, 17),
    (3, 18), (3, 19), (3, 20), (3, 21),
    (4, 22), (4, 23), (4, 24),
    (5, 25);


-- =========================================================
-- 5. Projects
-- =========================================================
CREATE TABLE `Projects` (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    name           VARCHAR(150) NOT NULL,
    category       VARCHAR(100),
    supervisor_id  INT NOT NULL,
    period_start   DATE,
    period_end     DATE,
    status         ENUM('Recruiting','Voting','Executing','Completed','Archived') DEFAULT 'Recruiting',
    progress       INT DEFAULT 0,
    description    TEXT,
    FOREIGN KEY (supervisor_id) REFERENCES `Teachers`(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

INSERT INTO `Projects`
    (name, category, supervisor_id, period_start, period_end, status, progress, description) VALUES
    ('TeamBox',            'Software Engineering', 1, '2026-09-01', '2027-04-30', 'Executing',  60, 'Cross-platform collaboration and management system for student teams.'),
    ('Smart Attendance',   'Mobile App',           1, '2026-09-01', '2027-04-30', 'Executing',  35, 'Face recognition based attendance system.'),
    ('Campus Map AR',      'AR / VR',              1, '2026-09-01', '2027-04-30', 'Recruiting', 10, 'AR navigation for campus buildings.'),
    ('Smart Library',      'Web App',              2, '2026-09-01', '2027-04-30', 'Executing',  40, 'Library seat booking and recommendation system.'),
    ('Campus Food Order',  'Mobile App',           2, '2026-09-01', '2027-04-30', 'Executing',  25, 'Mobile app to order food from campus canteens.'),
    ('AI Study Buddy',     'Machine Learning',     3, '2026-09-01', '2027-04-30', 'Executing',  55, 'AI assistant for personalised study plans.'),
    ('Green Campus',       'IoT',                  3, '2026-09-01', '2027-04-30', 'Recruiting', 10, 'IoT energy monitoring for campus buildings.'),
    ('Career Path Finder', 'Web App',              4, '2026-09-01', '2027-04-30', 'Executing',  30, 'Web platform to match students with internships.'),
    ('Virtual Lab',        'VR',                   4, '2026-09-01', '2027-04-30', 'Recruiting',  5, 'VR-based virtual laboratory for science students.'),
    ('Campus Events',      'Mobile App',           5, '2026-09-01', '2027-04-30', 'Executing',  45, 'Mobile app to discover and manage campus events.'),
    ('Study Group Match',  'Web App',              5, '2026-09-01', '2027-04-30', 'Voting',     15, 'Platform to match students into study groups.');


-- =========================================================
-- 6. Milestones
-- =========================================================
CREATE TABLE `Milestones` (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    project_id  INT NOT NULL,
    name        VARCHAR(150) NOT NULL,
    start_date  DATE NOT NULL,
    end_date    DATE NOT NULL,
    status      ENUM('Pending','In Progress','Completed') DEFAULT 'Pending',
    progress    INT DEFAULT 0,
    FOREIGN KEY (project_id) REFERENCES `Projects`(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `Milestones` (project_id, name, start_date, end_date, status, progress) VALUES
    (1, 'Requirement Analysis',    '2026-09-01', '2026-10-05', 'Completed',   100),
    (1, 'System Design',           '2026-10-06', '2026-10-20', 'Completed',   100),
    (1, 'Core Development',        '2026-10-21', '2027-01-15', 'In Progress',  55),
    (1, 'Testing & Documentation', '2027-01-16', '2027-03-20', 'Pending',       0),
    (1, 'Final Presentation',      '2027-03-21', '2027-04-30', 'Pending',       0),
    (2, 'Requirement Analysis',    '2026-09-01', '2026-10-10', 'Completed',   100),
    (2, 'System Design',           '2026-10-11', '2026-10-31', 'In Progress',  60),
    (2, 'Development',             '2026-11-01', '2027-02-15', 'Pending',       0),
    (2, 'Testing',                 '2027-02-16', '2027-03-31', 'Pending',       0),
    (2, 'Presentation',            '2027-04-01', '2027-04-30', 'Pending',       0),
    (3, 'Requirement Analysis',    '2026-09-01', '2026-10-20', 'Pending',       0),
    (3, 'AR Prototype',            '2026-10-21', '2027-02-28', 'Pending',       0),
    (3, 'User Testing',            '2027-03-01', '2027-04-10', 'Pending',       0),
    (4, 'Requirement Analysis',    '2026-09-01', '2026-10-10', 'Completed',   100),
    (4, 'System Design',           '2026-10-11', '2026-10-31', 'In Progress',  60),
    (4, 'Development',             '2026-11-01', '2027-02-15', 'Pending',       0),
    (4, 'Testing',                 '2027-02-16', '2027-03-31', 'Pending',       0),
    (4, 'Presentation',            '2027-04-01', '2027-04-30', 'Pending',       0),
    (5, 'Requirement Analysis',    '2026-09-01', '2026-10-05', 'Completed',   100),
    (5, 'UI Design',               '2026-10-06', '2026-10-25', 'In Progress',  40),
    (5, 'Development',             '2026-10-26', '2027-02-28', 'Pending',       0),
    (5, 'Testing',                 '2027-03-01', '2027-04-10', 'Pending',       0),
    (6, 'Research',                '2026-09-01', '2026-09-30', 'Completed',   100),
    (6, 'Model Training',          '2026-10-01', '2027-01-10', 'In Progress',  55),
    (6, 'Evaluation',              '2027-01-11', '2027-03-15', 'Pending',       0),
    (6, 'Thesis',                  '2027-03-16', '2027-04-30', 'Pending',       0),
    (7, 'Requirement Analysis',    '2026-09-01', '2026-10-15', 'Pending',       0),
    (7, 'Hardware Setup',          '2026-10-16', '2027-01-31', 'Pending',       0),
    (7, 'Data Collection',         '2027-02-01', '2027-03-15', 'Pending',       0),
    (7, 'Final Presentation',      '2027-03-16', '2027-04-30', 'Pending',       0),
    (8, 'Requirement Analysis',    '2026-09-01', '2026-10-10', 'Completed',   100),
    (8, 'Database Design',         '2026-10-11', '2026-11-10', 'In Progress',  50),
    (8, 'Frontend Dev',            '2026-11-11', '2027-02-10', 'Pending',       0),
    (8, 'Backend Dev',             '2026-11-11', '2027-02-10', 'Pending',       0),
    (8, 'Final Presentation',      '2027-03-01', '2027-04-30', 'Pending',       0),
    (9, 'Research',                '2026-09-01', '2026-10-20', 'Pending',       0),
    (9, 'VR Prototype',            '2026-10-21', '2027-02-28', 'Pending',       0),
    (9, 'User Testing',            '2027-03-01', '2027-04-10', 'Pending',       0),
    (10, 'Requirement Analysis',   '2026-09-01', '2026-10-05', 'Completed',   100),
    (10, 'UI Design',              '2026-10-06', '2026-11-15', 'In Progress',  65),
    (10, 'Development',            '2026-11-16', '2027-02-28', 'Pending',       0),
    (10, 'Testing',                '2027-03-01', '2027-04-10', 'Pending',       0),
    (11, 'Requirement Analysis',   '2026-09-01', '2026-10-20', 'In Progress',  30),
    (11, 'System Design',          '2026-10-21', '2026-11-30', 'Pending',       0),
    (11, 'Development',            '2026-12-01', '2027-03-15', 'Pending',       0),
    (11, 'Testing',                '2027-03-16', '2027-04-20', 'Pending',       0);


-- =========================================================
-- 7. Groups
-- =========================================================
CREATE TABLE `Groups` (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    project_id   INT NOT NULL,
    name         VARCHAR(100) NOT NULL,
    leader_id    INT NULL,
    max_members  INT DEFAULT 4,
    status       ENUM('Recruiting','Voting','Executing','Completed') DEFAULT 'Recruiting',
    formed_at    DATETIME NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES `Projects`(id) ON DELETE CASCADE,
    FOREIGN KEY (leader_id)  REFERENCES `Students`(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO `Groups` (project_id, name, leader_id, max_members, status, formed_at) VALUES
    (1,  'Team 01', 1,  4, 'Executing', '2026-09-15 10:00:00'),
    (1,  'Team 02', 3,  4, 'Voting',    NULL),
    (2,  'Team 03', 5,  4, 'Recruiting',NULL),
    (4,  'Team 04', 8,  4, 'Executing', '2026-09-14 10:00:00'),
    (5,  'Team 05', 9,  4, 'Executing', '2026-09-15 11:30:00'),
    (6,  'Team 06', 13, 4, 'Executing', '2026-09-16 14:20:00'),
    (6,  'Team 07', 17, 4, 'Voting',    NULL),
    (7,  'Team 08', 18, 4, 'Recruiting',NULL),
    (8,  'Team 09', 20, 4, 'Executing', '2026-09-17 09:00:00'),
    (9,  'Team 10', 22, 4, 'Recruiting',NULL);


-- =========================================================
-- 8. Group_Members
-- =========================================================
CREATE TABLE `Group_Members` (
    group_id    INT NOT NULL,
    student_id  INT NOT NULL,
    role        ENUM('leader','member') DEFAULT 'member',
    joined_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (group_id, student_id),
    FOREIGN KEY (group_id)   REFERENCES `Groups`(id)   ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES `Students`(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `Group_Members` (group_id, student_id, role) VALUES
    (1, 1, 'leader'), (1, 2, 'member'), (1, 3, 'member'), (1, 4, 'member'),
    (2, 3, 'leader'), (2, 6, 'member'),
    (3, 5, 'leader'), (3, 6, 'member'), (3, 7, 'member'),
    (4, 8, 'leader'), (4, 9, 'member'), (4, 10, 'member'), (4, 11, 'member'),
    (5, 9, 'leader'), (5, 12, 'member'), (5, 13, 'member'), (5, 14, 'member'),
    (6, 13, 'leader'), (6, 15, 'member'), (6, 16, 'member'), (6, 17, 'member'),
    (7, 17, 'leader'), (7, 18, 'member'),
    (8, 18, 'leader'), (8, 19, 'member'),
    (9, 20, 'leader'), (9, 21, 'member'), (9, 22, 'member'), (9, 23, 'member'),
    (10, 22, 'leader'), (10, 23, 'member');


-- =========================================================
-- 9. Group_Requests
-- =========================================================
CREATE TABLE `Group_Requests` (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    group_name     VARCHAR(100) NOT NULL,
    project_id     INT NOT NULL,
    submitted_by   INT NOT NULL,
    status         ENUM('Pending','Accepted','Rejected') DEFAULT 'Pending',
    review_note    TEXT,
    submitted_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at    DATETIME NULL,
    reviewed_by    INT NULL,
    FOREIGN KEY (project_id)   REFERENCES `Projects`(id) ON DELETE CASCADE,
    FOREIGN KEY (submitted_by) REFERENCES `Students`(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by)  REFERENCES `Teachers`(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO `Group_Requests` (group_name, project_id, submitted_by, status, submitted_at) VALUES
    ('Team A', 1,  1,  'Pending', '2026-10-04 08:42:00'),
    ('Team B', 2,  6,  'Pending', '2026-10-03 14:20:00'),
    ('Team C', 4,  8,  'Pending', '2026-10-05 09:10:00'),
    ('Team D', 4,  11, 'Pending', '2026-10-05 10:25:00'),
    ('Team E', 5,  12, 'Pending', '2026-10-05 11:40:00'),
    ('Team F', 6,  13, 'Pending', '2026-10-04 14:05:00'),
    ('Team G', 7,  16, 'Pending', '2026-10-03 16:30:00'),
    ('Team H', 8,  19, 'Pending', '2026-10-03 17:45:00'),
    ('Team I', 9,  21, 'Pending', '2026-10-02 11:00:00'),
    ('Team J', 10, 24, 'Pending', '2026-10-02 13:15:00');


-- =========================================================
-- 10. Request_Members
-- =========================================================
CREATE TABLE `Request_Members` (
    request_id  INT NOT NULL,
    student_id  INT NOT NULL,
    is_leader   TINYINT(1) DEFAULT 0,
    PRIMARY KEY (request_id, student_id),
    FOREIGN KEY (request_id) REFERENCES `Group_Requests`(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES `Students`(id)       ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `Request_Members` (request_id, student_id, is_leader) VALUES
    (1, 1, 1), (1, 2, 0), (1, 3, 0), (1, 4, 0),
    (2, 6, 1), (2, 7, 0), (2, 8, 0),
    (3, 8, 1), (3, 9, 0), (3, 10, 0),
    (4, 11, 1), (4, 12, 0), (4, 13, 0),
    (5, 12, 1), (5, 14, 0), (5, 15, 0),
    (6, 13, 1), (6, 14, 0), (6, 15, 0),
    (7, 16, 1), (7, 17, 0), (7, 18, 0),
    (8, 19, 1), (8, 20, 0), (8, 21, 0),
    (9, 21, 1), (9, 22, 0), (9, 23, 0),
    (10, 24, 1), (10, 25, 0);


-- =========================================================
-- 11. Leader_Votes
-- =========================================================
CREATE TABLE `Leader_Votes` (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    group_id      INT NOT NULL,
    voter_id      INT NOT NULL,
    candidate_id  INT NOT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_vote (group_id, voter_id),
    FOREIGN KEY (group_id)     REFERENCES `Groups`(id)   ON DELETE CASCADE,
    FOREIGN KEY (voter_id)     REFERENCES `Students`(id) ON DELETE CASCADE,
    FOREIGN KEY (candidate_id) REFERENCES `Students`(id) ON DELETE CASCADE
) ENGINE=InnoDB;


-- =========================================================
-- 12. Tasks
-- =========================================================
CREATE TABLE `Tasks` (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    project_id    INT NOT NULL,
    group_id      INT NOT NULL,
    title         VARCHAR(200) NOT NULL,
    description   TEXT,
    assigned_to   INT NULL,
    created_by    INT NOT NULL,
    status        ENUM('Pending','In Progress','Completed') DEFAULT 'Pending',
    priority      ENUM('Low','Medium','High') DEFAULT 'Medium',
    due_date      DATE,
    completed_at  DATETIME NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id)  REFERENCES `Projects`(id) ON DELETE CASCADE,
    FOREIGN KEY (group_id)    REFERENCES `Groups`(id)   ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES `Students`(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by)  REFERENCES `Users`(id)    ON DELETE RESTRICT
) ENGINE=InnoDB;

INSERT INTO `Tasks` (project_id, group_id, title, description, assigned_to, created_by, status, priority, due_date) VALUES
    (1, 1, 'Complete Project Proposal',  'Draft and submit the FYP proposal.',          1,  1, 'Completed',   'High',   '2026-09-30'),
    (1, 1, 'Requirement Analysis',       'Analyse requirements and produce document.',  3,  1, 'Completed',   'Medium', '2026-10-05'),
    (1, 1, 'Create System Interface',    'Design the main UI screens.',                 4,  1, 'In Progress', 'High',   '2026-10-10'),
    (1, 1, 'Database Schema Design',     'Design the MySQL schema for TeamBox.',        3,  1, 'Pending',     'Medium', '2026-10-15'),
    (1, 2, 'Android Prototype',          'Build a first Android prototype.',            2,  1, 'Pending',     'High',   '2026-10-25'),
    (4, 4, 'Write SRS Document',         'Software requirement spec.',                  8,  2, 'Completed',   'High',   '2026-10-05'),
    (4, 4, 'Design Seat Map UI',         'Interactive seat map design.',                9,  2, 'In Progress', 'Medium', '2026-10-15'),
    (4, 4, 'Booking API',                'Implement booking API.',                     10,  2, 'Pending',     'High',   '2026-10-25'),
    (5, 5, 'Menu Database',              'Create menu database schema.',                9,  2, 'Completed',   'Medium', '2026-10-01'),
    (5, 5, 'Payment Integration',        'Integrate payment gateway.',                 12,  2, 'In Progress', 'High',   '2026-10-20'),
    (5, 5, 'Order Tracking UI',          'Design order tracking screen.',              13,  2, 'Pending',     'Low',    '2026-10-30'),
    (6, 6, 'Data Collection',            'Collect study material dataset.',            13,  3, 'Completed',   'High',   '2026-09-30'),
    (6, 6, 'Train Baseline Model',       'Train first ML baseline.',                   15,  3, 'In Progress', 'High',   '2026-10-25'),
    (6, 6, 'Prompt Engineering',         'Design prompt templates.',                   16,  3, 'Pending',     'Medium', '2026-11-10'),
    (8, 9, 'Company Database',           'Collect company data.',                      20,  4, 'Completed',   'Medium', '2026-10-05'),
    (8, 9, 'Matching Algorithm',         'Build matching algorithm.',                  21,  4, 'In Progress', 'High',   '2026-10-28'),
    (8, 9, 'Frontend Dashboard',         'Build student dashboard.',                   22,  4, 'Pending',     'Medium', '2026-11-15');


-- =========================================================
-- 13. Files
-- =========================================================
CREATE TABLE `Files` (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    project_id   INT NOT NULL,
    group_id     INT NOT NULL,
    uploader_id  INT NOT NULL,
    file_name    VARCHAR(255) NOT NULL,
    file_size    VARCHAR(30),
    file_type    VARCHAR(30),
    status       ENUM('Unviewed','Accepted','Rejected') DEFAULT 'Unviewed',
    uploaded_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_by  INT NULL,
    reviewed_at  DATETIME NULL,
    review_note  TEXT,
    FOREIGN KEY (project_id)  REFERENCES `Projects`(id) ON DELETE CASCADE,
    FOREIGN KEY (group_id)    REFERENCES `Groups`(id)   ON DELETE CASCADE,
    FOREIGN KEY (uploader_id) REFERENCES `Students`(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES `Teachers`(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO `Files` (project_id, group_id, uploader_id, file_name, file_size, file_type, status, uploaded_at, reviewed_by, reviewed_at, review_note) VALUES
    (1, 1, 1, 'Proposal_v2.docx',                '2.4 MB', 'DOCX', 'Unviewed', '2026-10-04 10:42:00', NULL, NULL, NULL),
    (1, 1, 3, 'Requirement_Analysis.pdf',        '1.8 MB', 'PDF',  'Accepted', '2026-10-03 15:20:00', 1,   '2026-10-04 09:00:00', 'Looks good.'),
    (1, 1, 4, 'UI_Wireframe.fig',                '5.1 MB', 'FIG',  'Unviewed', '2026-10-02 09:15:00', NULL, NULL, NULL),
    (1, 1, 1, 'Sprint_Plan_Oct.xlsx',            '180 KB', 'XLSX', 'Accepted', '2026-09-25 17:45:00', 1,   '2026-09-26 09:00:00', 'OK.'),
    (1, 1, 1, 'Kickoff_Notes.docx',              '120 KB', 'DOCX', 'Accepted', '2026-09-10 09:00:00', 1,   '2026-09-10 12:00:00', 'OK.'),
    (1, 1, 2, 'Brainstorm.png',                  '780 KB', 'PNG',  'Accepted', '2026-09-12 14:00:00', 1,   '2026-09-12 16:00:00', 'OK.'),
    (1, 2, 3, 'Database_Schema.sql',             '120 KB', 'SQL',  'Accepted', '2026-10-01 11:00:00', 1,   '2026-10-01 15:00:00', 'Approved.'),
    (1, 2, 2, 'API_Spec.pdf',                    '890 KB', 'PDF',  'Rejected', '2026-09-30 16:30:00', 1,   '2026-10-01 10:00:00', 'Missing authentication section.'),
    (1, 2, 3, 'ER_Diagram.png',                  '1.1 MB', 'PNG',  'Accepted', '2026-09-15 10:00:00', 1,   '2026-09-15 14:00:00', 'Nice.'),
    (2, 3, 6, 'Meeting_Minutes_2026-09-28.docx', '240 KB', 'DOCX', 'Accepted', '2026-09-28 14:00:00', 1,   '2026-09-28 16:00:00', 'OK.'),
    (2, 3, 7, 'Budget_Plan.xlsx',                '310 KB', 'XLSX', 'Rejected', '2026-09-26 10:00:00', 1,   '2026-09-27 09:00:00', 'Numbers do not match the proposal.'),
    (2, 3, 8, 'Research_Notes.pdf',              '1.1 MB', 'PDF',  'Unviewed', '2026-09-22 13:10:00', NULL, NULL, NULL),
    (4, 4, 8,  'SRS_v1.docx',                    '3.1 MB', 'DOCX', 'Accepted', '2026-10-05 09:30:00', 2,   '2026-10-05 14:00:00', 'Looks good.'),
    (4, 4, 9,  'SeatMap_Mockup.png',             '1.2 MB', 'PNG',  'Unviewed', '2026-10-06 11:15:00', NULL, NULL, NULL),
    (4, 4, 10, 'API_Design.pdf',                 '880 KB', 'PDF',  'Rejected', '2026-10-06 15:00:00', 2,   '2026-10-07 09:00:00', 'Please add error handling.'),
    (5, 5, 9,  'Menu_Schema.sql',                '95 KB',  'SQL',  'Accepted', '2026-10-02 10:00:00', 2,   '2026-10-02 16:00:00', 'Approved.'),
    (5, 5, 12, 'Payment_Flow.pdf',               '1.4 MB', 'PDF',  'Unviewed', '2026-10-05 08:45:00', NULL, NULL, NULL),
    (5, 5, 13, 'Order_Tracking.fig',             '2.0 MB', 'FIG',  'Unviewed', '2026-10-06 09:20:00', NULL, NULL, NULL),
    (6, 6, 13, 'Dataset_Description.pdf',        '1.5 MB', 'PDF',  'Accepted', '2026-09-30 14:00:00', 3,   '2026-10-01 09:00:00', 'Good.'),
    (6, 6, 15, 'Baseline_Model.ipynb',           '2.8 MB', 'IPYNB','Unviewed', '2026-10-06 16:40:00', NULL, NULL, NULL),
    (6, 6, 16, 'Prompt_Templates.txt',           '45 KB',  'TXT',  'Unviewed', '2026-10-07 10:00:00', NULL, NULL, NULL),
    (8, 9, 20, 'Company_List.xlsx',              '420 KB', 'XLSX', 'Accepted', '2026-10-05 11:00:00', 4,   '2026-10-05 15:30:00', 'Verified.'),
    (8, 9, 21, 'Matching_Algo.pdf',              '950 KB', 'PDF',  'Unviewed', '2026-10-07 13:00:00', NULL, NULL, NULL),
    (8, 9, 22, 'Dashboard_Wireframe.fig',        '3.5 MB', 'FIG',  'Unviewed', '2026-10-07 14:30:00', NULL, NULL, NULL);


-- =========================================================
-- 14. Messages
-- =========================================================
CREATE TABLE `Messages` (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    group_id      INT NOT NULL,
    sender_id     INT NOT NULL,
    content       TEXT,
    attachment_id INT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id)      REFERENCES `Groups`(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id)     REFERENCES `Students`(id) ON DELETE CASCADE,
    FOREIGN KEY (attachment_id) REFERENCES `Files`(id)   ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO `Messages` (group_id, sender_id, content) VALUES
    (1, 1, 'Please update your assigned task progress before the team meeting.'),
    (1, 3, 'I have completed the student login page and I am currently developing the student dashboard.'),
    (1, 2, 'I have uploaded the latest project proposal to the shared files page.'),
    (1, 4, 'What time will the next team meeting begin?'),
    (1, 1, 'The meeting will begin at 3:30 PM today. Please prepare your current progress and questions.'),
    (4, 8, 'Hi team, let us start the SRS document today.'),
    (4, 9, 'OK. I will draft the introduction section.'),
    (4, 10, 'I will work on the functional requirements.'),
    (5, 9, 'Payment integration is tricky. Any ideas?'),
    (5, 12, 'Let us use Stripe test mode first.'),
    (5, 13, 'Agreed. I will set up a sandbox account.'),
    (6, 13, 'Baseline model accuracy is 78%.'),
    (6, 15, 'Can we try a bigger transformer model?'),
    (6, 16, 'Yes, I will test it this week.'),
    (9, 20, 'Company list is ready. Please review.'),
    (9, 21, 'Matching algorithm draft done. Testing now.'),
    (9, 22, 'Dashboard wireframe uploaded.');


-- =========================================================
-- 15. Announcements
-- =========================================================
CREATE TABLE `Announcements` (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id        INT NOT NULL,
    title             VARCHAR(200) NOT NULL,
    message           TEXT NOT NULL,
    target_group_id   INT NULL,
    target_project_id INT NULL,
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id)        REFERENCES `Teachers`(id) ON DELETE CASCADE,
    FOREIGN KEY (target_group_id)   REFERENCES `Groups`(id)   ON DELETE SET NULL,
    FOREIGN KEY (target_project_id) REFERENCES `Projects`(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO `Announcements` (teacher_id, title, message, target_project_id) VALUES
    (1, 'Progress Report Deadline', 'Please submit your progress report by 15 Oct.', 1),
    (1, 'Welcome to FYP',           'Welcome everyone to the Final Year Project.',   NULL),
    (2, 'Mid-term Demo Schedule',   'Mid-term demo will be held on 20 Nov 2026.',   4),
    (2, 'Campus Food Order Update', 'Please finalise the payment flow by end Oct.',  5),
    (3, 'AI Study Buddy Reminder',  'Please submit the baseline model results.',     6),
    (3, 'Green Campus Kickoff',     'Kickoff meeting on 15 Oct 2026.',               7),
    (4, 'Career Path Finder',       'Dashboard design review next Monday.',          8),
    (4, 'Virtual Lab Update',       'VR prototype draft due end of month.',          9),
    (5, 'Campus Events Update',     'UI design must be done by mid Nov.',           10),
    (5, 'Study Group Match',        'Voting for leader opens next week.',           11);


-- =========================================================
-- 16. Notifications
-- =========================================================
CREATE TABLE `Notifications` (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    type        ENUM('task','file','team','project','system') NOT NULL,
    title       VARCHAR(200) NOT NULL,
    message     TEXT,
    link        VARCHAR(255),
    is_read     TINYINT(1) DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES `Users`(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `Notifications` (user_id, type, title, message, link) VALUES
    (6,  'task', 'New Task Assigned',   'You have been assigned "Develop Student Dashboard".', 'student_task.php'),
    (6,  'file', 'New File Uploaded',   'Student B uploaded "Proposal_v2.docx".',              'student_files.php'),
    (6,  'team', 'New Group Message',   'A new meeting reminder was posted.',                  'student_chat.php'),
    (13, 'task', 'New Task Assigned',   'You have been assigned "Design Seat Map UI".',        'student_task.php'),
    (14, 'file', 'File Approved',       'Your "Menu_Schema.sql" was approved.',                'student_files.php'),
    (15, 'file', 'File Rejected',       'Your "API_Design.pdf" was rejected.',                 'student_files.php'),
    (18, 'task', 'Task In Progress',    'Your task "Train Baseline Model" is ongoing.',        'student_task.php'),
    (20, 'team', 'New Group Message',   'A new message was posted in Team 06.',                'student_chat.php'),
    (25, 'file', 'File Approved',       'Your "Company_List.xlsx" was approved.',              'student_files.php'),
    (26, 'task', 'New Task Assigned',   'You have been assigned "Matching Algorithm".',        'student_task.php'),
    (27, 'team', 'New Group Message',   'A new message was posted in Team 09.',                'student_chat.php');


-- =========================================================
-- 17. Activities
-- =========================================================
CREATE TABLE `Activities` (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    project_id  INT NULL,
    group_id    INT NULL,
    actor_id    INT NOT NULL,
    action      VARCHAR(100) NOT NULL,
    detail      TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES `Projects`(id) ON DELETE CASCADE,
    FOREIGN KEY (group_id)   REFERENCES `Groups`(id)   ON DELETE CASCADE,
    FOREIGN KEY (actor_id)   REFERENCES `Users`(id)    ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `Activities` (project_id, group_id, actor_id, action, detail) VALUES
    (1,  1,  6, 'uploaded',  'uploaded Proposal_v2.docx'),
    (1,  1,  8, 'completed', 'marked Requirement Analysis complete'),
    (1,  1,  9, 'commented', 'commented on UI_Wireframe'),
    (1,  1,  6, 'elected',   'was elected as team leader'),
    (1,  1,  1, 'locked',    'locked the team roster'),
    (4,  4,  13,'uploaded',  'uploaded SRS_v1.docx'),
    (4,  4,  14,'uploaded',  'uploaded SeatMap_Mockup.png'),
    (4,  4,  15,'uploaded',  'uploaded API_Design.pdf'),
    (4,  4,  2, 'reviewed',  'accepted SRS_v1.docx'),
    (5,  5,  14,'uploaded',  'uploaded Menu_Schema.sql'),
    (5,  5,  17,'uploaded',  'uploaded Payment_Flow.pdf'),
    (6,  6,  18,'uploaded',  'uploaded Dataset_Description.pdf'),
    (6,  6,  20,'uploaded',  'uploaded Baseline_Model.ipynb'),
    (6,  6,  21,'uploaded',  'uploaded Prompt_Templates.txt'),
    (8,  9,  25,'uploaded',  'uploaded Company_List.xlsx'),
    (8,  9,  26,'uploaded',  'uploaded Matching_Algo.pdf'),
    (8,  9,  27,'uploaded',  'uploaded Dashboard_Wireframe.fig'),
    (1,  1,  6, 'created',   'created Task "Create System Interface"'),
    (5,  5,  2, 'reviewed',  'rejected API_Design.pdf');


-- =========================================================
-- Done.
-- =========================================================
