<?php
session_start();


    $conn = new mysqli("localhost", "root", "", "pos");

    if ($conn->connect_error){
        die("Connection Failed: ".$conn->connect_error);
    }

    if(($handle = fopen("C:\Users\USER\Documents\September 2024 Customer Database.csv", "r")) !== FALSE){
        fgetcsv($handle);
        while(($data = fgetcsv($handle)) !== FALSE ){
            $stmt = $conn->prepare("INSERT INTO customers (customer_id, first_name, company_name, phone_number, address, customer_category, other_information) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss",  $data[17],  $data[0],  $data[14],  $data[3], $data[4], $data[16], $data[10]);

            $stmt->execute();
        }
        fclose($handle);
    }

    $conn->close();