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
    TextView tvSNAME, SID;
    private ImageView image;
    private String stu_name = "";
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_stu_profile);

        tvSNAME = findViewById(R.id.tvStuName);
        SID = findViewById(R.id.tvSID);
        image = findViewById(R.id.imageView);
        if (getIntent() != null && getIntent().hasExtra("USERNAME")) {
            stu_name = getIntent().getStringExtra("USERNAME");
            tvSNAME.setText(tvSNAME.getText()+stu_name);
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
