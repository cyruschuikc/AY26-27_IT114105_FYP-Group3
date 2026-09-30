package com.example.teambox_sk1;

import android.os.*;
import android.widget.*;
import androidx.activity.*;
import androidx.appcompat.app.*;
import androidx.core.graphics.*;
import androidx.core.view.*;

public class MainActivity extends AppCompatActivity {
    private TextView tvWelcome, tvRegistMsg, tvLoginMsg, tvField1, tvField2, tvVer;
    private EditText etUser, etPWD;
    private ImageView fav;
    private Button btRegister, btLogin;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_main);

        tvWelcome = findViewById(R.id.welStaTxt);
        tvRegistMsg = findViewById(R.id.registMsg);
        tvLoginMsg = findViewById(R.id.loginMsg);
        tvField1 = findViewById(R.id.field_tag_1);
        tvField2 = findViewById(R.id.field_tag_2);
        tvVer = findViewById(R.id.verTag);

        etUser = findViewById(R.id.uname);
        etPWD = findViewById(R.id.passwd);

        fav = findViewById(R.id.AppIcon);

        btRegister = findViewById(R.id.registBtn);
        btLogin = findViewById(R.id.btLogin);

        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
                    Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
                    v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
                    return insets;});
    }

    @Override
    protected void onStart() {
        super.onStart();
    }

    @Override
    protected void onPause() {
        super.onPause();
    }

    @Override
    protected void onResume() {
        super.onResume();
    }

    @Override
    protected void onStop() {
        super.onStop();
    }

    @Override
    protected void onRestart() {
        super.onRestart();
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
    }
}
