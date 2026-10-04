package com.example.teambox_sk1;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.ListView;
import android.widget.TextView;
import android.widget.Toast;
import androidx.appcompat.app.AppCompatActivity;

public class StuProjectsActivity extends AppCompatActivity {

    private Button btBack;
    private Button btNew; // "New" button in the header
    private TextView tvTitle;
    private ListView projectList;

    // Sample data for the project list
    private String[] sampleProjects = {
            "Project Alpha: TeamBox Mobile App (Y2)",
            "Project Beta: TeamBox Web Platform",
            "Project Gamma: UI/UX Redesign System"
    };

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_stu_projects);

        // Initialize UI components matching IDs in XML
        btBack = findViewById(R.id.btBack);
        btNew = findViewById(R.id.btNew);
        tvTitle = findViewById(R.id.tvTitle);
        projectList = findViewById(R.id.lvProjects);

        tvTitle.setText("My Project");

        // Set up an adapter for the ListView
        ArrayAdapter<String> adapter = new ArrayAdapter<>(
                this,
                android.R.layout.simple_list_item_1,
                sampleProjects
        );
        projectList.setAdapter(adapter);

        // Handle item selection in the ListView
        projectList.setOnItemClickListener((parent, view, position, id) -> {
            String selectedProject = sampleProjects[position];
            Toast.makeText(StuProjectsActivity.this, "Selected: " + selectedProject, Toast.LENGTH_SHORT).show();
        });

        // Handle Back button action
        btBack.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Toast.makeText(StuProjectsActivity.this, "Going back...", Toast.LENGTH_SHORT).show();
                finish();
            }
        });

        // Handle "New" button action to launch CreateProjectActivity
        btNew.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Intent intent = new Intent(StuProjectsActivity.this, CreateProjectActivity.class);
                startActivity(intent);
            }
        });
    }
}
