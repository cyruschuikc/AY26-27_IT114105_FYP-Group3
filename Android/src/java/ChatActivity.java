package com.example.teambox_sk1;

import android.os.Bundle;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ListView;
import android.widget.TextView;
import android.widget.Toast;
import androidx.appcompat.app.AppCompatActivity;
import java.util.ArrayList;

public class ChatActivity extends AppCompatActivity {

    private Button btBack, btnSend;
    private TextView tvTitle;
    private ListView chatListView;
    private EditText etMessageInput;

    private ArrayList<String> messageList;
    private ArrayAdapter<String> adapter;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_chat);

        // Initialize UI components matching XML IDs
        btBack = findViewById(R.id.btBack);
        tvTitle = findViewById(R.id.tvTitle);
        chatListView = findViewById(R.id.lvChatMessages);
        etMessageInput = findViewById(R.id.etMessageInput);
        btnSend = findViewById(R.id.btnSendMessage);

        tvTitle.setText("Group Chat");

        // Initialize sample chat messages
        messageList = new ArrayList<>();
        messageList.add("Cyrus: Let's start working on the UI wireframes.");
        messageList.add("Savio: Sounds good! I'll handle the database layout.");
        messageList.add("Thomas: Got it, I'll prepare the initial project slides.");

        // Set up the adapter for the chat ListView
        adapter = new ArrayAdapter<>(
                this,
                android.R.layout.simple_list_item_1,
                messageList
        );
        chatListView.setAdapter(adapter);

        // Handle Send Button Click
        btnSend.setOnClickListener(v -> {
            String newMessage = etMessageInput.getText().toString().trim();
            if (!newMessage.isEmpty()) {
                messageList.add("You: " + newMessage);
                adapter.notifyDataSetChanged();
                chatListView.smoothScrollToPosition(messageList.size() - 1);
                etMessageInput.setText("");
            } else {
                Toast.makeText(ChatActivity.this, "Please enter a message", Toast.LENGTH_SHORT).show();
            }
        });

        // Handle Back button action
        btBack.setOnClickListener(v -> finish());
    }
}
