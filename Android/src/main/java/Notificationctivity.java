package com.example.teambox_sk2;

import android.os.Bundle;
import android.view.View;
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

public class Notificationctivity extends AppCompatActivity {

    private ListView lvNotifications;
    private Button btBack;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        // Initialize context for SQLite helper
        fyp03Adaptee.initializeContext(this);

        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_notificationctivity);

        // Safely apply window insets if the root view ID 'main' exists
        View rootView = findViewById(R.id.main);
        if (rootView != null) {
            ViewCompat.setOnApplyWindowInsetsListener(rootView, (v, insets) -> {
                Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
                v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
                return insets;
            });
        }

        // Bind UI components
        lvNotifications = findViewById(R.id.lvNotifications);
        btBack = findViewById(R.id.btBack);

        // Handle Back button action
        if (btBack != null) {
            btBack.setOnClickListener(v -> finish());
        }

        // Load notifications from database or display mock items if empty
        loadNotificationsData();
    }

    private void loadNotificationsData() {
        try {
            // Use the Object adapter to fetch notification/task details
            fyp03DB db = fyp03Factory.getDatabaseAdapter("OBJECT");
            String rawData = db != null ? db.getData("tasks") : null;

            ArrayList<String> notificationList = new ArrayList<>();
            if (rawData != null && !rawData.contains("No matching records")) {
                String[] items = rawData.split("\n");
                for (String item : items) {
                    if (!item.trim().isEmpty()) {
                        notificationList.add(item.trim());
                    }
                }
            }

            // If empty, load fallback demo data matching your app theme
            if (notificationList.isEmpty()) {
                notificationList.add("New Task Assigned: Develop Student Dashboard [Priority: High]");
                notificationList.add("File Approved: Menu_Schema.sql [Status: Accepted]");
                notificationList.add("Team Message: Meeting reminder posted in Team 01");
            }

            ArrayAdapter<String> adapter = new ArrayAdapter<>(
                    this,
                    android.R.layout.simple_list_item_1,
                    notificationList
            );
            lvNotifications.setAdapter(adapter);

        } catch (Exception e) {
            Toast.makeText(this, "Error loading notifications: " + e.getMessage(), Toast.LENGTH_SHORT).show();
        }
    }
}
