package com.example.teambox_sk2;

import android.content.Intent;
import android.os.Bundle;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.ListView;
import android.widget.Toast;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

import com.example.teambox_sk2.dbDefinder.fyp03DB;
import com.example.teambox_sk2.dbDefinder.fyp03Factory;

import java.util.ArrayList;
import java.util.Arrays;

public class TeamActivity extends AppCompatActivity {

    private Button btBack, btNewTeam;
    private ListView lvTeams;
    private fyp03DB databaseAccessor;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_team);

        // Apply system bar insets for edge-to-edge support
        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });

        // Initialize UI components
        initViews();

        // Initialize database accessor using dbDefinder pattern
        initDatabase();

        // Load team members and user roles into the ListView
        loadTeamData();

        // Setup button click listeners
        setupListeners();
    }

    private void initViews() {
        btBack = findViewById(R.id.btBack);
        btNewTeam = findViewById(R.id.btNewTeam);
        lvTeams = findViewById(R.id.lvTeams);
    }

    private void initDatabase() {
        databaseAccessor = fyp03Factory.getDatabaseAdapter("OBJECT");
    }

    private void loadTeamData() {
        // Query users table from database for team members (e.g., student and teacher roles)
        String teamResult = databaseAccessor.getData("student");

        ArrayList<String> teamList = new ArrayList<>();
        if (teamResult != null && !teamResult.contains("No matching records found")) {
            String[] lines = teamResult.split("\n");
            teamList.addAll(Arrays.asList(lines));
        } else {
            // Fallback seed items based on project context
            teamList.add("User: CHAN Ho Yin | Email: 260222111@stu.vtc.edu.hk | Role: student");
            teamList.add("User: CHUI Ka Chun | Email: 260222112@stu.vtc.edu.hk | Role: student");
            teamList.add("User: Esther Yuen | Email: wyyuen@vtc.edu.hk | Role: teacher");
        }

        // Bind data to ListView using an ArrayAdapter
        ArrayAdapter<String> adapter = new ArrayAdapter<>(
                this,
                android.R.layout.simple_list_item_1,
                teamList
        );
        lvTeams.setAdapter(adapter);
    }

    private void setupListeners() {
        // Back Button: Return to Student Home Activity
        btBack.setOnClickListener(v -> {
            Intent intent = new Intent(TeamActivity.this, StuHomeActivity.class);
            startActivity(intent);
            finish();
        });

        // + New Team Member Button Action
        btNewTeam.setOnClickListener(v -> {
            Toast.makeText(TeamActivity.this, "Add New Team Member clicked", Toast.LENGTH_SHORT).show();
            Intent intent = new Intent(TeamActivity.this, MemberInviteActivity.class);
            startActivity(intent);
            finish();
        });

        // ListView Item Click Listener
        lvTeams.setOnItemClickListener((parent, view, position, id) -> {
            String selectedMember = (String) parent.getItemAtPosition(position);
            Toast.makeText(TeamActivity.this, "Selected Member: " + selectedMember, Toast.LENGTH_SHORT).show();
        });
    }
}
