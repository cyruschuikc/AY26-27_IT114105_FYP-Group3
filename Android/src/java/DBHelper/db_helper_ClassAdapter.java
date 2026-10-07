package com.example.teambox_sk1.DBHelper;

import java.sql.ResultSet;

public class db_helper_ClassAdapter extends LegacyDB implements db_helper {

    @Override
    public void connect() {super.openConnection();} // Inherited method from LegacyDatabase

    @Override
    public ResultSet executeQuery(String sql) {return super.runQuery(sql);} // Inherited method from LegacyDatabase

    @Override
    public int executeUpdate(String sql) {return super.runUpdate(sql);} // Inherited method from LegacyDatabase

    @Override
    public void disconnect() {super.closeConnection();} // Inherited method from LegacyDatabase
}
