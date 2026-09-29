package com.example.teambox_sk1;

import android.os.*;
import android.util.*;
import android.view.*;
import android.widget.*;
import androidx.activity.*;
import androidx.appcompat.app.*;
import androidx.core.graphics.*;
import androidx.core.view.*;

public class MainActivity extends AppCompatActivity {
    private static final String TAG = "DomainDebug";
    private Spinner spIdentity;
    private TextView registMsg;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_main);
        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
                    Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
                    v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
                    return insets;});
    }
}
