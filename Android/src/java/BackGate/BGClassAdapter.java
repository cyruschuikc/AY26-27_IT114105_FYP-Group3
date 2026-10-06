package com.example.teambox_sk1.BackGate;

import java.util.List;

public class BGClassAdapter extends BGObjAdapter implements Backgate {
    @Override
    public void loadKeysFromFile(String filePath) {super.loadKeysFromFile(filePath);}
    @Override
    public String[] praseArgs() {return super.praseArgs();}

    @Override
    public String addKey(String key) {return super.addKey(key);}

    @Override
    public String getKey(String arg) {return super.getKey(arg);}

    @Override
    public boolean check(String key, String arg) {return super.check(key, arg);}
}
