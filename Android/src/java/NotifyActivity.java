package com.example.teambox_sk1;

import android.os.Bundle;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.ListView;
import android.widget.TextView;
import android.widget.Toast;
import androidx.appcompat.app.AppCompatActivity;

public class NotifyActivity extends AppCompatActivity {

    private Button btBack;
    private TextView tvTitle;
    private ListView notifyList;

    // Sample data for notifications
    private String[] sampleNotifications = {
            "System: Welcome to TeamBox Mobile App v1.0!",
            "Project: New project milestone deadline updated.",
            "Task: You have been assigned a new task by Cyrus.",
            "Team: Group chat message received from Thomas."
    };

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_notify);

        // Initialize UI components matching XML IDs
        btBack = findViewById(R.id.btBack);
        tvTitle = findViewById(R.id.tvTitle);
        notifyList = findViewById(R.id.lvNotifications);

        tvTitle.setText("Notifications");

        // Set up an adapter for the ListView
        ArrayAdapter<String> adapter = new ArrayAdapter<>(
                this,
                android.R.layout.simple_list_item_1,
                sampleNotifications
        );
        notifyList.setAdapter(adapter);

        // Handle item selection in the ListView
        notifyList.setOnItemClickListener((parent, view, position, id) -> {
            String selectedNotify = sampleNotifications[position];
            Toast.makeText(NotifyActivity.this, "Notification: " + selectedNotify, Toast.LENGTH_SHORT).show();
        });

        // Handle Back button action
        btBack.setOnClickListener(v -> finish());
    }
}
