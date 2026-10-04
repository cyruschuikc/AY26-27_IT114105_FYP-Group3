package com.example.teambox_sk1;

import android.app.DatePickerDialog;
import android.content.Intent;
import android.os.Bundle;
import android.widget.*;
import androidx.appcompat.app.AppCompatActivity;
import java.util.Calendar;

public class CreateProjectActivity extends AppCompatActivity {
    String selectedDeadline = "";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_create_project);

        EditText etTitle = findViewById(R.id.etTitle);
        EditText etScore = findViewById(R.id.etScore);
        Spinner sp = findViewById(R.id.spGroupSize);
        Button ddl = findViewById(R.id.btnDeadline);
        Button create = findViewById(R.id.btnCreate);
        Button student = findViewById(R.id.btnStudentPage);
        TextView out = findViewById(R.id.tvOutput);

        // Set adapter for group size spinner
        sp.setAdapter(new ArrayAdapter<>(this, android.R.layout.simple_spinner_item, new String[]{"1", "2", "3", "4", "5", "6", "7", "8"}));

        // DatePickerDialog for deadline selection
        ddl.setOnClickListener(v -> {
            Calendar c = Calendar.getInstance();
            new DatePickerDialog(this, (vw, y, m, d) -> {
                selectedDeadline = d + "/" + (m + 1) + "/" + y;
                ddl.setText(selectedDeadline);
            }, c.get(Calendar.YEAR), c.get(Calendar.MONTH), c.get(Calendar.DAY_OF_MONTH)).show();
        });

        // Handle project creation click
        create.setOnClickListener(v -> out.setText("Project Created\n" +
                "Title: " + etTitle.getText() + "\n" +
                "Group Size: " + sp.getSelectedItem() + "\n" +
                "Deadline: " + selectedDeadline + "\n" +
                "Score: " + etScore.getText()));

        // Optional navigation button (if you have another target activity)
        student.setOnClickListener(v -> {
            // Toast or handle submission navigation if needed
            Toast.makeText(this, "Student page action clicked", Toast.LENGTH_SHORT).show();
        });
    }
}
