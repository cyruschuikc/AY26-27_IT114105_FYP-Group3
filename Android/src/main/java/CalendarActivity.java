package com.example.teambox_sk2;

import android.os.Bundle;
import android.view.View;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.CalendarView;
import android.widget.ListView;
import android.widget.TextView;
import android.widget.Toast;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

import com.example.teambox_sk2.dbDefinder.fyp03DB;
import com.example.teambox_sk2.dbDefinder.fyp03Factory;
import com.example.teambox_sk2.dbDefinder.fyp03Adaptee;

import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.Calendar;
import java.util.Locale;

public class CalendarActivity extends AppCompatActivity {

    private Button btnBack;
    private CalendarView calendarView;
    private TextView tvSelectedDate;
    private ListView lvDayEvents;

    private fyp03DB databaseAccessor;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        // Initialize SQLite helper context
        fyp03Adaptee.initializeContext(this);

        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_calendar);

        // Handle window system bars insets
        View rootView = findViewById(android.R.id.content);
        if (rootView != null) {
            ViewCompat.setOnApplyWindowInsetsListener(rootView, (v, insets) -> {
                Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
                v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
                return insets;
            });
        }

        initViews();
        initDatabase();
        setupListeners();

        // Load events for today's default date on start
        loadEventsForDate(System.currentTimeMillis());
    }

    private void initViews() {
        btnBack = findViewById(R.id.btnBack);
        calendarView = findViewById(R.id.calendarView);
        tvSelectedDate = findViewById(R.id.tvSelectedDate);
        lvDayEvents = findViewById(R.id.lvDayEvents);
    }

    private void initDatabase() {
        databaseAccessor = fyp03Factory.getDatabaseAdapter("OBJECT");
        if (databaseAccessor != null) {
            databaseAccessor.readData("teambox.sql");
        }
    }

    private void setupListeners() {
        // Handle Back button click
        if (btnBack != null) {
            btnBack.setOnClickListener(v -> finish());
        }

        // Handle calendar date change selection
        if (calendarView != null) {
            calendarView.setOnDateChangeListener((view, year, month, dayOfMonth) -> {
                Calendar calendar = Calendar.getInstance();
                calendar.set(year, month, dayOfMonth);
                loadEventsForDate(calendar.getTimeInMillis());
            });
        }
    }

    private void loadEventsForDate(long timestamp) {
        SimpleDateFormat sdf = new SimpleDateFormat("yyyy-MM-dd", Locale.getDefault());
        String formattedDate = sdf.format(timestamp);

        tvSelectedDate.setText("Events for: " + formattedDate);

        try {
            ArrayList<String> eventList = new ArrayList<>();

            // Query tasks or project milestones from database adapter
            String taskData = databaseAccessor != null ? databaseAccessor.getData("tasks") : null;

            if (taskData != null && !taskData.contains("No matching records")) {
                String[] items = taskData.split("\n");
                for (String item : items) {
                    if (!item.trim().isEmpty()) {
                        // Filter or display matching tasks (or show all active schedule items for demo)
                        eventList.add(item.trim());
                    }
                }
            }

            // Fallback sample schedule items if none found for the date
            if (eventList.isEmpty()) {
                eventList.add("Team Progress Report Due [Priority: High]");
                eventList.add("Mid-term System Design Review @ 3:30 PM");
                eventList.add("Project Sprint Planning Meeting");
            }

            ArrayAdapter<String> adapter = new ArrayAdapter<>(
                    this,
                    android.R.layout.simple_list_item_1,
                    eventList
            );
            lvDayEvents.setAdapter(adapter);

        } catch (Exception e) {
            Toast.makeText(this, "Error loading calendar events: " + e.getMessage(), Toast.LENGTH_SHORT).show();
        }
    }
}
