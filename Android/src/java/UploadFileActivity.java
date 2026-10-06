package com.example.teambox_sk1;

import android.os.Bundle;
import android.widget.Button;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;

public class UploadFileActivity extends AppCompatActivity {

    private Button btnChooseFile;
    private Button btnUpload;

    private Button btnBack;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_upload_file);

        btnChooseFile = findViewById(R.id.btnChooseFile);
        btnUpload = findViewById(R.id.btnUpload);

        btnChooseFile.setOnClickListener(v ->
                Toast.makeText(this,
                        "File picker not implemented yet",
                        Toast.LENGTH_SHORT).show());

        btnUpload.setOnClickListener(v ->
                Toast.makeText(this,
                        "Upload feature not implemented yet",
                        Toast.LENGTH_SHORT).show());

        btnBack = findViewById(R.id.btnBack);

        btnBack.setOnClickListener(v -> {
            // Add usage for back button
        });
    }
}
