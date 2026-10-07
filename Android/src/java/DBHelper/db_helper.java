package com.example.teambox_sk1.DBHelper;

import java.sql.ResultSet;

public interface db_helper {
    void connect();
    ResultSet executeQuery(String sql);
    int executeUpdate(String sql);
    void disconnect();
}
