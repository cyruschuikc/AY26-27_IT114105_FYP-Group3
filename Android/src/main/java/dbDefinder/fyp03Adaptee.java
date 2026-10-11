package com.example.teambox_sk2.dbDefinder;

import android.content.Context;
import android.database.Cursor;
import android.database.sqlite.SQLiteDatabase;
import android.database.sqlite.SQLiteOpenHelper;
import android.util.Log;

public class fyp03Adaptee extends SQLiteOpenHelper {
    private static final String DATABASE_NAME = "teambox_local.db";
    private static final int DATABASE_VERSION = 5; // Bumped version to include chat table schema
    private static final String TAG = "fyp03Adaptee";

    private static Context appContext;

    public static void initializeContext(Context context) {
        appContext = context.getApplicationContext();
    }

    public fyp03Adaptee() {
        super(appContext, DATABASE_NAME, null, DATABASE_VERSION);
    }

    @Override
    public void onCreate(SQLiteDatabase db) {
        db.execSQL("CREATE TABLE users (" +
                "id INTEGER PRIMARY KEY AUTOINCREMENT, " +
                "username TEXT NOT NULL, " +
                "email TEXT UNIQUE NOT NULL, " +
                "password_hash TEXT NOT NULL, " +
                "role TEXT NOT NULL);");

        db.execSQL("CREATE TABLE projects (" +
                "id INTEGER PRIMARY KEY AUTOINCREMENT, " +
                "name TEXT NOT NULL, " +
                "category TEXT, " +
                "status TEXT, " +
                "progress INTEGER);");

        db.execSQL("CREATE TABLE tasks (" +
                "id INTEGER PRIMARY KEY AUTOINCREMENT, " +
                "project_id INTEGER, " +
                "group_id INTEGER, " +
                "title TEXT NOT NULL, " +
                "description TEXT, " +
                "status TEXT, " +
                "priority TEXT, " +
                "due_date TEXT);");

        db.execSQL("CREATE TABLE files (" +
                "id INTEGER PRIMARY KEY AUTOINCREMENT, " +
                "name TEXT NOT NULL, " +
                "category TEXT, " +
                "shared_with TEXT, " +
                "is_shared INTEGER);");

        db.execSQL("CREATE TABLE chat (" +
                "id INTEGER PRIMARY KEY AUTOINCREMENT, " +
                "sender TEXT NOT NULL, " +
                "message TEXT NOT NULL);");

        // Seed initial users
        db.execSQL("INSERT INTO users (id, username, email, password_hash, role) VALUES (1, 'Esther Yuen', 'wyyuen@vtc.edu.hk', '12345', 'teacher');");
        db.execSQL("INSERT INTO users (id, username, email, password_hash, role) VALUES (6, 'CHAN Ho Yin', '260222111@stu.vtc.edu.hk', '12345', 'student');");
        db.execSQL("INSERT INTO users (id, username, email, password_hash, role) VALUES (7, 'CHUI Ka Chun', '260222112@stu.vtc.edu.hk', '12345', 'student');");

        // Seed initial projects
        db.execSQL("INSERT INTO projects (id, name, category, status, progress) VALUES (1, 'TeamBox', 'Software Engineering', 'Executing', 60);");
        db.execSQL("INSERT INTO projects (id, name, category, status, progress) VALUES (2, 'Smart Attendance', 'Mobile App', 'Executing', 35);");

        // Seed initial tasks
        db.execSQL("INSERT INTO tasks (id, project_id, group_id, title, description, status, priority, due_date) VALUES (1, 1, 1, 'Complete Project Proposal', 'Write initial draft', 'Completed', 'High', '2026-05-01');");
        db.execSQL("INSERT INTO tasks (id, project_id, group_id, title, description, status, priority, due_date) VALUES (2, 1, 1, 'Requirement Analysis', 'Gather requirements', 'Completed', 'Medium', '2026-05-10');");

        // Seed initial files
        db.execSQL("INSERT INTO files (name, category, shared_with, is_shared) VALUES ('FYP_Proposal_Draft.docx (2.4 MB)', 'Personal', 'Private', 0);");
        db.execSQL("INSERT INTO files (name, category, shared_with, is_shared) VALUES ('Research_Notes.pdf (1.1 MB)', 'Personal', 'Private', 0);");
        db.execSQL("INSERT INTO files (name, category, shared_with, is_shared) VALUES ('TeamBox_Requirements.pdf', 'Shared', 'CHUI Ka Chun (Group Member)', 1);");
        db.execSQL("INSERT INTO files (name, category, shared_with, is_shared) VALUES ('Database_Schema_v1.sql', 'Shared', 'Esther Yuen (Teacher)', 1);");

        // Seed initial chat messages
        db.execSQL("INSERT INTO chat (sender, message) VALUES ('Esther Yuen', 'Welcome to the TeamBox project channel.');");
        db.execSQL("INSERT INTO chat (sender, message) VALUES ('CHAN Ho Yin', 'Thanks! Working on the architecture setup.');");
        db.execSQL("INSERT INTO chat (sender, message) VALUES ('CHUI Ka Chun', 'Database schema is ready.');");

        Log.d(TAG, "Database created and seeded successfully with users, projects, tasks, files, and chat tables.");
    }

