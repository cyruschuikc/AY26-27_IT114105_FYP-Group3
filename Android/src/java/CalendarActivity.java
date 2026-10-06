package com.example.teambox_sk1;

import android.os.Bundle;
import android.widget.*;
import androidx.appcompat.app.AppCompatActivity;
import java.util.ArrayList;

public class CalendarActivity extends AppCompatActivity {

    private CalendarView calendarView;
    private ListView lvDayEvents;
    private TextView tvSelectedDate;
    private Button btnBack;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_calendar);

        calendarView = findViewById(R.id.calendarView);
        lvDayEvents = findViewById(R.id.lvDayEvents);
        tvSelectedDate = findViewById(R.id.tvSelectedDate);
        btnBack = findViewById(R.id.btnBack);

        tvSelectedDate.setText("Select a date to view deadlines & events");

        ArrayList<String> sampleEvents = new ArrayList<>();
        sampleEvents.add("10/6/2026: TeamBox Mobile App UI Wireframe Deadline");
        sampleEvents.add("15/6/2026: Database ERD Schema Review Meeting");
        sampleEvents.add("25/6/2026: Final Project Submission & Presentation");

        ArrayAdapter<String> adapter = new ArrayAdapter<>(
                this,
                android.R.layout.simple_list_item_1,
                sampleEvents
        );
        lvDayEvents.setAdapter(adapter);

        calendarView.setOnDateChangeListener((view, year, month, dayOfMonth) -> {
            String dateStr = dayOfMonth + "/" + (month + 1) + "/" + year;
            tvSelectedDate.setText("Events for: " + dateStr);
            Toast.makeText(CalendarActivity.this, "Selected Date: " + dateStr, Toast.LENGTH_SHORT).show();
        });

        btnBack.setOnClickListener(v -> finish());
    }
}
