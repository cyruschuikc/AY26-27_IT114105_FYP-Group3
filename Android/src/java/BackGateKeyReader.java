package com.example.teambox_sk1;

import android.content.Context;
import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.util.ArrayList;
import java.util.List;

public class BackGateKeyReader {

    /**
     * Reads the BackGateKeys file from the Android assets folder.
     * @param context The Android Context
     * @return A List containing all the keys as strings
     */
    public static List<String> readKeys(Context context) {
        List<String> keys = new ArrayList<>();
        
        // Using try-with-resources to automatically close streams and prevent memory leaks
        try (BufferedReader reader = new BufferedReader(
                new InputStreamReader(context.getAssets().open("BackGateKeys")))) {

            String line;
            while ((line = reader.readLine()) != null) {
                // Ignore empty lines or lines with just whitespace, and trim spaces
                if (!line.trim().isEmpty()) {
                    keys.add(line.trim());
                }
            }
        } catch (Exception e) {
            e.printStackTrace();
        }
        
        return keys;
    }
}
