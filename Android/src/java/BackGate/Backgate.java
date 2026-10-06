package com.example.teambox_sk1.BackGate;

import java.util.List;

public interface Backgate {
    public String[] praseArgs();
    public String addKey(String key);
    public String getKey(String arg);
    public boolean check(String key, String arg);
    void loadKeysFromFile(String filePath);
}
