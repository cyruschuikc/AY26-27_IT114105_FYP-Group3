SELECT 
    u.id AS user_id,
    u.username,
    u.email,
    t.department,
    t.title,
    t.hire_date
FROM Users u
INNER JOIN Teachers t ON u.id = t.user_id;
