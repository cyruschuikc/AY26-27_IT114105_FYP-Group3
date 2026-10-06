package com.example.teambox_sk1;

import android.os.Bundle;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.ListView;
import android.widget.TextView;
import android.widget.Toast;
import androidx.appcompat.app.AppCompatActivity;

public class FilesActivity extends AppCompatActivity {

    private Button btBack;
    private TextView tvTitle;
    private ListView fileList;

    // Sample data for shared files
    private String[] sampleFiles = {
            "TeamBox_Project_Proposal.pdf",
            "UI_Wireframes_v2.png",
            "Database_Schema_ERD.docx",
            "Source_Code_Archive.zip"
    };

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_files);

        // Initialize UI components matching XML IDs
        btBack = findViewById(R.id.btBack);
        tvTitle = findViewById(R.id.tvTitle);
        fileList = findViewById(R.id.lvFiles);

        tvTitle.setText("Shared Files");

        // Set up an adapter for the ListView
        ArrayAdapter<String> adapter = new ArrayAdapter<>(
                this,
                android.R.layout.simple_list_item_1,
                sampleFiles
        );
        fileList.setAdapter(adapter);

        // Handle item selection in the ListView
        fileList.setOnItemClickListener((parent, view, position, id) -> {
            String selectedFile = sampleFiles[position];
            Toast.makeText(FilesActivity.this, "Opening: " + selectedFile, Toast.LENGTH_SHORT).show();
        });

        // Handle Back button action
        btBack.setOnClickListener(v -> finish());
    }
}
