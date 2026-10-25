package com.example.teambox_sk2.dbHolder;

public class DataRepoCreator implements CreateDB {
    @Override
    public DataRepository createDataRepo(String name, Storage useStorage) {
        return new DataRepository(name, useStorage);
    }
}
