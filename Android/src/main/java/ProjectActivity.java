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

public class ProjectActivity extends AppCompatActivity {

    private Button btBack, btNew;
    private ListView lvProjects;
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
        setContentView(R.layout.activity_project);

        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });

        extractIntentData();
        initViews();
        initDatabase();
        loadProjectData();
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
    }

    private Intent createSessionIntent(Class<?> targetActivityClass) {
        Intent intent = new Intent(ProjectActivity.this, targetActivityClass);
        intent.putExtra("EXTRA_USERNAME", currentUsername);
        intent.putExtra("EXTRA_ROLE", userRole);
        intent.putExtra("EXTRA_TOKEN", userToken);
        intent.putExtra("EXTRA_LOGIN_TIME", loginTime);
        return intent;
    }

    private void initViews() {
        btBack = findViewById(R.id.btBack);
        btNew = findViewById(R.id.btNew);
        lvProjects = findViewById(R.id.lvProjects);
    }

    private void initDatabase() {
        databaseAccessor = fyp03Factory.getDatabaseAdapter("OBJECT");
        if (databaseAccessor != null) {
            databaseAccessor.readData("teambox.sql");
        }
    }

    private void loadProjectData() {
        String projectResult = databaseAccessor != null ? databaseAccessor.getData("ALL_PROJECTS") : null;

        ArrayList<String> projectList = new ArrayList<>();
        if (projectResult != null && !projectResult.contains("No matching records found")) {
            String[] lines = projectResult.split("\n");
            projectList.addAll(Arrays.asList(lines));
        } else {
            projectList.add("TeamBox - Software Engineering (Executing, 60%)");
            projectList.add("Smart Attendance - Mobile App (Executing, 35%)");
            projectList.add("Smart Library - Web App (Executing, 40%)");
        }

        ArrayAdapter<String> adapter = new ArrayAdapter<>(
                this,
                android.R.layout.simple_list_item_1,
                projectList
        );
        lvProjects.setAdapter(adapter);
    }

    private void setupListeners() {
        if (btBack != null) {
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
        }

        if (btNew != null) {
            btNew.setOnClickListener(v -> {
                Intent intent = createSessionIntent(CreateProjectActivity.class);
                startActivity(intent);
            });
        }

        if (lvProjects != null) {
            lvProjects.setOnItemClickListener((parent, view, position, id) -> {
                String selectedItem = (String) parent.getItemAtPosition(position);

                if (selectedItem.contains("No projects found")) {
                    return;
                }

                // 修正後的字串解析，安全萃取專案名稱
                String projectName = selectedItem;
                if (selectedItem.contains(" - ")) {
                    projectName = selectedItem.split(" - ")[0].trim();
                }

                Intent intent = createSessionIntent(ProjectDetailActivity.class);
                intent.putExtra("PROJECT_NAME", projectName);
                startActivity(intent);
            });
        }
    }
}
