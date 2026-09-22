<?php

include "db.php";

$action = $_GET["action"];
$orderID = (int)$_GET["order_id"];

$result = $conn->query(
    "SELECT OrderStatus FROM Orders WHERE OrderID = $orderID"
);

$order = $result->fetch_assoc();

if (!$order) {
    die("Order not found.");
}

$oldStatus = $order["OrderStatus"];
$newStatus = $oldStatus;
$changedBy = "System";

if ($action == "accept" && $oldStatus == "Placed") {

    $newStatus = "Accepted";
    $changedBy = "Restaurant";

} elseif ($action == "prepare" && $oldStatus == "Accepted") {

    $newStatus = "Preparing";
    $changedBy = "Restaurant";

} elseif ($action == "assign" && $oldStatus == "Preparing") {

    $partner = $conn->query(
        "SELECT DeliveryPartnerID
         FROM DeliveryPartner
         WHERE AvailabilityStatus = 'Available'
         LIMIT 1"
    )->fetch_assoc();

    if (!$partner) {
        die("No delivery partner available.");
    }

    $partnerID = $partner["DeliveryPartnerID"];

    $conn->query(
    "INSERT INTO Delivery
    (OrderID, DeliveryPartnerID, DeliveryStatus, PickupTime)
    VALUES
    ($orderID, $partnerID, 'Out for Delivery', NOW())"
);

    $conn->query(
        "UPDATE DeliveryPartner
         SET AvailabilityStatus = 'Busy'
         WHERE DeliveryPartnerID = $partnerID"
    );

    $newStatus = "Out for Delivery";

} elseif ($action == "deliver" && $oldStatus == "Out for Delivery") {

    $conn->query(
        "UPDATE Delivery
         SET DeliveryStatus = 'Delivered',
             ActualDeliveryTime = NOW()
         WHERE OrderID = $orderID"
    );

    $conn->query(
        "UPDATE DeliveryPartner dp
         JOIN Delivery d
         ON dp.DeliveryPartnerID = d.DeliveryPartnerID
         SET dp.AvailabilityStatus = 'Available'
         WHERE d.OrderID = $orderID"
    );

    $newStatus = "Delivered";
    $changedBy = "Delivery Partner";

} elseif ($action == "archive" && $oldStatus == "Delivered") {

    $conn->query(
        "INSERT INTO OrderHistory
        (OriginalOrderID, CustomerID, RestaurantID, AddressID,
         TotalAmount, FinalStatus, OrderDate, CompletedAt)
        SELECT OrderID, CustomerID, RestaurantID, AddressID,
               TotalAmount, OrderStatus, OrderDate, NOW()
        FROM Orders
        WHERE OrderID = $orderID"
    );

    $conn->query(
    "DELETE op
     FROM OnlinePayment op
     JOIN Payment p ON op.PaymentID = p.PaymentID
     WHERE p.OrderID = $orderID"
);

$conn->query(
    "DELETE cod
     FROM CashOnDelivery cod
     JOIN Payment p ON cod.PaymentID = p.PaymentID
     WHERE p.OrderID = $orderID"
);

$conn->query(
    "UPDATE Orders
     SET IsArchived = TRUE
     WHERE OrderID = $orderID"
);

header("Location: index.php");
exit;

} else {

    die("Invalid workflow action.");

}

if ($newStatus != $oldStatus) {

    $conn->query(
        "UPDATE Orders
         SET OrderStatus = '$newStatus'
         WHERE OrderID = $orderID"
    );

    $conn->query(
        "INSERT INTO OrderStatusHistory
        (OrderID, PreviousStatus, NewStatus, ChangedBy)
        VALUES
        ($orderID, '$oldStatus', '$newStatus', '$changedBy')"
    );

}

header("Location: index.php");

?>