    @Override
    public void onUpgrade(SQLiteDatabase db, int oldVersion, int newVersion) {
        db.execSQL("DROP TABLE IF EXISTS users;");
        db.execSQL("DROP TABLE IF EXISTS projects;");
        db.execSQL("DROP TABLE IF EXISTS tasks;");
        db.execSQL("DROP TABLE IF EXISTS files;");
        db.execSQL("DROP TABLE IF EXISTS chat;");
        onCreate(db);
    }

    public String legacyReadFile(String sqlFilePath) {
        return "SQLite Database active and running.";
    }

    public String legacyGetData(String sqlFilePath, String searchTerm) {
        SQLiteDatabase db = this.getReadableDatabase();
        StringBuilder results = new StringBuilder();

        try {
            Cursor cursor = null;
            if (searchTerm.equals("ALL_PROJECTS")) {
                cursor = db.rawQuery("SELECT * FROM projects", null);
            } else if (searchTerm.equals("tasks")) {
                cursor = db.rawQuery("SELECT * FROM tasks", null);
            } else if (searchTerm.equals("chat")) {
                cursor = db.rawQuery("SELECT * FROM chat", null);
            } else if (searchTerm.equals("personal_files")) {
                cursor = db.rawQuery("SELECT * FROM files WHERE is_shared = 0", null);
            } else if (searchTerm.equals("shared_files") || searchTerm.equals("files")) {
                cursor = db.rawQuery("SELECT * FROM files WHERE is_shared = 1", null);
            } else if (searchTerm.equals("student") || searchTerm.equals("teacher") || searchTerm.equals("leader") || searchTerm.equals("ALL_USERS")) {
                if (searchTerm.equals("ALL_USERS")) {
                    cursor = db.rawQuery("SELECT * FROM users", null);
                } else {
                    cursor = db.rawQuery("SELECT * FROM users WHERE role = ?", new String[]{searchTerm});
                }
            } else {
                cursor = db.rawQuery("SELECT * FROM users WHERE username LIKE ? OR email LIKE ? UNION SELECT * FROM projects WHERE name LIKE ?",
                        new String[]{"%" + searchTerm + "%", "%" + searchTerm + "%", "%" + searchTerm + "%"});
            }

            if (cursor != null && cursor.moveToFirst()) {
                do {
                    if (searchTerm.equals("ALL_PROJECTS")) {
                        String name = cursor.getString(cursor.getColumnIndexOrThrow("name"));
                        String category = cursor.getString(cursor.getColumnIndexOrThrow("category"));
                        String status = cursor.getString(cursor.getColumnIndexOrThrow("status"));
                        int progress = cursor.getInt(cursor.getColumnIndexOrThrow("progress"));
                        results.append(name).append(" - ").append(category).append(" (").append(status).append(", ").append(progress).append("%)\n");
                    } else if (searchTerm.equals("tasks")) {
                        String title = cursor.getString(cursor.getColumnIndexOrThrow("title"));
                        String priority = cursor.getString(cursor.getColumnIndexOrThrow("priority"));
                        String status = cursor.getString(cursor.getColumnIndexOrThrow("status"));
                        results.append(title).append(" - ").append(priority).append(" Priority (").append(status).append(")\n");
                    } else if (searchTerm.equals("chat")) {
                        String sender = cursor.getString(cursor.getColumnIndexOrThrow("sender"));
                        String message = cursor.getString(cursor.getColumnIndexOrThrow("message"));
                        results.append(sender).append(": ").append(message).append("\n");
                    } else if (searchTerm.equals("personal_files")) {
                        String name = cursor.getString(cursor.getColumnIndexOrThrow("name"));
                        results.append(name).append("\n");
                    } else if (searchTerm.equals("shared_files") || searchTerm.equals("files")) {
                        String name = cursor.getString(cursor.getColumnIndexOrThrow("name"));
                        String sharedWith = cursor.getString(cursor.getColumnIndexOrThrow("shared_with"));
                        results.append(name).append("\n↳ Shared with: ").append(sharedWith).append("\n");
                    } else {
                        String username = cursor.getString(cursor.getColumnIndexOrThrow("username"));
                        String email = cursor.getString(cursor.getColumnIndexOrThrow("email"));
                        String passwordHash = cursor.getString(cursor.getColumnIndexOrThrow("password_hash")); // FIXED: Included password hash in result string
                        String role = cursor.getString(cursor.getColumnIndexOrThrow("role"));
                        results.append("User: ").append(username).append(" | Email: ").append(email).append(" | Password: ").append(passwordHash).append(" | Role: ").append(role).append("\n");
                    }
                } while (cursor.moveToNext());
            }
            if (cursor != null) {
                cursor.close();
            }

            if (results.length() == 0) {
                return "No matching records found for: " + searchTerm;
            }
        } catch (Exception e) {
            return "Error querying database: " + e.getMessage();
        }

        return results.toString();
    }

    public void legacyUpdateData(String sqlFilePath, String tableName, String updateQuery) {
        SQLiteDatabase db = this.getWritableDatabase();
        try {
            db.execSQL(updateQuery);
            Log.d(TAG, "[Adaptee] Successfully executed query on table [" + tableName + "]: " + updateQuery);
        } catch (Exception e) {
            Log.e(TAG, "[Adaptee] Failed to execute query: " + e.getMessage());
        }
    }
}
