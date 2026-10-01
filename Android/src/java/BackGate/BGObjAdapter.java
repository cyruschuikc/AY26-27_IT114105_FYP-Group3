package com.example.teambox_sk1.BackGate;

import java.io.*;
import java.util.*;

public class BGObjAdapter implements Backgate {
    private List<String> keyStorage = new ArrayList<>();

    // 保留原本的檔案路徑讀取
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
    public String[] open() { return new String[0]; }

    @Override
    public String[] setWindow(String[] args) { return new String[0]; }

    @Override
    public String[] getWindow(String[] window) { return window; }

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
