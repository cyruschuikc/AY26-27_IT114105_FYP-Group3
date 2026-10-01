package com.example.teambox_sk1.BackGate;

public class BGClassAdapter extends BGObjAdapter implements Backgate {
    @Override
    public void loadKeysFromFile(String filePath) {super.loadKeysFromFile(filePath);}

    @Override
    public String[] open() {return super.open();}

    @Override
    public String[] setWindow(String[] args) {return super.setWindow(args);}

    @Override
    public String[] getWindow(String[] window) {return super.getWindow(window);}

    @Override
    public String addKey(String key) {return super.addKey(key);}

    @Override
    public String getKey(String arg) {return super.getKey(arg);}

    @Override
    public boolean check(String key, String arg) {return super.check(key, arg);}
}
