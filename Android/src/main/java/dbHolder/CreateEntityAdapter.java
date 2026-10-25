package com.example.teambox_sk2.dbHolder;

public class CreateEntityAdapter implements CreateEntity {
    private final EntityCreator entityCreator;

    public CreateEntityAdapter(EntityCreator entityCreator) {
        this.entityCreator = entityCreator;
    }

    @Override
    public Entity createEntity(String name, int id, String[] attributes) {
        Entity entity = entityCreator.createEntity(name, id, attributes);
        // Object Adapter pattern translation to void command handler
        return entity;
    }
}
