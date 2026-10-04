package com.example.teambox_sk1.BackGate;

import java.io.*;
import java.util.*;

public class BGObjAdapter implements Backgate {
    private List<String> keyStorage = new ArrayList<>();

    @Override
    public void loadKeysFromFile(String filePath) {
        try (BufferedReader reader = new BufferedReader(new FileReader(filePath))) {
            String line;
            while ((line = reader.readLine()) != null) {
                String trimmedKey = line.trim();
                if (!trimmedKey.isEmpty() && !keyStorage.contains(trimmedKey)) {
                    keyStorage.add(trimmedKey);
                }
            }
        } catch (IOException e) {
            e.printStackTrace();
        }
    }

    @Override
    public String[] praseArgs() {
        // 修正這裡的轉型，避免發生 ClassCastException
        return keyStorage.toArray(new String[0]);
    }

    @Override
    public String addKey(String key) {
        if (key != null && !key.isEmpty() && !keyStorage.contains(key)) {
            keyStorage.add(key);
        }
        return key;
    }

    @Override
    public String getKey(String arg) {
        for (String storedKey : keyStorage) {
            if (storedKey.equals(arg) || storedKey.contains(arg)) {
                return storedKey;
            }
        }
        return null;
    }

    @Override
    public boolean check(String key, String arg) {
        String retrievedKey = getKey(arg);
        return key != null && key.equals(retrievedKey);
    }
}
