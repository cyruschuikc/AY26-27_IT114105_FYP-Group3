package com.example.teambox_sk1;

import android.os.Bundle;
import android.widget.*;
import androidx.appcompat.app.AppCompatActivity;

public class SubmitTaskActivity extends AppCompatActivity {
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_submit_task);

        EditText etTaskTitle = findViewById(R.id.etTaskTitle);
        EditText etCommitMessage = findViewById(R.id.etCommitMessage);
        EditText etRepoLink = findViewById(R.id.etRepoLink);
        Spinner spTaskStatus = findViewById(R.id.spTaskStatus);
        TextView tvResult = findViewById(R.id.tvSubmitTaskResult);
        Button btnSubmitTask = findViewById(R.id.btnSubmitTask);
        Button btnBack = findViewById(R.id.btnBack);

        // Populate task status options
        spTaskStatus.setAdapter(new ArrayAdapter<>(this, android.R.layout.simple_spinner_item,
                new String[]{"In Progress", "Ready for Review", "Completed", "Needs Revision"}));

        btnSubmitTask.setOnClickListener(v -> {
            String title = etTaskTitle.getText().toString().trim();
            String message = etCommitMessage.getText().toString().trim();
            String link = etRepoLink.getText().toString().trim();
            String status = spTaskStatus.getSelectedItem().toString();

            if (title.isEmpty() || message.isEmpty()) {
                Toast.makeText(this, "Please fill in the task title and commit message", Toast.LENGTH_SHORT).show();
            } else {
                tvResult.setText("Task Commit Successful!\n" +
                        "Task: " + title + "\n" +
                        "Status: " + status + "\n" +
                        "Message: " + message + "\n" +
                        "Link: " + (link.isEmpty() ? "None" : link));
                Toast.makeText(this, "Commit submitted successfully!", Toast.LENGTH_SHORT).show();
            }
        });

        btnBack.setOnClickListener(v -> finish());
    }
}
