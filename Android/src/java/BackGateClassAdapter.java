package com.example.teambox_sk1;

import android.util.Log;
import android.view.View;
import android.widget.ProgressBar;

/**
 * A concrete Class Adapter that implements BackGate.
 * This acts as a default handler for your gate events and provides hooks for UI updates.
 */
public class BackGateClassAdapter implements BackGate {
    private static final String TAG = "BackGateClassAdapter";
    private ProgressBar progressBar;
    private GateEventListener listener;

    /**
     * Interface to communicate gate events back to activities or fragments safely.
     */
    public interface GateEventListener {
        void onDataProcessed(String parsedData);
        void onGateFailure(String error);
    }

    // Constructors
    public BackGateClassAdapter(ProgressBar progressBar, GateEventListener listener) {
        this.progressBar = progressBar;
        this.listener = listener;
    }

    @Override
    public void onGateOpen() {
        Log.d(TAG, "Gate opened successfully.");
        handleGateOpen();
    }

    @Override
    public void onDataReceived(String data) {
        Log.d(TAG, "Data arrived at class adapter: " + data);
        processData(data);
    }

    @Override
    public void onError(String errorMessage) {
        Log.e(TAG, "Error caught in adapter: " + errorMessage);
        logErrorToSystem(errorMessage);
    }

    @Override
    public void onGateClose() {
        Log.d(TAG, "Gate closed safely.");
        handleGateClose();
    }

    // --- Core Implementation Logic & Hook Methods ---

    protected void handleGateOpen() {
        // Show a loading spinner on the UI thread if present
        if (progressBar != null) {
            progressBar.post(() -> progressBar.setVisibility(View.VISIBLE));
        }
    }

    protected void processData(String data) {
        if (data == null || data.trim().isEmpty()) {
            onError("Received empty or null data payload.");
            return;
        }

        // Example: Base logic for data caching or formatting
        String sanitizedData = data.trim();

        // Notify the UI layer listener if assigned
        if (listener != null) {
            listener.onDataProcessed(sanitizedData);
        }
    }

    protected void logErrorToSystem(String error) {
        // Fallback safety check for null messages
        String finalError = (error != null) ? error : "Unknown execution error";

        if (listener != null) {
            listener.onGateFailure(finalError);
        }
        // Ensure progress bar hides even during failures
        handleGateClose();
    }

    protected void handleGateClose() {
        // Hide the loading spinner on the UI thread if present
        if (progressBar != null) {
            progressBar.post(() -> progressBar.setVisibility(View.GONE));
        }
    }
}
