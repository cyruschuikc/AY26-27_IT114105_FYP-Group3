package com.example.teambox_sk1;

import android.os.*;
import android.text.Editable;
import android.text.TextWatcher;
import android.widget.*;
import androidx.activity.*;
import androidx.appcompat.app.*;
import androidx.core.graphics.*;
import androidx.core.view.*;
import java.util.Random;

public class MainActivity extends AppCompatActivity {
    private String user = "", pwd = ""; // Initialized to prevent null pointer bugs
    private TextView tvWelcome, tvRegistMsg, tvLoginMsg, tvField1, tvField2, tvField3, tvField4, tvVer;
    private EditText etUser, etPWD, etOTC;
    private ImageView fav;
    private Button btRegister, btLogin, btGetOTC;

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
        tvField3 = findViewById(R.id.field_tag_3);
        tvField4 = findViewById(R.id.field_tag_4);
        tvVer = findViewById(R.id.verTag);

        etUser = findViewById(R.id.uname);
        etPWD = findViewById(R.id.passwd);
        etOTC = findViewById(R.id.OTC);

        fav = findViewById(R.id.AppIcon);

        btRegister = findViewById(R.id.registBtn);
        btLogin = findViewById(R.id.btLogin);
        btGetOTC = findViewById(R.id.btGet);

        // --- Active Real-Time Listeners Connected Here ---
        setupUserTextWatcher();
        setupPasswordTextWatcher();

        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });

        // Triggering the methods when clicking the Login button
        btLogin.setOnClickListener(v -> {
            // Note: currentUsername and currentPassword are no longer needed here
            // because your 'user' and 'pwd' global variables are already completely up-to-date!
            if (user.isEmpty() || pwd.isEmpty()) {
                Toast.makeText(MainActivity.this, "Please fill in all fields", Toast.LENGTH_SHORT).show();
            } else {
                // Proceed with your login logic here using global 'user' and 'pwd'
            }
        });

        // OnClickListener for btGetOTC
        btGetOTC.setOnClickListener(v -> {
            if (user.isEmpty() || pwd.isEmpty()) {
                Toast.makeText(MainActivity.this, "Please fill in username & password fields", Toast.LENGTH_SHORT).show();
            }
            else {
                String verifyCode = generateVerificationCode(6);
                tvField3.setText("Verify Code: " + verifyCode);
                Toast.makeText(MainActivity.this, "Code Generated!", Toast.LENGTH_SHORT).show();
            }
        });
    }

    /**
     * Listens to the username EditText and changes the global variable 'user' instantly.
     */
    private void setupUserTextWatcher() {
        etUser.addTextChangedListener(new TextWatcher() {
            @Override
            public void beforeTextChanged(CharSequence s, int start, int count, int after) {}

            @Override
            public void onTextChanged(CharSequence s, int start, int before, int count) {
                // Instantly changes the value as the user types
                user = s.toString().trim();
            }

            @Override
            public void afterTextChanged(Editable s) {}
        });
    }

    /**
     * Listens to the password EditText and changes the global variable 'pwd' instantly.
     */
    private void setupPasswordTextWatcher() {
        etPWD.addTextChangedListener(new TextWatcher() {
            @Override
            public void beforeTextChanged(CharSequence s, int start, int count, int after) {}

            @Override
            public void onTextChanged(CharSequence s, int start, int before, int count) {
                // Instantly changes the value as the user types (No trim, spaces allowed in password)
                pwd = s.toString();
            }

            @Override
            public void afterTextChanged(Editable s) {}
        });
    }

    /**
     * Generates a random alphanumeric verification code of a specified length.
     */
    private String generateVerificationCode(int length) {
        String characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        StringBuilder codeBuilder = new StringBuilder();
        Random random = new Random();

        for (int i = 0; i < length; i++) {
            int index = random.nextInt(characters.length());
            codeBuilder.append(characters.charAt(index));
        }

        return codeBuilder.toString();
    }

    @Override protected void onStart() { super.onStart(); }
    @Override protected void onPause() { super.onPause(); }
    @Override protected void onResume() { super.onResume(); }
    @Override protected void onStop() { super.onStop(); }
    @Override protected void onRestart() { super.onRestart(); }
    @Override protected void onDestroy() { super.onDestroy(); }
}
