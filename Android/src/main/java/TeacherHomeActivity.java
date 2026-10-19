package com.example.teambox_sk2;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.LinearLayout;
import android.widget.TextView;
import android.widget.Toast;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

import com.example.teambox_sk2.dbDefinder.fyp03DB;
import com.example.teambox_sk2.dbDefinder.fyp03Factory;
import com.example.teambox_sk2.dbDefinder.fyp03Adaptee;

public class TeacherHomeActivity extends AppCompatActivity {

    private LinearLayout cardProjects, cardTasks, cardTeams, cardFiles, cardChat;
    private TextView tvWelcome;

    private fyp03DB databaseAccessor;
    private String currentUsername = "Esther Yuen";
    private String userRole = "teacher";
    private String userToken = "";
    private long loginTime = 0;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        fyp03Adaptee.initializeContext(this);

        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_teacher_home);

        View rootView = findViewById(R.id.main);
        if (rootView != null) {
            ViewCompat.setOnApplyWindowInsetsListener(rootView, (v, insets) -> {
                Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
                v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
                return insets;
            });
        }

        extractIntentData();
        initViews();
        initDatabase();
        loadDashboardMetrics();
        setupClickListeners();
    }

    @Override
    protected void onResume() {
        super.onResume();
        loadDashboardMetrics();
    }

    private void extractIntentData() {
        Intent intent = getIntent();
        if (intent != null) {
            String username = intent.getStringExtra("EXTRA_USERNAME");
            if (username != null && !username.isEmpty()) {
                currentUsername = username;
            }
            userRole = intent.getStringExtra("EXTRA_ROLE");
            userToken = intent.getStringExtra("EXTRA_TOKEN");
            loginTime = intent.getLongExtra("EXTRA_LOGIN_TIME", 0);
        }
        if (userRole == null || userRole.isEmpty()) {
            userRole = "teacher";
        }
    }

    private Intent createSessionIntent(Class<?> targetActivityClass) {
        Intent intent = new Intent(TeacherHomeActivity.this, targetActivityClass);
        intent.putExtra("EXTRA_USERNAME", currentUsername);
        intent.putExtra("EXTRA_ROLE", userRole);
        intent.putExtra("EXTRA_TOKEN", userToken);
        intent.putExtra("EXTRA_LOGIN_TIME", loginTime);
        return intent;
    }

    private void initViews() {
        tvWelcome = findViewById(R.id.tvWelcome);
        if (tvWelcome != null) {
            tvWelcome.setText("Welcome Back, " + currentUsername);
        }

        cardProjects = findViewById(R.id.cardProjects);
        cardTasks = findViewById(R.id.cardTasks);
        cardTeams = findViewById(R.id.cardTeams);
        cardFiles = findViewById(R.id.cardFiles);
        cardChat = findViewById(R.id.cardChat);
    }

    private void initDatabase() {
        databaseAccessor = fyp03Factory.getDatabaseAdapter("OBJECT");
        if (databaseAccessor != null) {
            databaseAccessor.readData("teambox.sql");
        }
    }

    private void loadDashboardMetrics() {
        if (databaseAccessor == null) return;
        try {
            String projectData = databaseAccessor.getData("ALL_PROJECTS");
            int activeProjectsCount = 0;
            if (projectData != null && !projectData.contains("No matching records found")) {
                String[] lines = projectData.split("\n");
                for (String line : lines) {
                    if (!line.trim().isEmpty()) {
                        activeProjectsCount++;
                    }
                }
            }
            if (activeProjectsCount == 0) activeProjectsCount = 11;
        } catch (Exception e) {
            // Handle exceptions
        }
    }

    private void setupClickListeners() {
        if (cardProjects != null) {
            cardProjects.setOnClickListener(v -> {
                Toast.makeText(TeacherHomeActivity.this, "Opening Projects Management...", Toast.LENGTH_SHORT).show();
                startActivity(createSessionIntent(ProjectActivity.class));
            });
        }

        if (cardTasks != null) {
            cardTasks.setOnClickListener(v -> {
                Toast.makeText(TeacherHomeActivity.this, "Opening Tasks & Milestones...", Toast.LENGTH_SHORT).show();
                startActivity(createSessionIntent(TaskActivity.class));
            });
        }

        if (cardTeams != null) {
            cardTeams.setOnClickListener(v -> {
                Toast.makeText(TeacherHomeActivity.this, "Opening Teams & Groups...", Toast.LENGTH_SHORT).show();
                startActivity(createSessionIntent(TeamActivity.class));
            });
        }

        if (cardFiles != null) {
            cardFiles.setOnClickListener(v -> {
                Toast.makeText(TeacherHomeActivity.this, "Opening Files & Submissions...", Toast.LENGTH_SHORT).show();
                startActivity(createSessionIntent(FilesActivity.class));
            });
        }

        if (cardChat != null) {
            cardChat.setOnClickListener(v -> {
                Toast.makeText(TeacherHomeActivity.this, "Opening Messages & Chat...", Toast.LENGTH_SHORT).show();
                startActivity(createSessionIntent(ChatActivity.class));
            });
        }
    }
}
