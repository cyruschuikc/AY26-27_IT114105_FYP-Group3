package com.example.teambox_sk2.dbHolder;

public class Entity {
    private final String name;
    private final int id;
    private final String[] attributes;

    public Entity(String name, int id, String[] args) {
        this.name = name;
        this.id = id;
        this.attributes = args;
    }

    public String getName() {
        return name;
    }

    public int getID() {
        return id;
    }

    public long getInfo() {
        return (long) attributes.length;
    }

    public String InfoToStrArr(long[] args) throws IllegalArgumentException {
        return String.join(",", attributes);
    }

    public String InfoToStrArr() throws IllegalArgumentException {
        return String.join(",", attributes);
    }
}
