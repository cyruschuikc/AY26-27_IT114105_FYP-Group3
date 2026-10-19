package com.example.teambox_sk2;

import android.content.Intent;
import android.os.Bundle;
import android.util.Log;
import android.view.Menu;
import android.view.MenuInflater;
import android.view.MenuItem;
import android.widget.ImageButton;
import android.widget.ProgressBar;
import android.widget.TextView;
import android.widget.Toast;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.appcompat.widget.Toolbar;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

import com.example.teambox_sk2.dbDefinder.fyp03DB;
import com.example.teambox_sk2.dbDefinder.fyp03Factory;
import com.example.teambox_sk2.dbDefinder.fyp03Adaptee;

public class StuHomeActivity extends AppCompatActivity {

    private static final String TAG = "StuHomeActivityLifecycle";

    private TextView welBackMsg, tvActiveProjects, tvNotifications, tvAssignedTasks, tvCompletedTasks, tvProgressText, tvProgressPercentage;
    private ProgressBar progressBar;
    private ImageButton ibViewProjects, ibViewTasks, ibViewTeams, ibSD, ibChat;

    private fyp03DB databaseAccessor;
    private String currentUsername = "";
    private String userRole = "";
    private String userToken = "";
    private long loginTime = 0;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        fyp03Adaptee.initializeContext(this);

        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_stu_home);

        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });

        Toolbar toolbar = findViewById(R.id.toolbar);
        setSupportActionBar(toolbar);

        extractIntentData();
        initViews();
        initDatabase();
        setupQuickAccessListeners();

        Log.d(TAG, "onCreate: StuHomeActivity created for user: " + currentUsername);
    }

    @Override
    protected void onResume() {
        super.onResume();
        loadDashboardMetrics();
        Log.d(TAG, "onResume: StuHomeActivity has user focus, metrics refreshed.");
    }

    private void extractIntentData() {
        Intent intent = getIntent();
        if (intent != null) {
            currentUsername = intent.getStringExtra("EXTRA_USERNAME");
            userRole = intent.getStringExtra("EXTRA_ROLE");
            userToken = intent.getStringExtra("EXTRA_TOKEN");
            loginTime = intent.getLongExtra("EXTRA_LOGIN_TIME", 0);
        }
        if (currentUsername == null || currentUsername.isEmpty()) {
            currentUsername = "CHUI Ka Chun";
        }
        if (userRole == null || userRole.isEmpty()) {
            userRole = "student";
        }
    }

    private Intent createSessionIntent(Class<?> targetActivityClass) {
        Intent intent = new Intent(StuHomeActivity.this, targetActivityClass);
        intent.putExtra("EXTRA_USERNAME", currentUsername);
        intent.putExtra("EXTRA_ROLE", userRole);
        intent.putExtra("EXTRA_TOKEN", userToken);
        intent.putExtra("EXTRA_LOGIN_TIME", loginTime);
        return intent;
    }

    private void initViews() {
        welBackMsg = findViewById(R.id.wel_back_msg);
        tvActiveProjects = findViewById(R.id.textView4);
        tvNotifications = findViewById(R.id.textView7);
        tvAssignedTasks = findViewById(R.id.textView10);
        tvCompletedTasks = findViewById(R.id.textView13);
        tvProgressPercentage = findViewById(R.id.textView17);
        tvProgressText = findViewById(R.id.textView18);
        progressBar = findViewById(R.id.progressBar2);

        ibViewProjects = findViewById(R.id.ibViewProjects);
        ibViewTasks = findViewById(R.id.ibViewTasks);
        ibViewTeams = findViewById(R.id.ibViewTeams);
        ibSD = findViewById(R.id.ibSD);
        ibChat = findViewById(R.id.ibChat);

        if (welBackMsg != null) {
            welBackMsg.setText("Welcome back, " + currentUsername + "!");
        }
    }

    private void initDatabase() {
        databaseAccessor = fyp03Factory.getDatabaseAdapter("OBJECT");
        if (databaseAccessor != null) {
            databaseAccessor.readData("teambox.sql");
        }
    }

    private void loadDashboardMetrics() {
        if (databaseAccessor == null) return;

        String projectData = databaseAccessor.getData("ALL_PROJECTS");
        int activeProjectCount = 0;
        if (projectData != null && !projectData.contains("No matching records found")) {
            String[] lines = projectData.split("\n");
            for (String line : lines) {
                if (!line.trim().isEmpty()) {
                    activeProjectCount++;
                }
            }
        }
        if (activeProjectCount == 0) activeProjectCount = 11;

        String taskData = databaseAccessor.getData("tasks");
        int totalTasksCount = 0;
        int completedTasksCount = 0;
        if (taskData != null && !taskData.contains("No matching records found")) {
            String[] lines = taskData.split("\n");
            for (String line : lines) {
                if (!line.trim().isEmpty()) {
                    totalTasksCount++;
                    if (line.contains("Completed")) {
                        completedTasksCount++;
                    }
                }
            }
        }
        if (totalTasksCount == 0) {
            totalTasksCount = 5;
            completedTasksCount = 3;
        }

        if (tvActiveProjects != null) tvActiveProjects.setText(String.valueOf(activeProjectCount));
        if (tvNotifications != null) tvNotifications.setText("3");
        if (tvAssignedTasks != null) tvAssignedTasks.setText(String.valueOf(totalTasksCount));
        if (tvCompletedTasks != null) tvCompletedTasks.setText(String.valueOf(completedTasksCount));

        int progressPercent = totalTasksCount > 0 ? (int) (((float) completedTasksCount / totalTasksCount) * 100) : 0;

        if (progressBar != null) {
            progressBar.setMax(100);
            progressBar.setProgress(progressPercent);
        }
        if (tvProgressPercentage != null) tvProgressPercentage.setText(progressPercent + "%");
        if (tvProgressText != null) tvProgressText.setText(completedTasksCount + " of " + totalTasksCount + " project tasks have been completed.");
    }

    private void setupQuickAccessListeners() {
        if (ibViewProjects != null) {
            ibViewProjects.setOnClickListener(v -> {
                Toast.makeText(StuHomeActivity.this, "Opening Projects...", Toast.LENGTH_SHORT).show();
                startActivity(createSessionIntent(ProjectActivity.class));
            });
        }

        if (ibViewTasks != null) {
            ibViewTasks.setOnClickListener(v -> {
                Toast.makeText(StuHomeActivity.this, "Opening My Tasks...", Toast.LENGTH_SHORT).show();
                startActivity(createSessionIntent(TaskActivity.class));
            });
        }

        if (ibViewTeams != null) {
            ibViewTeams.setOnClickListener(v -> {
                Toast.makeText(StuHomeActivity.this, "Opening Teams / Groups...", Toast.LENGTH_SHORT).show();
                startActivity(createSessionIntent(TeamActivity.class));
            });
        }

        if (ibSD != null) {
            ibSD.setOnClickListener(v -> {
                Toast.makeText(StuHomeActivity.this, "Opening Files & Documents...", Toast.LENGTH_SHORT).show();
                startActivity(createSessionIntent(FilesActivity.class));
            });
        }

        if (ibChat != null) {
            ibChat.setOnClickListener(v -> {
                Toast.makeText(StuHomeActivity.this, "Opening Chat...", Toast.LENGTH_SHORT).show();
                startActivity(createSessionIntent(ChatActivity.class));
            });
        }
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        MenuInflater inflater = getMenuInflater();
        inflater.inflate(R.menu.menu_stu_home_activity, menu);
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        int id = item.getItemId();

        if (id == R.id.action_notify) {
            startActivity(createSessionIntent(Notificationctivity.class));
            return true;
        } else if (id == R.id.action_calendar) {
            startActivity(createSessionIntent(CalendarActivity.class));
            return true;
        } else if (id == R.id.action_profile) {
            startActivity(createSessionIntent(StuProfileActivity.class));
            return true;
        } else if (id == R.id.action_logout) {
            Toast.makeText(this, "Logging out...", Toast.LENGTH_SHORT).show();
            Intent intent = new Intent(StuHomeActivity.this, MainActivity.class);
            startActivity(intent);
            finish();
            return true;
        } else if (id == R.id.action_projects) {
            startActivity(createSessionIntent(ProjectActivity.class));
            return true;
        } else if (id == R.id.action_tasks) {
            startActivity(createSessionIntent(TaskActivity.class));
            return true;
        } else if (id == R.id.action_files) {
            startActivity(createSessionIntent(FilesActivity.class));
            return true;
        } else if (id == R.id.action_teams) {
            startActivity(createSessionIntent(TeamActivity.class));
            return true;
        } else if (id == R.id.action_chat) {
            startActivity(createSessionIntent(ChatActivity.class));
            return true;
        } else if (id == R.id.action_setting) {
            Toast.makeText(this, "Settings clicked", Toast.LENGTH_SHORT).show();
            return true;
        }

        return super.onOptionsItemSelected(item);
    }
}
