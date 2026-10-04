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
            tvSNAME.setText(tvSNAME.getText()+stu_name);
            if (stu_name.equals("Cyrus")||stu_name.equals("cyrus")){
                SID.setText(SID.getText()+"250193995");
                gender.setText(gender.getText()+"Male");
                tvLv.setText(tvLv.getText()+"Higher Diploma (Y2)");
                college.setText(college.getText()+"HKIIT");
                course.setText(course.getText()+"HD in Software Engineering");
                tvClass.setText(tvClass.getText()+"2B");
            }
            else if (stu_name.equals("Savio")||stu_name.equals("savio")) {
                SID.setText(SID.getText()+"250350618");
                gender.setText(gender.getText()+"Male");
                tvLv.setText(tvLv.getText()+"Higher Diploma (Y2)");
                college.setText(college.getText()+"HKIIT");
                course.setText(course.getText()+"HD in Software Engineering");
                tvClass.setText(tvClass.getText()+"2B");
            }
            else if (stu_name.equals("Thomas")||stu_name.equals("thomas")) {
                SID.setText(SID.getText()+"250014543");
                gender.setText(gender.getText()+"Male");
                tvLv.setText(tvLv.getText()+"Higher Diploma (Y2)");
                college.setText(college.getText()+"HKIIT");
                course.setText(course.getText()+"HD in Software Engineering");
                tvClass.setText(tvClass.getText()+"2B");
            }
            else {
                SID.setText(SID.getText()+"250025281");
                gender.setText(gender.getText()+"Female");
                tvLv.setText(tvLv.getText()+"Higher Diploma (Y2)");
                college.setText(college.getText()+"HKIIT");
                course.setText(course.getText()+"HD in Software Engineering");
                tvClass.setText(tvClass.getText()+"2B");
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
