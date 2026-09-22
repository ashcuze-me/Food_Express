<?php

include "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $_POST["name"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $password = $_POST["password"];

    $houseNo = $_POST["house_no"];
    $areaStreet = $_POST["area_street"];
    $landmark = $_POST["landmark"];
    $city = $_POST["city"];
    $state = $_POST["state"];
    $pincode = $_POST["pincode"];

    $conn->begin_transaction();

    try {

        $sql = "INSERT INTO Customer
                (FullName, Email, PhoneNumber, Password)
                VALUES (?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssss",
            $name,
            $email,
            $phone,
            $password
        );

        if (!$stmt->execute()) {
            throw new Exception("Customer creation failed.");
        }

        $customerID = $conn->insert_id;

        $sql = "INSERT INTO Address
                (CustomerID, HouseNo, AreaStreet, Landmark,
                 City, State, PINCode)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "issssss",
            $customerID,
            $houseNo,
            $areaStreet,
            $landmark,
            $city,
            $state,
            $pincode
        );

        if (!$stmt->execute()) {
            throw new Exception("Address creation failed.");
        }

        $conn->commit();

        $message = "Account and address created successfully!";

    } catch (Exception $e) {

        $conn->rollback();

        $message = "Error: " . $e->getMessage();

    }

}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Create Account</title>

</head>

<body>

    <h1>Create Account</h1>

    <form method="POST">

        <h3>Customer Details</h3>

        <label>Full Name:</label>
        <input type="text" name="name" required>
        <br><br>

        <label>Email:</label>
        <input type="email" name="email" required>
        <br><br>

        <label>Phone:</label>
        <input type="text" name="phone">
        <br><br>

        <label>Password:</label>
        <input type="password" name="password" required>
        <br><br>

        <h3>Address Details</h3>

        <label>House Number:</label>
        <input type="text" name="house_no" required>
        <br><br>

        <label>Area / Street:</label>
        <input type="text" name="area_street" required>
        <br><br>

        <label>Landmark:</label>
        <input type="text" name="landmark">
        <br><br>

        <label>City:</label>
        <input type="text" name="city" required>
        <br><br>

        <label>State:</label>
        <input type="text" name="state" required>
        <br><br>

        <label>PIN Code:</label>
        <input type="text" name="pincode" required>
        <br><br>

        <button type="submit">Create Account</button>

    </form>

    <p><?php echo htmlspecialchars($message); ?></p>

    <a href="index.php">Back to Home</a>

</body>

</html>