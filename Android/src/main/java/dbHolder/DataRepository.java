package com.example.teambox_sk2.dbHolder;

import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.List;
import java.util.logging.Level;
import java.util.logging.Logger;

public class DataRepository {
    private static final Logger LOGGER = Logger.getLogger(DataRepository.class.getName());
    private String name; // Table name e.g., 'tasks', 'projects', 'users'
    private final Storage useStorage;

    public DataRepository(String name, Storage useStorage) {
        this.name = name;
        this.useStorage = useStorage;
    }

    public String getName() {
        return name;
    }

    public void rename(String No, String Nn) {
        if (this.name.equals(No)) {
            this.name = Nn;
        }
    }

    public void addRes(Entity item) {
        try {
            String query = "INSERT INTO " + name + " (id, name, attributes) VALUES (?, ?, ?)";
            PreparedStatement stmt = useStorage.getConnection().prepareStatement(query);
            stmt.setInt(1, item.getID());
            stmt.setString(2, item.getName());
            stmt.setString(3, "");
            stmt.executeUpdate();
        } catch (SQLException e) {
            LOGGER.log(Level.SEVERE, "Failed to add entity to " + name, e);
        }
    }

    public List<Entity> findAll() {
        List<Entity> entities = new ArrayList<>();
        try {
            String query = "SELECT * FROM " + name;
            PreparedStatement stmt = useStorage.getConnection().prepareStatement(query);
            ResultSet rs = stmt.executeQuery();
            while (rs.next()) {
                int id = rs.getInt("id");
                String title = rs.getString(2); // generic column fetch
                entities.add(new Entity(title, id, new String[]{}));
            }
        } catch (SQLException e) {
            LOGGER.log(Level.SEVERE, "Failed to retrieve entities from " + name, e);
        }
        return entities;
    }
}
