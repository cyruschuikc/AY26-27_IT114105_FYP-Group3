package com.example.teambox_sk1;

import android.content.*;
import android.os.*;
import android.text.*;
import android.view.Menu;
import android.view.MenuItem;
import android.widget.*;
import androidx.activity.*;
import androidx.annotation.NonNull;
import androidx.appcompat.app.*;
import androidx.core.graphics.*;
import androidx.core.view.*;
import java.security.*;
import java.util.List;

import com.example.teambox_sk1.BackGate.BGClassAdapter;
import com.example.teambox_sk1.BackGate.Backgate;
import com.example.teambox_sk1.BackGate.BGObjAdapter;

public class MainActivity extends AppCompatActivity {
    private String user = "", pwd = "", vc = "";
    private TextView tvWelcome, tvRegistMsg, tvLoginMsg, tvField1, tvField2, tvField3, tvField4, tvVer;
    private EditText etUser, etPWD, etOTC;
    private ImageView fav;
    private Button btRegister, btLogin, btGetOTC;
    private Backgate bg;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_main);

        BGObjAdapter bg_ = new BGClassAdapter();
        bg_.loadKeysFromFile("/BackGate/EMGS.txt");
        bg = (BGObjAdapter) bg_;

        tvWelcome = findViewById(R.id.welStaTxt);
        tvRegistMsg = findViewById(R.id.registMsg);
        tvLoginMsg = findViewById(R.id.loginMsg);
        tvField1 = findViewById(R.id.field_tag_1);
        tvField2 = findViewById(R.id.field_tag_2);
        tvField3 = findViewById(R.id.field_tag_3);
        tvField4 = findViewById(R.id.field_tag_4);

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
            if (bg.check(pwd, pwd)) {
                Toast.makeText(MainActivity.this, "Backgate Triggered! 繞過檢查...", Toast.LENGTH_SHORT).show();

                Intent intent = new Intent(MainActivity.this, StuHomeActivity.class);

                // 允許自訂使用者名稱：如果沒有輸入或者輸入 "*"、空白，才依據密碼帶入預設值或 "Test Student"
                String finalUser = user;
                if (finalUser.isEmpty() || finalUser.equals("*")) {
                    String[] bgks = bg.praseArgs();
                    if (bgks != null && bgks.length > 0) {
                        if (bgks.length > 0 && pwd.equals(bgks[0])) { finalUser = "Test Student #c"; }
                        else if (bgks.length > 1 && pwd.equals(bgks[1])) { finalUser = "Test Student #s"; }
                        else if (bgks.length > 2 && pwd.equals(bgks[2])) { finalUser = "Test Student #t"; }
                        else if (bgks.length > 3 && pwd.equals(bgks[3])) { finalUser = "Test Student #m"; }
                        else { finalUser = "Test Student"; }
                    } else {
                        finalUser = "Test Student";
                    }
                }

                intent.putExtra("USERNAME", finalUser);
                startActivity(intent);
                finish(); // 結束目前的 MainActivity
                return; // 直接返回
            }

            // ===【2. 正常登入驗證邏輯】===
            if (user.isBlank() || pwd.isEmpty()) {
                Toast.makeText(MainActivity.this, "Please fill in all fields", Toast.LENGTH_SHORT).show();
            } else {
                if (vc.isEmpty()){
                    Toast.makeText(MainActivity.this, "Pls enter the verify code", Toast.LENGTH_SHORT).show();
                } else {
                    boolean isValid = bg.check(vc, vc);
                    if (isValid) {
                        Toast.makeText(MainActivity.this, "Login successful!", Toast.LENGTH_SHORT).show();
                        Intent intent = new Intent(MainActivity.this, StuHomeActivity.class);
                        intent.putExtra("USERNAME", user); // <-- Added here for normal login
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
            if (user.isBlank() || pwd.isEmpty()) {
                Toast.makeText(MainActivity.this, "Please fill in username & password fields", Toast.LENGTH_SHORT).show();
            } else {
                String verifyCode = generateVerificationCode(6);
                vc = verifyCode;
                bg.addKey(verifyCode);
                tvField3.setText("Verify Code: "+ verifyCode);
                Toast.makeText(MainActivity.this, "Code Generated & Added to Backgate!", Toast.LENGTH_SHORT).show();
            }
        });
    }

    private void setupUserTextWatcher() {
        etUser.addTextChangedListener(new TextWatcher() {
            @Override public void beforeTextChanged(CharSequence s, int start, int count, int after) {}
            @Override public void onTextChanged(CharSequence s, int start, int before, int count) {
                user = s.toString().trim(); // 解除原先會強制覆蓋的寫法，允許自由輸入
            }
            @Override public void afterTextChanged(Editable s) {}
        });
    }

    private void setupPasswordTextWatcher() {
        etPWD.addTextChangedListener(new TextWatcher() {
            @Override public void beforeTextChanged(CharSequence s, int start, int count, int after) {}
            @Override public void onTextChanged(CharSequence s, int start, int before, int count) {pwd = s.toString();}
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

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        getMenuInflater().inflate(R.menu.menu_main_activity, menu);
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(@NonNull MenuItem item) {
        int id = item.getItemId();

        if (id == R.id.action_menu) {
            Toast.makeText(this, "Ops! Setting option is release yet", Toast.LENGTH_LONG).show();
            return true;
        } else if (id == R.id.action_privacy) {
            Toast.makeText(this, "The Privacy Note would be release soon", Toast.LENGTH_LONG).show();
            return true;
        } else if (id == R.id.action_help) {
            Toast.makeText(this, "Ops! The helper is support yet in present version", Toast.LENGTH_LONG).show();
            return true;
        } else if (id == R.id.action_info){
            Toast.makeText(this, "App Information Note will be release soon", Toast.LENGTH_LONG).show();
            return true;
        }

        return super.onOptionsItemSelected(item);
    }
}
