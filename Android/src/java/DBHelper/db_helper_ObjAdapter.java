package com.example.teambox_sk1.DBHelper;

import java.sql.ResultSet;

public class db_helper_ObjAdapter extends LegacyDB implements db_helper {
    private LegacyDB lDB;

    // Inject the adaptee via constructor composition
    public db_helper_ObjAdapter(LegacyDB legacyDatabase) {this.lDB = legacyDatabase;}

    @Override
    public void connect() {lDB.openConnection();}

    @Override
    public ResultSet executeQuery(String sql) {return lDB.runQuery(sql);}

    @Override
    public int executeUpdate(String sql) {return lDB.runUpdate(sql);}

    @Override
    public void disconnect() {lDB.closeConnection();}
}
