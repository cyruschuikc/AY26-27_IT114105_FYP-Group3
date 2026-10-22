package com.example.teambox_sk2;

import android.app.DatePickerDialog;
import android.os.Bundle;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Spinner;
import android.widget.TextView;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;

import java.util.Calendar;

public class CreateProjectActivity extends AppCompatActivity {

    private String selectedDeadline = "";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_create_project);

        EditText etTitle = findViewById(R.id.etTitle);
        EditText etScore = findViewById(R.id.etScore);

        Spinner spGroupSize = findViewById(R.id.spGroupSize);

        Button btnDeadline = findViewById(R.id.btnDeadline);
        Button btnCreate = findViewById(R.id.btnCreate);
        Button btnStudentPage = findViewById(R.id.btnStudentPage);

        TextView tvOutput = findViewById(R.id.tvOutput);

        String[] sizes = {"1","2","3","4","5","6","7","8"};

        ArrayAdapter<String> adapter =
                new ArrayAdapter<>(
                        this,
                        android.R.layout.simple_spinner_item,
                        sizes);

        adapter.setDropDownViewResource(
                android.R.layout.simple_spinner_dropdown_item);

        spGroupSize.setAdapter(adapter);

        btnDeadline.setOnClickListener(v -> {

            Calendar c = Calendar.getInstance();

            new DatePickerDialog(
                    this,
                    (view, year, month, dayOfMonth) -> {

                        selectedDeadline =
                                dayOfMonth + "/" +
                                        (month + 1) + "/" +
                                        year;

                        btnDeadline.setText(selectedDeadline);

                    },
                    c.get(Calendar.YEAR),
                    c.get(Calendar.MONTH),
                    c.get(Calendar.DAY_OF_MONTH))
                    .show();
        });

        btnCreate.setOnClickListener(v -> {

            String result =
                    "Project Created\n\n" +
                            "Title: " + etTitle.getText().toString() + "\n" +
                            "Group Size: " + spGroupSize.getSelectedItem() + "\n" +
                            "Deadline: " + selectedDeadline + "\n" +
                            "Max Score: " + etScore.getText().toString();

            tvOutput.setText(result);
        });

        btnStudentPage.setOnClickListener(v ->
                Toast.makeText(
                        this,
                        "Student Page Clicked",
                        Toast.LENGTH_SHORT
                ).show());
    }
}