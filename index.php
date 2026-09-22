<?php

include "db.php";

$message = $_GET["message"] ?? "";


/* =========================
   DASHBOARD COUNTS
   ========================= */

$activeOrdersCount = $conn->query(
    "SELECT COUNT(*) AS total
     FROM Orders
     WHERE IsArchived = FALSE"
)->fetch_assoc()["total"];

$completedOrdersCount = $conn->query(
    "SELECT COUNT(*) AS total
     FROM OrderHistory"
)->fetch_assoc()["total"];

$customerCount = $conn->query(
    "SELECT COUNT(*) AS total
     FROM Customer"
)->fetch_assoc()["total"];

$restaurantCount = $conn->query(
    "SELECT COUNT(*) AS total
     FROM Restaurant"
)->fetch_assoc()["total"];


/* =========================
   ORDER HISTORY
   ========================= */

$history = $conn->query(
    "SELECT * FROM OrderHistory
     ORDER BY CompletedAt DESC"
);


/* =========================
   ACTIVE ORDERS
   ========================= */

$orders = $conn->query(
    "SELECT o.OrderID, c.FullName AS CustomerName,
            r.RestaurantName, o.TotalAmount,
            o.OrderStatus, o.OrderDate,
            dp.FullName AS DeliveryPartnerName,
            (
                SELECT GROUP_CONCAT(
                    CONCAT(mi.ItemName, ' x ', oi.Quantity)
                    SEPARATOR ', '
                )
                FROM OrderItem oi
                JOIN MenuItem mi
                ON oi.MenuItemID = mi.MenuItemID
                WHERE oi.OrderID = o.OrderID
            ) AS OrderItems
     FROM Orders o
     JOIN Customer c ON o.CustomerID = c.CustomerID
     JOIN Restaurant r ON o.RestaurantID = r.RestaurantID
     LEFT JOIN Delivery d ON o.OrderID = d.OrderID
     LEFT JOIN DeliveryPartner dp
     ON d.DeliveryPartnerID = dp.DeliveryPartnerID
     WHERE o.IsArchived = FALSE
     ORDER BY o.OrderID DESC"
);

?>

<!DOCTYPE html>
<html>

