package com.example.teambox_sk1;

import android.os.Bundle;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.ListView;
import android.widget.TextView;
import android.widget.Toast;
import androidx.appcompat.app.AppCompatActivity;

public class StuTeamsActivity extends AppCompatActivity {

    private Button btBack;
    private TextView tvTitle;
    private ListView teamList;

    // Sample data for team members
    private String[] sampleTeamMembers = {
            "Thomas (Team Leader + Wep Platform Developer + DB) - Software Engineering",
            "Cyrus (Android App Developer + DB) - Software Engineering",
            "Savio (Android App Developer + DB) - Software Engineering",
            "Miao Tung (Wep Platform Developer + DB) - Software Engineering"
    };

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_stu_teams);

        // Initialize UI components matching XML IDs
        btBack = findViewById(R.id.btBack);
        tvTitle = findViewById(R.id.tvTitle);
        teamList = findViewById(R.id.lvTeams);

        tvTitle.setText("My Team");

        // Set up an adapter for the ListView
        ArrayAdapter<String> adapter = new ArrayAdapter<>(
                this,
                android.R.layout.simple_list_item_1,
                sampleTeamMembers
        );
        teamList.setAdapter(adapter);

        // Handle item selection in the ListView
        teamList.setOnItemClickListener((parent, view, position, id) -> {
            String selectedMember = sampleTeamMembers[position];
            Toast.makeText(StuTeamsActivity.this, "Member: " + selectedMember, Toast.LENGTH_SHORT).show();
        });

        // Handle Back button action
        btBack.setOnClickListener(v -> finish());
    }
}
