package com.example.teambox_sk1.BackGate;

public interface Backgate {
    public String[] open();
    public String[] setWindow(String[] args);
    public String[] getWindow(String[] window);
    public String addKey(String key);
    public String getKey(String arg);
    public boolean check(String key, String arg);
    void loadKeysFromFile(String filePath);
}
