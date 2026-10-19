package com.example.teambox_sk2;

import android.content.Intent;
import android.os.Bundle;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.ListView;
import android.widget.Toast;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

import com.example.teambox_sk2.dbDefinder.fyp03DB;
import com.example.teambox_sk2.dbDefinder.fyp03Factory;
import com.example.teambox_sk2.dbDefinder.fyp03Adaptee;

import java.util.ArrayList;
import java.util.Arrays;

public class TaskActivity extends AppCompatActivity {

    private Button btBack, btNewTask;
    private ListView lvTasks;
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
        setContentView(R.layout.activity_task);

        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });

        extractIntentData();
        initViews();
        initDatabase();
        loadTaskData();
        setupListeners();
    }

    private void extractIntentData() {
        Intent intent = getIntent();
        if (intent != null) {
            currentUsername = intent.getStringExtra("EXTRA_USERNAME");
            userRole = intent.getStringExtra("EXTRA_ROLE");
            userToken = intent.getStringExtra("EXTRA_TOKEN");
            loginTime = intent.getLongExtra("EXTRA_LOGIN_TIME", 0);
        }
        if (userRole == null) {
            userRole = "student";
        }
    }

    private Intent createSessionIntent(Class<?> targetActivityClass) {
        Intent intent = new Intent(TaskActivity.this, targetActivityClass);
        intent.putExtra("EXTRA_USERNAME", currentUsername);
        intent.putExtra("EXTRA_ROLE", userRole);
        intent.putExtra("EXTRA_TOKEN", userToken);
        intent.putExtra("EXTRA_LOGIN_TIME", loginTime);
        return intent;
    }

    private void initViews() {
        btBack = findViewById(R.id.btBack);
        btNewTask = findViewById(R.id.btNewTask);
        lvTasks = findViewById(R.id.lvTasks);
    }

    private void initDatabase() {
        databaseAccessor = fyp03Factory.getDatabaseAdapter("OBJECT");
        if (databaseAccessor != null) {
            databaseAccessor.readData("teambox.sql");
        }
    }

    private void loadTaskData() {
        String taskResult = databaseAccessor != null ? databaseAccessor.getData("tasks") : null;

        ArrayList<String> taskList = new ArrayList<>();
        if (taskResult != null && !taskResult.contains("No matching records found")) {
            String[] lines = taskResult.split("\n");
            taskList.addAll(Arrays.asList(lines));
        } else {
            taskList.add("Complete Project Proposal - High Priority (Completed)");
            taskList.add("Requirement Analysis - Medium Priority (Completed)");
            taskList.add("Create System Interface - High Priority (In Progress)");
            taskList.add("Database Schema Design - Medium Priority (Pending)");
        }

        ArrayAdapter<String> adapter = new ArrayAdapter<>(
                this,
                android.R.layout.simple_list_item_1,
                taskList
        );
        lvTasks.setAdapter(adapter);
    }

    private void setupListeners() {
        btBack.setOnClickListener(v -> {
            Intent intent;
            if ("teacher".equals(userRole)) {
                intent = createSessionIntent(TeacherHomeActivity.class);
            } else {
                intent = createSessionIntent(StuHomeActivity.class);
            }
            startActivity(intent);
            finish();
        });

        btNewTask.setOnClickListener(v -> {
            Intent intent = createSessionIntent(CreateTaskActivity.class);
            startActivity(intent);
        });

        lvTasks.setOnItemClickListener((parent, view, position, id) -> {
            String selectedItem = (String) parent.getItemAtPosition(position);

            if (selectedItem == null || selectedItem.isEmpty()) {
                return;
            }

            // 安全擷取任務名稱
            String taskTitle = selectedItem;
            if (selectedItem.contains(" - ")) {
                taskTitle = selectedItem.split(" - ")[0].trim();
            }

            Intent intent = createSessionIntent(TaskDetailActivity.class);
            intent.putExtra("TASK_TITLE", taskTitle);
            startActivity(intent);
        });
    }
}
