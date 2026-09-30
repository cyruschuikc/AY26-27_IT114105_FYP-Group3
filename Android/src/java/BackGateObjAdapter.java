package com.example.teambox_sk1;

/**
 * Abstract adapter class that provides empty implementations of the BackGate interface.
 * Subclasses can override only the methods they need.
 */
public abstract class BackGateObjAdapter implements BackGate {

    @Override
    public void onGateOpen() {
        // Default empty implementation - override if needed
    }

    @Override
    public void onDataReceived(String data) {
        // Default empty implementation - override if needed
    }

    @Override
    public void onError(String errorMessage) {
        // Default empty implementation - override if needed
    }

    @Override
    public void onGateClose() {
        // Default empty implementation - override if needed
    }
}
