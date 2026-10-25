package com.example.teambox_sk2.dbHolder;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;
import java.util.logging.Level;
import java.util.logging.Logger;

public class Storage {
    private static final Logger LOGGER = Logger.getLogger(Storage.class.getName());
    private final String name;
    private final int id;
    private Connection connection;

    private void connect() {
        try {
            // Optional: ensure driver is loaded explicitly
            Class.forName("com.mysql.cj.jdbc.Driver");

            String url = "jdbc:mysql://10.0.2.2:3306/" + name + "?useSSL=false&serverTimezone=UTC&allowPublicKeyRetrieval=true";
            this.connection = DriverManager.getConnection(url, "root", "");
        } catch (ClassNotFoundException e) {
            LOGGER.log(Level.SEVERE, "MySQL JDBC Driver class not found", e);
            throw new RuntimeException("MySQL JDBC Driver not found", e);
        } catch (SQLException e) {
            LOGGER.log(Level.SEVERE, "Failed to connect to database: " + name, e);
            // Throwing RuntimeException prevents moving forward with a null connection
            throw new RuntimeException("Database connection failed for: " + name, e);
        }
    }

    public Connection getConnection() {
        return connection;
    }

    public Storage(String name, int id) {
        this.name = name;
        this.id = id;
        this.connect();
    }

    public String getName() {
        return name;
    }

    public int getID() {
        return id;
    }

    public long getInfo() {
        try {
            return connection.isClosed() ? 0L : 1L;
        } catch (SQLException e) {
            return 0L;
        }
    }

    public String InfoToStrArr(long[] args) {
        return "Database Connected: " + name + " (ID: " + id + ")";
    }
}
