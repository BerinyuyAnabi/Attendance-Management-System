<?php

// Starting the transaction 
$conn-> begin_transaction();

try{
    // Insert into the user table 
$stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password_hash, role) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $firstName, $lastName, $email, $password, $role);

// Checking for errors on execute 
if($stmt->execute()){
    echo "Registration Successful";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
if($role == 'student'){
    $stmt2 = $conn->prepare("INSERT INTO students (student_id) VALUES (?)");
    $stmt2 ->bind_param("i",$user_id);

    if(!$stmt2->execute()){
        throw new Exception("Insertion into the student table failed!")''
    }
    $stmt2->close();
}

else if($role == 'facullty'){
    $stmt2 = $conn->prepare("INSERT INTO faculty (faculty_id) VALUES (?)");
    $stmt2 = bind_param("i",$user_id);

    if(!$stmt2->execute()){
        throw new Exception("Insertion into the student table failed!")
    }
    $stmt2->close();
}
$conn->commit();

}catch(Exception $e){
    $conn->rollback();
    echo "Registration Failed: " . $e->getMessage();
}

$conn->close();

?>

