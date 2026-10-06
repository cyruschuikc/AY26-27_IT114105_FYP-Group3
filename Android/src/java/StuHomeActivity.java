package com.example.teambox_sk1;

import android.content.DialogInterface;
import android.content.Intent;
import android.os.Bundle;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.widget.ImageButton;
import android.widget.TextView;
import android.widget.Toast;

import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;
import androidx.appcompat.app.AlertDialog;

public class StuHomeActivity extends AppCompatActivity {
    TextView tvSWBM;
    ImageButton ibViewProjects, ibViewTasks, ibViewTeams, ibSD, ibChat;
    private String username = ""; // Store username

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_stu_home);

        // Initialize UI components
        tvSWBM = findViewById(R.id.wel_back_msg);
        ibViewProjects = findViewById(R.id.ibViewProjects);
        ibViewTasks = findViewById(R.id.ibViewTasks);
        ibViewTeams = findViewById(R.id.ibViewTeams);
        ibSD = findViewById(R.id.ibSD);
        ibChat = findViewById(R.id.ibChat);

        // Get username from Intent
        if (getIntent() != null && getIntent().hasExtra("USERNAME")) {
            username = getIntent().getStringExtra("USERNAME");
            tvSWBM.setText("Welcome Back, " + username);
        }

        // Set up Quick Access Button Listeners
        ibViewProjects.setOnClickListener(v -> {
            Intent intent = new Intent(StuHomeActivity.this, StuProjectsActivity.class);
            startActivity(intent);
        });

        ibViewTasks.setOnClickListener(v -> {
            Intent intent = new Intent(StuHomeActivity.this, StuTasksActivity.class);
            startActivity(intent);
        });

        ibViewTeams.setOnClickListener(v -> {
            Intent intent = new Intent(StuHomeActivity.this, StuTeamsActivity.class);
            startActivity(intent);
        });

        ibSD.setOnClickListener(v -> {
            Intent intent = new Intent(StuHomeActivity.this, FilesActivity.class);
            startActivity(intent);
        });

        ibChat.setOnClickListener(v -> {
            Intent intent = new Intent(StuHomeActivity.this, ChatActivity.class);
            startActivity(intent);
        });

        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        getMenuInflater().inflate(R.menu.menu_stu_home_activity, menu);
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(@NonNull MenuItem item) {
        int id = item.getItemId();

        if (id == R.id.action_notify) {
            Intent intent = new Intent(StuHomeActivity.this, NotifyActivity.class);
            startActivity(intent);
            return true;
        } else if (id == R.id.action_profile) {
            Intent intent = new Intent(StuHomeActivity.this, StuProfileActivity.class);
            intent.putExtra("USERNAME", username);
            startActivity(intent);
            return true;
        } else if (id == R.id.action_logout) {
            showConfirmationDialog();
            return true;
        } else if (id == R.id.action_info){
            Toast.makeText(this, "App Information Note will be release soon", Toast.LENGTH_LONG).show();
            return true;
        } else if (id == R.id.action_projects) {
            Intent intent = new Intent(StuHomeActivity.this, StuProjectsActivity.class);
            startActivity(intent);
            return true;
        }
        return super.onOptionsItemSelected(item);
    }

    private void showConfirmationDialog(){
        AlertDialog.Builder alert = new AlertDialog.Builder(this);
        alert.setTitle("Logout Confirmation");
        alert.setMessage("Are you want to sign out?");
        alert.setPositiveButton("Sign Out", (dialog, which) -> {
            Toast.makeText(StuHomeActivity.this, "Your account is logged out", Toast.LENGTH_SHORT).show();
            Intent intent = new Intent(StuHomeActivity.this, MainActivity.class);
            startActivity(intent);
            finish();
        });
        alert.setNegativeButton("Cancel", (dialog, which) -> dialog.dismiss());
        AlertDialog dialog = alert.create();
        dialog.show();
    }
}
