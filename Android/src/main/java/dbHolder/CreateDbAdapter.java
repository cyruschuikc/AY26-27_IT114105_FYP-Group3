package com.example.teambox_sk2.dbHolder;

public class CreateDbAdapter extends DataRepoCreator implements CreateDB {
    @Override
    public DataRepository createDataRepo(String name, Storage useStorage) {
        DataRepository repo = super.createDataRepo(name, useStorage);
        // Initialization logging or wrapper execution
        return repo;
    }
}
