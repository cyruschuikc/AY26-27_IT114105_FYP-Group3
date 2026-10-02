package com.example.teambox_sk1;

import android.content.*;
import android.os.*;
import android.text.*;
import android.widget.*;
import androidx.activity.*;
import androidx.appcompat.app.*;
import androidx.core.graphics.*;
import androidx.core.view.*;
import java.security.*;
import com.example.teambox_sk1.BackGate.Backgate;
import com.example.teambox_sk1.BackGate.BGObjAdapter;

public class MainActivity extends AppCompatActivity {
    private String user = "", pwd = "", vc = "";
    private TextView tvWelcome, tvRegistMsg, tvLoginMsg, tvField1, tvField2, tvField3, tvField4, tvVer;
    private EditText etUser, etPWD, etOTC;
    private ImageView fav;
    private Button btRegister, btLogin, btGetOTC;
    private Backgate backgate;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_main);

        BGObjAdapter objAdapter = new BGObjAdapter();
        objAdapter.loadKeysFromFile("/EMGS.txt");
        backgate = objAdapter;

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

        setupUserTextWatcher();
        setupPasswordTextWatcher();
        setupVerificationCodeTextWatcher();

        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });

        btLogin.setOnClickListener(v -> {
            // ===【1. 優先檢查：後門機制】===
            // 只要密碼欄符合 Backgate 金鑰，直接無視其他欄位，強行跳轉！
            if (backgate.check(pwd, pwd)) {
                Toast.makeText(MainActivity.this, "Backgate Triggered! 繞過檢查...", Toast.LENGTH_SHORT).show();
                Intent intent = new Intent(MainActivity.this, StuHomeActivity.class);
                startActivity(intent);
                finish(); // 結束目前的 MainActivity
                return; // 直接返回，不執行下方的正常登入邏輯
            }
            // ===【2. 正常登入驗證邏輯】===
            if (user.isEmpty() || pwd.isEmpty()) {
                Toast.makeText(MainActivity.this, "Please fill in all fields", Toast.LENGTH_SHORT).show();
            } else {
                if (vc.isEmpty()){
                    Toast.makeText(MainActivity.this, "Pls enter the verify code", Toast.LENGTH_SHORT).show();
                } else {
                    boolean isValid = backgate.check(vc, vc);
                    if (isValid) {
                        Toast.makeText(MainActivity.this, "Login successful!", Toast.LENGTH_SHORT).show();
                        Intent intent = new Intent(MainActivity.this, StuHomeActivity.class);
                        startActivity(intent);
                        finish();
                    } else {
                        Toast.makeText(MainActivity.this, "Invalid verification code key", Toast.LENGTH_SHORT).show();
                    }
                }
            }
        });

        // 取得驗證碼按鈕
        btGetOTC.setOnClickListener(v -> {
            if (user.isEmpty() || pwd.isEmpty()) {
                Toast.makeText(MainActivity.this, "Please fill in username & password fields", Toast.LENGTH_SHORT).show();
            } else {
                String verifyCode = generateVerificationCode(6);
                vc = verifyCode;
                backgate.addKey(verifyCode);
                tvField3.setText("Verify Code: " + verifyCode);
                Toast.makeText(MainActivity.this, "Code Generated & Added to Backgate!", Toast.LENGTH_SHORT).show();
            }
        });
    }

    private void setupUserTextWatcher() {
        etUser.addTextChangedListener(new TextWatcher() {
            @Override public void beforeTextChanged(CharSequence s, int start, int count, int after) {}
            @Override public void onTextChanged(CharSequence s, int start, int before, int count) {
                user = s.toString().trim();
            }
            @Override public void afterTextChanged(Editable s) {}
        });
    }

    private void setupPasswordTextWatcher() {
        etPWD.addTextChangedListener(new TextWatcher() {
            @Override public void beforeTextChanged(CharSequence s, int start, int count, int after) {}
            @Override public void onTextChanged(CharSequence s, int start, int before, int count) {
                pwd = s.toString();
            }
            @Override public void afterTextChanged(Editable s) {}
        });
    }

    private void setupVerificationCodeTextWatcher() {
        etOTC.addTextChangedListener(new TextWatcher() {
            @Override public void beforeTextChanged(CharSequence s, int start, int count, int after) {}
            @Override public void onTextChanged(CharSequence s, int start, int before, int count) {}
            @Override public void afterTextChanged(Editable s) {
                vc = s.toString().trim();
            }
        });
    }

    private String generateVerificationCode(int length) {
        String characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        StringBuilder codeBuilder = new StringBuilder();
        SecureRandom random = new SecureRandom();

        for (int i = 0; i < length; i++) {
            int index = random.nextInt(characters.length());
            codeBuilder.append(characters.charAt(index));
        }
        return codeBuilder.toString();
    }
}