<head>

    <title>FoodExpress</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<div class="container">


    <!-- =========================
         HEADER
         ========================= -->

    <div class="top-bar">

        <div class="brand">

            <a href="index.php">

                <img src="logo.png" alt="FoodExpress Logo">

            </a>

        </div>


        <div class="welcome">

            <h2>Welcome!</h2>

            <p>
                Manage customers, orders and deliveries from one place.
            </p>

        </div>


        <div class="top-actions">

            <a href="create_account.php">
                <button>Create Account</button>
            </a>

            <a href="place_order.php">
                <button>Place Order</button>
            </a>

        </div>

    </div>


    <!-- =========================
         MESSAGE
         ========================= -->

    <?php if ($message != "") { ?>

        <p class="message">

            <?php echo htmlspecialchars($message); ?>

        </p>

    <?php } ?>


    <!-- =========================
         DASHBOARD STATS
         ========================= -->

    <div class="stats-grid">

        <div class="stat-card">

            <span>Active Orders</span>

            <strong>
                <?php echo $activeOrdersCount; ?>
            </strong>

        </div>


        <div class="stat-card">

            <span>Completed Orders</span>

            <strong>
                <?php echo $completedOrdersCount; ?>
            </strong>

        </div>


        <div class="stat-card">

            <span>Customers</span>

            <strong>
                <?php echo $customerCount; ?>
            </strong>

        </div>


        <div class="stat-card">

            <span>Restaurants</span>

            <strong>
                <?php echo $restaurantCount; ?>
            </strong>

        </div>

    </div>


    <hr>


    <!-- =========================
         ACTIVE ORDERS
         ========================= -->

    <h2>Active Orders</h2>

    <?php if ($orders->num_rows > 0) { ?>

        <table>

            <tr>

                <th>Order ID</th>
                <th>Customer</th>
                <th>Restaurant</th>
                <th>Order Items</th>
                <th>Total</th>
                <th>Status</th>
                <th>Delivery Partner</th>
                <th>Action</th>

            </tr>


            <?php while ($order = $orders->fetch_assoc()) { ?>

                <tr>

                    <td>
                        <?php echo $order["OrderID"]; ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($order["CustomerName"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($order["RestaurantName"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($order["OrderItems"] ?? ""); ?>
                    </td>

                    <td>
                        ₹<?php echo $order["TotalAmount"]; ?>
                    </td>


                    <!-- STATUS -->

                    <td>

                        <?php

                        $status = $order["OrderStatus"];

                        if ($status == "Placed") {

                            $class = "status-placed";

                        } elseif ($status == "Accepted") {

                            $class = "status-accepted";

                        } elseif ($status == "Preparing") {

                            $class = "status-preparing";

                        } elseif ($status == "Out for Delivery") {

                            $class = "status-delivery";

                        } elseif ($status == "Delivered") {

                            $class = "status-delivered";

                        } else {

                            $class = "";

                        }

                        ?>

                        <span class="<?php echo $class; ?>">

                            <?php echo htmlspecialchars($status); ?>

                        </span>

                    </td>


                    <!-- DELIVERY PARTNER -->

                    <td>

                        <?php

                        echo $order["DeliveryPartnerName"]

                            ? htmlspecialchars($order["DeliveryPartnerName"])

                            : "Not Assigned";

                        ?>

                    </td>


                    <!-- ACTION -->

                    <td>

                        <?php if ($order["OrderStatus"] == "Placed") { ?>

                            <a href="workflow.php?action=accept&order_id=<?php echo $order["OrderID"]; ?>">

                                <button class="action-btn accept">
                                    Accept Order
                                </button>

                            </a>


                        <?php } elseif ($order["OrderStatus"] == "Accepted") { ?>

                            <a href="workflow.php?action=prepare&order_id=<?php echo $order["OrderID"]; ?>">

                                <button class="action-btn prepare">
                                    Start Preparing
                                </button>

                            </a>


                        <?php } elseif ($order["OrderStatus"] == "Preparing") { ?>

                            <a href="workflow.php?action=assign&order_id=<?php echo $order["OrderID"]; ?>">

                                <button class="action-btn assign">
                                    Assign Delivery
                                </button>

                            </a>


                        <?php } elseif ($order["OrderStatus"] == "Out for Delivery") { ?>

                            <a href="workflow.php?action=deliver&order_id=<?php echo $order["OrderID"]; ?>">

                                <button class="action-btn deliver">
                                    Mark Delivered
                                </button>

                            </a>


                        <?php } elseif ($order["OrderStatus"] == "Delivered") { ?>

                            <a href="workflow.php?action=archive&order_id=<?php echo $order["OrderID"]; ?>">

                                <button class="action-btn archive">
                                    Archive Order
                                </button>

                            </a>

                        <?php } ?>

                    </td>

                </tr>

            <?php } ?>

        </table>


    <?php } else { ?>

        <div class="empty-state">

            <strong>No active orders</strong>

            <p>New orders will appear here.</p>

        </div>

    <?php } ?>


    <hr>


    <!-- =========================
         COMPLETED ORDER HISTORY
         ========================= -->

    <h2>Completed Order History</h2>

    <?php if ($history->num_rows > 0) { ?>

        <table>

            <tr>

                <th>Archive ID</th>
                <th>Original Order ID</th>
                <th>Customer ID</th>
                <th>Restaurant ID</th>
                <th>Total Amount</th>
                <th>Final Status</th>
                <th>Completed At</th>

            </tr>


            <?php while ($record = $history->fetch_assoc()) { ?>

                <tr>

                    <td>
                        <?php echo $record["ArchiveID"]; ?>
                    </td>

                    <td>
                        <?php echo $record["OriginalOrderID"]; ?>
                    </td>

                    <td>
                        <?php echo $record["CustomerID"]; ?>
                    </td>

                    <td>
                        <?php echo $record["RestaurantID"]; ?>
                    </td>

                    <td>
                        ₹<?php echo $record["TotalAmount"]; ?>
                    </td>

                    <td>

                        <span class="status-delivered">

                            <?php echo htmlspecialchars($record["FinalStatus"]); ?>

                        </span>

                    </td>

                    <td>
                        <?php echo $record["CompletedAt"]; ?>
                    </td>

                </tr>

            <?php } ?>

        </table>


    <?php } else { ?>

        <p>No completed orders yet.</p>

    <?php } ?>


    <hr>


    <!-- =========================
         LIVE DATABASE TABLES
         ========================= -->

    <h2>Live Database Tables</h2>

    <?php

    $tables = [

        "Customer",
        "Address",
        "Restaurant",
        "MenuItem",
        "Orders",
        "OrderItem",
        "Payment",
        "OnlinePayment",
        "CashOnDelivery",
        "DeliveryPartner",
        "Delivery",
        "OrderStatusHistory",
        "OrderHistory"

    ];


    foreach ($tables as $table) {

        echo "<h3>" . htmlspecialchars($table) . "</h3>";

        $result = $conn->query("SELECT * FROM `$table`");


        if ($result && $result->num_rows > 0) {

            echo "<table>";

            echo "<tr>";


            $fields = $result->fetch_fields();


            foreach ($fields as $field) {

                echo "<th>" .
                     htmlspecialchars($field->name) .
                     "</th>";

            }


            echo "</tr>";


            while ($row = $result->fetch_assoc()) {

                echo "<tr>";


                foreach ($row as $value) {

                    echo "<td>" .
                         htmlspecialchars($value ?? "") .
                         "</td>";

                }


                echo "</tr>";

            }


            echo "</table>";


        } else {

            echo "<p>No records found.</p>";

        }

    }

    ?>


    <hr>


    <!-- =========================
         DATABASE STATUS
         ========================= -->

    <div class="database-status">

        <h2>Database Connected</h2>

        <p>
            Our Food Delivery System is working.
        </p>

    </div>


</div>

</body>

</html>