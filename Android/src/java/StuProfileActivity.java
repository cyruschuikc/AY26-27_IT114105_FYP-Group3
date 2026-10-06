package com.example.teambox_sk1;

import android.os.Bundle;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.TextView;
import android.widget.Toast;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

public class StuProfileActivity extends AppCompatActivity {
    TextView tvSNAME, SID, gender, tvLv, college, course, tvClass;
    private ImageView image;
    private String stu_name = "";
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_stu_profile);

        tvSNAME = findViewById(R.id.tvStuName);
        SID = findViewById(R.id.tvSID);
        gender = findViewById(R.id.tvGender);
        tvLv = findViewById(R.id.tvGradeLv);
        college = findViewById(R.id.tvSchool);
        course = findViewById(R.id.tvCourse);
        tvClass = findViewById(R.id.tvClass);
        image = findViewById(R.id.imageView);
        if (getIntent() != null && getIntent().hasExtra("USERNAME")) {
            stu_name = getIntent().getStringExtra("USERNAME");
            tvSNAME.setText("Student Name: " + stu_name); // Clean explicit assignment
            if (stu_name.equalsIgnoreCase("Cyrus")) {
                SID.setText("SID: 250193995");
                gender.setText("Gender: Male");
                tvLv.setText("Grade Level: Higher Diploma (Y2)");
                college.setText("College: HKIIT");
                course.setText("Programme: HD in Software Engineering");
                tvClass.setText("Class: 2B");
            } else if (stu_name.equalsIgnoreCase("Savio")) {
                SID.setText("SID: 250350618");
                gender.setText("Gender: Male");
                tvLv.setText("Grade Level: Higher Diploma (Y2)");
                college.setText("College: HKIIT");
                course.setText("Programme: HD in Software Engineering");
                tvClass.setText("Class: 2B");
            } else if (stu_name.equalsIgnoreCase("Thomas")) {
                SID.setText("SID: 250014543");
                gender.setText("Gender: Male");
                tvLv.setText("Grade Level: Higher Diploma (Y2)");
                college.setText("College: HKIIT");
                course.setText("Programme: HD in Software Engineering");
                tvClass.setText("Class: 2B");
            } else {
                SID.setText("SID: 250025281");
                gender.setText("Gender: Female");
                tvLv.setText("Grade Level: Higher Diploma (Y2)");
                college.setText("College: HKIIT");
                course.setText("Programme: HD in Software Engineering");
                tvClass.setText("Class: 2B");
            }
        }

        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });

        Button btBack = findViewById(R.id.btBack);
        btBack.setOnClickListener(v -> finish()); // Closes profile and goes back to previous screen
    }
}
