package com.example.teambox_sk2.dbHolder;

public class EntityCreator implements CreateEntity {
    @Override
    public Entity createEntity(String name, int id, String[] attributes) {
        return new Entity(name, id, attributes);
    }
}
