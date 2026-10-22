package com.example.teambox_sk2;

import android.os.Bundle;
import android.widget.Button;
import android.widget.TextView;

import androidx.appcompat.app.AppCompatActivity;

public class TeacherProfileActivity extends AppCompatActivity {

    private TextView tvTeacherName;
    private TextView tvTID;
    private TextView tvGender;
    private TextView tvDepartment;
    private TextView tvPosition;
    private TextView tvFaculty;
    private TextView tvEmail;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_teacher_profile);

        tvTeacherName = findViewById(R.id.tvTeacherName);
        tvTID = findViewById(R.id.tvTID);
        tvGender = findViewById(R.id.tvGender);
        tvDepartment = findViewById(R.id.tvDepartment);
        tvPosition = findViewById(R.id.tvPosition);
        tvFaculty = findViewById(R.id.tvFaculty);
        tvEmail = findViewById(R.id.tvEmail);

        tvTeacherName.setText("Dr Yuen");
        tvTID.setText("T001");
        tvGender.setText("Female");
        tvDepartment.setText("Software Engineering");
        tvPosition.setText("Senior Lecturer");
        tvFaculty.setText("HKIIT");
        tvEmail.setText("chan@hkiit.edu.hk");

        Button btBack = findViewById(R.id.btBack);
        btBack.setOnClickListener(v -> finish());
    }
}