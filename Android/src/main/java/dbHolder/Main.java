package com.example.teambox_sk2.dbHolder;

import java.util.*;

public class Main {
    public static void main(String[] args) {
        new Thread(new Runnable() {
            @Override
            public void run() {
                try {
                    System.out.println("=== Starting TeamBox Database Driver ===");

                    /*==========================================
                    1. Storage & Storage Manager Initialization
                    ==========================================*/
                    // Using StorageCreator implementing CreateStorage interface
                    CreateStorage sc = new StorageCreator();
                    Storage storage = sc.createStorage("teambox", 1);

                    // Using CreateStorageAdapter (Class Adapter)
                    CreateStorage sac = new CreateStorageAdapter();
                    Storage sa = sac.createStorage("teambox", 1);

                    // Managing storage configuration
                    StorageManager sm = new StorageManager();
                    sm.upward(); // Scale up / Initialize connection pool
                    ManageStorageAdapter sam = new ManageStorageAdapter();
                    sam.rename("teambox_old", "teambox");

                    /*============================================================================
                    2. Display Storage Information Details
                    #TODO: Implement a method to display storage information in a formatted manner
                    ============================================================================*/
                    System.out.println("Storage Connected: " + storage.getName()
                            + " (ID: " + storage.getID() + ")");
                    System.out.println("Adapter Storage Connected: " + sa.getName()
                            + " (ID: " + sa.getID() + ")");
                    System.out.println(storage.InfoToStrArr(new long[]{100L, 200L}));

                    /*===============================
                    3. Data Repository Initialization
                    ===============================*/
                    // Using DataRepoCreator implementing CreateDB interface
                    CreateDB dc1 = new DataRepoCreator();
                    DataRepository tasksRepo = dc1.createDataRepo("tasks", storage);

                    // Using CreateDbAdapter (Class Adapter for database repositories)
                    CreateDB dc2 = new CreateDbAdapter();
                    DataRepository projectsRepo = dc2.createDataRepo("projects", storage);

                    /*===============================================================================
                    4. Display Repository Information Details
                    #TODO: Implement a method to display repository information in a formatted manner
                    ===============================================================================*/
                    System.out.println("Initialized Repository: " + tasksRepo.getName());
                    System.out.println("Initialized Repository: " + projectsRepo.getName());

                     /*================================
                    5. Entity & Adapter Initialization
                    ================================*/
                    // Using EntityCreator implementing CreateEntity interface
                    CreateEntity ec1 = new EntityCreator();
                    Entity task1 = ec1.createEntity(
                            "Notification Bell Icon",
                            20,
                            new String[]{"Low", "Completed", "Design the notification bell icon"}
                    );

                    // Using CreateEntityAdapter (Object Adapter pattern wrapping EntityCreator)
                    EntityCreator ec2 = new EntityCreator();
                    CreateEntity ea = new CreateEntityAdapter(ec2);
                    Entity task2 = ea.createEntity(
                            "Bell Interaction States",
                            21,
                            new String[]{"Medium", "Completed", "Prepare hover and unread states"}
                    );

                    /*=======================================
                    6. Database Operations & Repository Usage
                    =======================================*/
                    // Adding entities (records) into the repository / database table
                    System.out.println("\nInserting entities into tasks repository...");
                    tasksRepo.addRes(task1);
                    tasksRepo.addRes(task2);

                    // Fetching all entities from the repository table
                    System.out.println("\nFetching all tasks from database repository:");
                    List<Entity> allTasks = tasksRepo.findAll();
                    for (Entity entity : allTasks) {
                        System.out.println("- Task ID: " + entity.getID()
                                + " | Title: " + entity.getName()
                                + " | Attributes info: " + entity.InfoToStrArr(null));
                    }

                    // Repository renaming utility test
                    tasksRepo.rename("tasks", "tasks_backup");
                    System.out.println("Renamed Repository Name: " + tasksRepo.getName());

                    /*=======================
                    7. Finalization & Cleanup
                    =======================*/
                    System.out.println("\n=== TeamBox Driver Execution Completed Successfully ===");

                } catch (Exception e) {
                    e.printStackTrace();
                }
            }
        }).start();
    }
}
