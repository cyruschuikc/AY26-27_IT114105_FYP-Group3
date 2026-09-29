package com.example.teambox_sk1;

import android.os.*;
import android.widget.*;
import androidx.activity.*;
import androidx.appcompat.app.*;
import androidx.core.graphics.*;
import androidx.core.view.*;

public class MainActivity extends AppCompatActivity {
    private ScrollView sv_main;
    private LinearLayout LLs_main;
    private TextView tvWelcome, tvRegistMsg, tvLoginMsg, tvField1, tvField2, tvVer;
    private EditText etUser, etPWD;
    private ImageView fav;
    private Button btRegister, btLogin;

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
