package com.example.teambox_sk1;

public interface BackGate {
    void onGateOpen();
    void onDataReceived(String data);
    void onError(String errorMessage);
    void onGateClose();
}
