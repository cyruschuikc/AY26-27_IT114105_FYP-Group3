package com.example.teambox_sk1;

public interface BackGate {
    void onGateOpen();                    // Called when a background or gate process starts
    void onDataReceived(String data);    // Called to pass data back (e.g., text from a database or background task)
    void onError(String errorMessage);   // Called if an error occurs during the operation
    void onGateClose();                  // Called when the process completes and the gate closes
}
