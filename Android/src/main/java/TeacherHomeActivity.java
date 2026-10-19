package com.example.teambox_sk2; // Change this to your actual package name

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.LinearLayout;
import android.widget.Toast;
import androidx.appcompat.app.AppCompatActivity;

import com.example.teambox_sk2.dbDefinder.fyp03DB;
import com.example.teambox_sk2.dbDefinder.fyp03Factory;

public class TeacherHomeActivity extends AppCompatActivity {

    // Declare your card views
    private LinearLayout cardProjects, cardTasks, cardTeams, cardFiles, cardChat;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_teacher_home); // Make sure this matches your XML file name

        // Initialize views by their IDs from the XML
        cardProjects = findViewById(R.id.cardProjects);
        cardTasks = findViewById(R.id.cardTasks);
        cardTeams = findViewById(R.id.cardTeams);
        cardFiles = findViewById(R.id.cardFiles);
        cardChat = findViewById(R.id.cardChat);

        // Set click listeners for each quick access card
        cardProjects.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                // TODO: Replace with your navigation code (e.g., Intent to ProjectsActivity)
                Toast.makeText(TeacherHomeActivity.this, "Projects Clicked", Toast.LENGTH_SHORT).show();
            }
        });

        cardTasks.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                // TODO: Handle Tasks click
                Toast.makeText(TeacherHomeActivity.this, "Tasks Clicked", Toast.LENGTH_SHORT).show();
            }
        });

        cardTeams.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                // TODO: Handle Teams click
                Toast.makeText(TeacherHomeActivity.this, "Teams Clicked", Toast.LENGTH_SHORT).show();
            }
        });

        cardFiles.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                // TODO: Handle Files click
                Toast.makeText(TeacherHomeActivity.this, "Files Clicked", Toast.LENGTH_SHORT).show();
            }
        });

        cardChat.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                // TODO: Handle Chat click
                Toast.makeText(TeacherHomeActivity.this, "Chat Clicked", Toast.LENGTH_SHORT).show();
            }
        });
    }
}
