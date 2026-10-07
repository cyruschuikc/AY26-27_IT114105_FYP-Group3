package com.example.teambox_sk1.DBHelper;

import java.sql.ResultSet;

public class LegacyDB {
    public void openConnection() {
        System.out.println("LegacyDatabase: Connection established successfully.");
    }

    public ResultSet runQuery(String querySql) {
        System.out.println("LegacyDatabase: Executing query -> " + querySql);
        return null; // Return simulated ResultSet
    }

    public int runUpdate(String updateSql) {
        System.out.println("LegacyDatabase: Executing update -> " + updateSql);
        return 1; // Return affected rows count
    }

    public void closeConnection() {
        System.out.println("LegacyDatabase: Connection closed.");
    }
}
