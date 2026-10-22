package com.example.teambox_sk2;

import android.content.Intent;
import android.os.Bundle;
import android.widget.LinearLayout;
import android.widget.TextView;

import androidx.appcompat.app.AppCompatActivity;

public class TeacherHomeActivity extends AppCompatActivity {

    private TextView tvWelcome;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_teacher_home);

        tvWelcome = findViewById(R.id.tvWelcome);

        tvWelcome.setText("Welcome Back, Teacher");

        LinearLayout cardProjects = findViewById(R.id.cardProjects);
        LinearLayout cardTasks = findViewById(R.id.cardTasks);
        LinearLayout cardTeams = findViewById(R.id.cardTeams);
        LinearLayout cardFiles = findViewById(R.id.cardFiles);
        LinearLayout cardChat = findViewById(R.id.cardChat);

        cardProjects.setOnClickListener(v ->
                startActivity(new Intent(this, TeacherProjectsActivity.class)));

        cardTasks.setOnClickListener(v ->
                startActivity(new Intent(this, TeacherTasksActivity.class)));

        cardTeams.setOnClickListener(v ->
                startActivity(new Intent(this, TeacherTeamsActivity.class)));

        cardFiles.setOnClickListener(v ->
                startActivity(new Intent(this, FilesActivity.class)));

        cardChat.setOnClickListener(v ->
                startActivity(new Intent(this, ChatActivity.class)));
    }
}