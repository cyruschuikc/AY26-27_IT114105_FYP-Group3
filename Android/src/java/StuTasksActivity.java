package com.example.teambox_sk1;

import android.os.Bundle;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.ListView;
import android.widget.TextView;
import android.widget.Toast;
import androidx.appcompat.app.AppCompatActivity;

public class StuTasksActivity extends AppCompatActivity {

    private Button btBack;
    private TextView tvTitle;
    private ListView taskList;

    // Sample data for tasks list
    private String[] sampleTasks = {
            "Design UI Wireframes for TeamBox",
            "Implement Login and Register Activities",
            "Connect Firebase Authentication",
            "Test Project Submission Form"
    };

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_stu_tasks);

        // Initialize UI components matching XML IDs
        btBack = findViewById(R.id.btBack);
        tvTitle = findViewById(R.id.tvTitle);
        taskList = findViewById(R.id.lvTasks);

        tvTitle.setText("My Tasks");

        // Set up an adapter for the ListView
        ArrayAdapter<String> adapter = new ArrayAdapter<>(
                this,
                android.R.layout.simple_list_item_1,
                sampleTasks
        );
        taskList.setAdapter(adapter);

        // Handle item selection in the ListView
        taskList.setOnItemClickListener((parent, view, position, id) -> {
            String selectedTask = sampleTasks[position];
            Toast.makeText(StuTasksActivity.this, "Selected Task: " + selectedTask, Toast.LENGTH_SHORT).show();
        });

        // Handle Back button action
        btBack.setOnClickListener(v -> finish());
    }
}
