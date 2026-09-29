-- This sql query file is acts for DB/Table debugging
-- not is using for create table/store data/create view

SELECT 
    u.id AS user_id,
    u.username,
    u.email,
    s.student_number,
    s.major,
    s.grade_level
FROM Users u
INNER JOIN Students s ON u.id = s.user_id;
