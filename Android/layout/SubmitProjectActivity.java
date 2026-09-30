package com.example.projectapp;
import android.content.Intent;import android.os.Bundle;import android.widget.*;import androidx.appcompat.app.AppCompatActivity;
public class SubmitProjectActivity extends AppCompatActivity{@Override protected void onCreate(Bundle b){super.onCreate(b);setContentView(R.layout.activity_submit_project);EditText n=findViewById(R.id.etStudentName),id=findViewById(R.id.etStudentId),l=findViewById(R.id.etProjectLink),notes=findViewById(R.id.etNotes);Spinner sp=findViewById(R.id.spProject);TextView r=findViewById(R.id.tvSubmissionResult);Button sub=findViewById(R.id.btnSubmit),back=findViewById(R.id.btnTeacherPage);sp.setAdapter(new ArrayAdapter<>(this,android.R.layout.simple_spinner_item,new String[]{"Mobile App Development","Database Project","AI Project","Networking Project"}));sub.setOnClickListener(v->r.setText("Submission Successful
Student: "+n.getText()+"
ID: "+id.getText()+"
Project: "+sp.getSelectedItem()+"
Link: "+l.getText()+"
Notes: "+notes.getText()));back.setOnClickListener(v->startActivity(new Intent(this,CreateProjectActivity.class)));}}