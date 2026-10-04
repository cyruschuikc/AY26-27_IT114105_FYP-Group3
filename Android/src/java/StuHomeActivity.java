package com.example.teambox_sk1;

import android.content.DialogInterface;
import android.content.Intent;
import android.os.Bundle;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.widget.AdapterView;
import android.widget.ArrayAdapter;
import android.widget.Spinner;
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
    Spinner spinnerQuickAccess;
    private String username = ""; // Store username
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_stu_home);

        tvSWBM = findViewById(R.id.wel_back_msg);
        spinnerQuickAccess = findViewById(R.id.spinner);
        String[] quickAccessOptions = {"Select an action...",
                "View My Project", "View My Tasks", "View My Team",
                "Open Shared Files", "Open Group Chat"
        };

        if (getIntent() != null && getIntent().hasExtra("USERNAME")) {
            username = getIntent().getStringExtra("USERNAME");
            tvSWBM.setText("Welcome Back, " + username);
        }
        ArrayAdapter<String> adapter = new ArrayAdapter<>(this, android.R.layout.simple_spinner_item, quickAccessOptions);
        adapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
        spinnerQuickAccess.setAdapter(adapter);
        spinnerQuickAccess.setOnItemSelectedListener(new AdapterView.OnItemSelectedListener() {
            @Override
            public void onItemSelected(AdapterView<?> parent, View view, int position, long id) {
                // Ignore the placeholder prompt at index 0
                if (position == 0) {
                    return;
                }
                String selectedAction = parent.getItemAtPosition(position).toString();
                switch (selectedAction) {
                    case "View My Project":
                        // TODO: Add Intent to open Project Activity
                        Toast.makeText(StuHomeActivity.this, "Opening My Project...", Toast.LENGTH_SHORT).show();
                        break;

                    case "View My Tasks":
                        // TODO: Add Intent to open Tasks Activity
                        Toast.makeText(StuHomeActivity.this, "Opening My Tasks...", Toast.LENGTH_SHORT).show();
                        break;

                    case "View My Team":
                        // TODO: Add Intent to open Team Activity
                        Toast.makeText(StuHomeActivity.this, "Opening My Team...", Toast.LENGTH_SHORT).show();
                        break;

                    case "Open Shared Files":
                        // TODO: Add Intent to open Files Activity
                        Toast.makeText(StuHomeActivity.this, "Opening Shared Files...", Toast.LENGTH_SHORT).show();
                        break;

                    case "Open Group Chat":
                        // TODO: Add Intent to open Chat Activity
                        Toast.makeText(StuHomeActivity.this, "Opening Group Chat...", Toast.LENGTH_SHORT).show();
                        break;
                }
                spinnerQuickAccess.setSelection(0);
            }
            @Override
            public void onNothingSelected(AdapterView<?> parent) {}
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
            Toast.makeText(this, "Ops! Notification is release yet", Toast.LENGTH_LONG).show();
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
        }

        return super.onOptionsItemSelected(item);
    }

    private void showConfirmationDialog(){
        AlertDialog.Builder alert = new AlertDialog.Builder(this);
        alert.setTitle("Logout Confirmation");
        alert.setMessage("Are you want to sign out?");
        alert.setPositiveButton("Sign Out", new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                Toast.makeText(StuHomeActivity.this, "Your account is logged out", Toast.LENGTH_SHORT).show();
                Intent intent = new Intent(StuHomeActivity.this, MainActivity.class);
                startActivity(intent);
                finish();
            }
        });
        alert.setNegativeButton("Cancel", new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                dialog.dismiss();
            }
        });
        AlertDialog dialog = alert.create();
        dialog.show();
    }
}
