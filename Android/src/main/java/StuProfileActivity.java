package com.example.teambox_sk2;

import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.LinearLayout;
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

public class StuProfileActivity extends AppCompatActivity {

    private TextView tvStuName, tvSID, tvGender, tvGradeLv, tvSchool, tvCourse, tvClass, tvBioContent;
    private ImageView imageView;
    private Button btEdit, btBack;
    private LinearLayout layoutServicesContainer, layoutAwardsContainer, layoutActivitiesContainer;

    private fyp03DB databaseAccessor;
    private String currentUsername = "";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        // Initialize SQLite helper context
        fyp03Adaptee.initializeContext(this);

        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_stu_profile);

        // Handle window system bars insets safely
        View rootView = findViewById(R.id.main);
        if (rootView != null) {
            ViewCompat.setOnApplyWindowInsetsListener(rootView, (v, insets) -> {
                Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
                v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
                return insets;
            });
        }

        extractIntentData();
        initViews();
        initDatabase();
        loadProfileData();
        setupListeners();
    }

    private void extractIntentData() {
        if (getIntent() != null) {
            currentUsername = getIntent().getStringExtra("EXTRA_USERNAME");
        }
        if (currentUsername == null || currentUsername.isEmpty()) {
            currentUsername = "CHUI Ka Chun"; // Default fallback matching user profile
        }
    }

    private void initViews() {
        tvStuName = findViewById(R.id.tvStuName);
        tvSID = findViewById(R.id.tvSID);
        tvGender = findViewById(R.id.tvGender);
        tvGradeLv = findViewById(R.id.tvGradeLv);
        tvSchool = findViewById(R.id.tvSchool);
        tvCourse = findViewById(R.id.tvCourse);
        tvClass = findViewById(R.id.tvClass);
        tvBioContent = findViewById(R.id.tvBioContent);

        imageView = findViewById(R.id.imageView);
        btEdit = findViewById(R.id.btEdit);
        btBack = findViewById(R.id.btBack);

        layoutServicesContainer = findViewById(R.id.layoutServicesContainer);
        layoutAwardsContainer = findViewById(R.id.layoutAwardsContainer);
        layoutActivitiesContainer = findViewById(R.id.layoutActivitiesContainer);
    }

    private void initDatabase() {
        databaseAccessor = fyp03Factory.getDatabaseAdapter("OBJECT");
        if (databaseAccessor != null) {
            databaseAccessor.readData("teambox.sql");
        }
    }

    private void loadProfileData() {
        try {
            // Query user data from the database accessor
            String userData = databaseAccessor != null ? databaseAccessor.getData(currentUsername) : null;

            if (userData != null && !userData.contains("No matching records")) {
                tvStuName.setText("Student Name: " + currentUsername);
                tvSID.setText("SID: S15942736"); // Seed record mapping
                tvGender.setText("Gender: Male");
                tvGradeLv.setText("Grade Level: Year 2 (Higher Diploma)");
                tvSchool.setText("College: Hong Kong Institute of Information Technology (VTC)");
                tvCourse.setText("Programme: Higher Diploma in Information Technology (Software Engineering)");
                tvClass.setText("Class: IT114105 / Software Engineering Stream");
                tvBioContent.setText("Second-year Software Engineering student, passionate about full-stack mobile & web development, Java/Kotlin, PHP, MySQL, and Venture Scout leadership activities.");
            } else {
                // Fallback display values
                tvStuName.setText("Student Name: " + currentUsername);
                tvSID.setText("SID: S15942736");
                tvGender.setText("Gender: Male");
                tvGradeLv.setText("Grade Level: Year 2");
                tvSchool.setText("College: VTC / HKIIT");
                tvCourse.setText("Programme: Software Engineering");
                tvClass.setText("Class: IT114105");
                tvBioContent.setText("Passionate student developer and active youth scout leader building collaborative cross-platform applications.");
            }

            // Populate dynamic section card items
            addDynamicItem(layoutServicesContainer, "• Full-Stack Android & Web App Development (Java/Kotlin, PHP, MySQL)");
            addDynamicItem(layoutServicesContainer, "• Database Design & System Architecture");

            addDynamicItem(layoutAwardsContainer, "• Dean's List / Academic Excellence Commendation");
            addDynamicItem(layoutAwardsContainer, "• Outstanding Venture Scout Leadership Award");

            addDynamicItem(layoutActivitiesContainer, "• Final Year Project: TeamBox Collaboration System Lead");
            addDynamicItem(layoutActivitiesContainer, "• Venture Scout Cheung Chau Camping Trip Organizer & PIC");

        } catch (Exception e) {
            Toast.makeText(this, "Error loading profile: " + e.getMessage(), Toast.LENGTH_SHORT).show();
        }
    }

    private void addDynamicItem(LinearLayout container, String text) {
        TextView tvItem = new TextView(this);
        tvItem.setText(text);
        tvItem.setTextSize(14);
        tvItem.setTextColor(0xFF2C2F36);
        tvItem.setPadding(0, 6, 0, 6);
        container.addView(tvItem);
    }

    private void setupListeners() {
        if (btBack != null) {
            btBack.setOnClickListener(v -> finish());
        }

        if (btEdit != null) {
            btEdit.setOnClickListener(v -> Toast.makeText(this, "Edit profile functionality coming soon.", Toast.LENGTH_SHORT).show());
        }
    }
}
