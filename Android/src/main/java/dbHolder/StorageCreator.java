package com.example.teambox_sk2.dbHolder;

public class StorageCreator implements CreateStorage {
    @Override
    public Storage createStorage(String name, int id) {
        return new Storage(name, id);
    }
}
