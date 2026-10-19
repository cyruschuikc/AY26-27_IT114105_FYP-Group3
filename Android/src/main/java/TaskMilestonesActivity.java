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

public class TaskMilestonesActivity extends AppCompatActivity {

    private Button btBack;
    private ListView lvMilestones;
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
        setContentView(R.layout.activity_task_milestones);

        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });

        extractIntentData();
        initViews();
        initDatabase();
        loadMilestoneData();
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
        Intent intent = new Intent(TaskMilestonesActivity.this, targetActivityClass);
        intent.putExtra("EXTRA_USERNAME", currentUsername);
        intent.putExtra("EXTRA_ROLE", userRole);
        intent.putExtra("EXTRA_TOKEN", userToken);
        intent.putExtra("EXTRA_LOGIN_TIME", loginTime);
        return intent;
    }

    private void initViews() {
        btBack = findViewById(R.id.btBack);
        lvMilestones = findViewById(R.id.lvMilestones);
    }

    private void initDatabase() {
        databaseAccessor = fyp03Factory.getDatabaseAdapter("OBJECT");
        if (databaseAccessor != null) {
            databaseAccessor.readData("teambox.sql");
        }
    }

    private void loadMilestoneData() {
        String milestoneResult = databaseAccessor != null ? databaseAccessor.getData("milestones") : null;

        ArrayList<String> milestoneList = new ArrayList<>();
        if (milestoneResult != null && !milestoneResult.contains("No matching records found")) {
            String[] lines = milestoneResult.split("\n");
            milestoneList.addAll(Arrays.asList(lines));
        } else {
            milestoneList.add("Requirement Analysis | 2026-09-01 ~ 2026-10-05 | Status: Completed (100%)");
            milestoneList.add("System Design | 2026-10-06 ~ 2026-10-20 | Status: Completed (100%)");
            milestoneList.add("Core Development | 2026-10-21 ~ 2027-01-15 | Status: In Progress (55%)");
            milestoneList.add("Testing & Documentation | 2027-01-16 ~ 2027-03-20 | Status: Pending (0%)");
            milestoneList.add("Final Presentation | 2027-03-21 ~ 2027-04-30 | Status: Pending (0%)");
        }

        ArrayAdapter<String> adapter = new ArrayAdapter<>(
                this,
                android.R.layout.simple_list_item_1,
                milestoneList
        );
        lvMilestones.setAdapter(adapter);
    }

    private void setupListeners() {
        if (btBack != null) {
            btBack.setOnClickListener(v -> {
                Intent intent = createSessionIntent(TaskActivity.class);
                startActivity(intent);
                finish();
            });
        }

        if (lvMilestones != null) {
            lvMilestones.setOnItemClickListener((parent, view, position, id) -> {
                String selectedMilestone = (String) parent.getItemAtPosition(position);

                if (selectedMilestone == null || selectedMilestone.isEmpty()) {
                    return;
                }

                String milestoneTitle = selectedMilestone;
                if (selectedMilestone.contains(" | ")) {
                    milestoneTitle = selectedMilestone.split(" \\| ")[0].trim();
                }

                Toast.makeText(this, "Milestone: " + milestoneTitle, Toast.LENGTH_SHORT).show();
            });
        }
    }
}
