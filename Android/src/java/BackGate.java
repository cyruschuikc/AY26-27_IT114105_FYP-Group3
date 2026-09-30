package com.example.teambox_sk1;

public interface BackGate {

    // Called when a background or gate process starts
    void onGateOpen();

    // Called to pass data back (e.g., text from a database or background task)
    void onDataReceived(String data);

    // Called if an error occurs during the operation
    void onError(String errorMessage);

    // Called when the process completes and the gate closes
    void onGateClose();
}
