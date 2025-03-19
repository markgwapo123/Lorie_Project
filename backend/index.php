<?php

// Database connection
$host = 'localhost';
$dbname = 'LabManagementSystem';
$username = 'root'; 
$password = ''; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Function to add a new user
function addUser($name, $user_type, $course_year_section = null) {
    global $pdo;
    $sql = "INSERT INTO users (name, user_type, course_year_section) VALUES (:name, :user_type, :course_year_section)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['name' => $name, 'user_type' => $user_type, 'course_year_section' => $course_year_section]);
    return $pdo->lastInsertId();
}

// Function to add an inventory item
function addInventoryItem($category, $item_name, $photo = null, $total) {
    global $pdo;
    $sql = "INSERT INTO inventory (category, item_name, photo, total, available) VALUES (:category, :item_name, :photo, :total, :total)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['category' => $category, 'item_name' => $item_name, 'photo' => $photo, 'total' => $total]);
    return $pdo->lastInsertId();
}

// Function to borrow an item
function borrowItem($user_id, $inventory_id, $borrow_date, $return_date) {
    global $pdo;
    $pdo->beginTransaction();
    try {
        // Insert into borrow_records
        $sql = "INSERT INTO borrow_records (user_id, borrow_date, return_date) VALUES (:user_id, :borrow_date, :return_date)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $user_id, 'borrow_date' => $borrow_date, 'return_date' => $return_date]);
        $borrow_record_id = $pdo->lastInsertId();

        // Insert into borrowed_items
        $sql = "INSERT INTO borrowed_items (borrow_record_id, inventory_id) VALUES (:borrow_record_id, :inventory_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['borrow_record_id' => $borrow_record_id, 'inventory_id' => $inventory_id]);

        // Update inventory availability
        $sql = "UPDATE inventory SET available = available - 1 WHERE id = :inventory_id AND available > 0";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['inventory_id' => $inventory_id]);

        $pdo->commit();
        return $borrow_record_id;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

// Function to return an item
function returnItem($borrow_record_id, $inventory_id) {
    global $pdo;
    $pdo->beginTransaction();
    try {
        // Delete from borrowed_items
        $sql = "DELETE FROM borrowed_items WHERE borrow_record_id = :borrow_record_id AND inventory_id = :inventory_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['borrow_record_id' => $borrow_record_id, 'inventory_id' => $inventory_id]);

        // Update inventory availability
        $sql = "UPDATE inventory SET available = available + 1 WHERE id = :inventory_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['inventory_id' => $inventory_id]);

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

// Function to mark an item as damaged
function reportDamagedItem($inventory_id, $code) {
    global $pdo;
    $sql = "INSERT INTO damaged_items (inventory_id, code) VALUES (:inventory_id, :code)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['inventory_id' => $inventory_id, 'code' => $code]);
    return $pdo->lastInsertId();
}

?>
