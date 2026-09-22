<?php

include "db.php";

$customers = $conn->query(
    "SELECT CustomerID, FullName FROM Customer"
);

$restaurants = $conn->query(
    "SELECT RestaurantID, RestaurantName FROM Restaurant"
);

$menuItems = $conn->query(
    "SELECT DISTINCT
        m.MenuItemID,
        m.ItemName,
        m.Price,
        rm.RestaurantID
     FROM MenuItem m
     JOIN RestaurantMenu rm
        ON m.MenuItemID = rm.MenuItemID
     WHERE m.AvailabilityStatus = 'Available'"
);
$addresses = $conn->query(
    "SELECT AddressID, CustomerID, HouseNo, AreaStreet, City
     FROM Address"
);

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $customerID = (int)$_POST["customer_id"];
    $addressID = (int)$_POST["address_id"];
    $restaurantID = (int)$_POST["restaurant_id"];

    $menuItemIDs = $_POST["menu_item_id"];
    $quantities = $_POST["quantity"];

    $paymentMethod = $_POST["payment_method"];

    $total = 0;
    $items = [];

    for ($i = 0; $i < count($menuItemIDs); $i++) {

        $menuItemID = (int)$menuItemIDs[$i];
        $quantity = (int)$quantities[$i];

        if ($menuItemID <= 0 || $quantity <= 0) {
            continue;
        }

        $result = $conn->query(
        "SELECT m.Price
        FROM MenuItem m
        JOIN RestaurantMenu rm
            ON m.MenuItemID = rm.MenuItemID
        WHERE m.MenuItemID = $menuItemID
        AND rm.RestaurantID = $restaurantID
        AND m.AvailabilityStatus = 'Available'"
    );

        $item = $result->fetch_assoc();

        if (!$item) {
            $message = "Invalid menu item selected.";
            break;
        }

        $price = $item["Price"];

        $total += $price * $quantity;

        $items[] = [
            "MenuItemID" => $menuItemID,
            "Quantity" => $quantity,
            "Price" => $price
        ];

    }

    if ($message == "" && count($items) > 0) {

        $conn->begin_transaction();

        try {

            $conn->query(
                "INSERT INTO Orders
                (CustomerID, AddressID, RestaurantID, TotalAmount)
                VALUES
                ($customerID, $addressID, $restaurantID, $total)"
            );

            $orderID = $conn->insert_id;

            foreach ($items as $item) {

                $menuItemID = $item["MenuItemID"];
                $quantity = $item["Quantity"];
                $price = $item["Price"];

                $conn->query(
                    "INSERT INTO OrderItem
                    (OrderID, MenuItemID, Quantity, UnitPrice)
                    VALUES
                    ($orderID, $menuItemID, $quantity, $price)"
                );

            }

            if ($paymentMethod == "Online Payment") {

                $paymentStatus = "Paid";

            } else {

                $paymentStatus = "Pending";

            }

            $conn->query(
                "INSERT INTO Payment
                (OrderID, PaymentStatus, PaymentDateTime, PaymentAmount)
                VALUES
                ($orderID, '$paymentStatus', NOW(), $total)"
            );

            $paymentID = $conn->insert_id;

            if ($paymentMethod == "Online Payment") {

                $transactionID =
                    "MOCK_TXN_" . $orderID . "_" . time();

                $conn->query(
                    "INSERT INTO OnlinePayment
                    (PaymentID, TransactionID, GatewayName, PaymentMethod)
                    VALUES
                    ($paymentID, '$transactionID', 'Mock Gateway', 'UPI')"
                );

            } else {

                $conn->query(
                    "INSERT INTO CashOnDelivery
                    (PaymentID, CollectedBy, CollectionStatus)
                    VALUES
                    ($paymentID, 'Delivery Partner', 'Not Collected')"
                );

            }

            $conn->query(
                "INSERT INTO OrderStatusHistory
                (OrderID, PreviousStatus, NewStatus, ChangedBy)
                VALUES
                ($orderID, '', 'Placed', 'Customer')"
            );

            $conn->commit();

            $message =
                "Order placed successfully! Order ID: $orderID";

        } catch (Exception $e) {

            $conn->rollback();

            $message = "Order failed.";

        }

    } elseif ($message == "") {

        $message = "Please select at least one menu item.";

    }

}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Place Order</title>

</head>

<body>

    <h1>Place Order</h1>

    <p><?php echo htmlspecialchars($message); ?></p>

    <form method="POST">

        <label>Customer:</label>

        <select name="customer_id" id="customer_id" required>

            <option value="">Select Customer</option>

            <?php while ($row = $customers->fetch_assoc()) { ?>

                <option value="<?php echo $row["CustomerID"]; ?>">

                    <?php echo htmlspecialchars($row["FullName"]); ?>

                </option>

            <?php } ?>

        </select>

        <br><br>

        <label>Address:</label>

        <select id="address_display" disabled>

            <option value="">Select Customer First</option>

            <?php while ($row = $addresses->fetch_assoc()) { ?>

                <option
                    value="<?php echo $row["AddressID"]; ?>"
                    data-customer="<?php echo $row["CustomerID"]; ?>"
                >

                    <?php

                    echo htmlspecialchars(
                        $row["HouseNo"] . ", " .
                        $row["AreaStreet"] . ", " .
                        $row["City"]
                    );

                    ?>

                </option>

            <?php } ?>

        </select>

        <input
            type="hidden"
            name="address_id"
            id="address_id"
        >

        <br><br>

        <label>Restaurant:</label>

        <select name="restaurant_id" id="restaurant_id" required>

            <option value="">Select Restaurant</option>

            <?php while ($row = $restaurants->fetch_assoc()) { ?>

                <option value="<?php echo $row["RestaurantID"]; ?>">

                    <?php echo htmlspecialchars($row["RestaurantName"]); ?>

                </option>

            <?php } ?>

        </select>

        <br><br>

        <h3>Menu Items</h3>

        <div id="items_container">

            <div class="item_row">

                <label>Menu Item:</label>

                <select name="menu_item_id[]" class="menu_item" required>

                    <option value="">Select Item</option>

                    <?php

                    $menuItems->data_seek(0);

                    while ($row = $menuItems->fetch_assoc()) {

                    ?>

                        <option
                            value="<?php echo $row["MenuItemID"]; ?>"
                            data-restaurant="<?php echo $row["RestaurantID"]; ?>"
                        >

                            <?php

                            echo htmlspecialchars(
                                $row["ItemName"] .
                                " - ₹" .
                                $row["Price"]
                            );

                            ?>

                        </option>

                    <?php } ?>

                </select>

                <label>Quantity:</label>

                <input
                    type="number"
                    name="quantity[]"
                    min="1"
                    value="1"
                    required
                >

                <button type="button" onclick="removeItem(this)">
                    Remove
                </button>

                <br><br>

            </div>

        </div>

        <button type="button" onclick="addItem()">
            Add Another Item
        </button>

        <br><br>

        <label>Payment Method:</label>

        <select name="payment_method" required>

            <option value="">Select Payment Method</option>

            <option value="Online Payment">
                Online Payment
            </option>

            <option value="Cash on Delivery">
                Cash on Delivery
            </option>

        </select>

        <br><br>

        <button type="submit">Place Order</button>

    </form>

    <br>

    <a href="index.php">Back to Home</a>

    <script>

        const restaurantSelect =
            document.getElementById("restaurant_id");

        const customerSelect =
            document.getElementById("customer_id");

        const addressDisplay =
            document.getElementById("address_display");

        const addressID =
            document.getElementById("address_id");

        restaurantSelect.addEventListener("change", function() {

            const restaurantID = this.value;

            const menuSelects =
                document.querySelectorAll(".menu_item");

            menuSelects.forEach(function(menuSelect) {

                for (let i = 0; i < menuSelect.options.length; i++) {

                    const option = menuSelect.options[i];

                    if (option.value === "") {

                        option.hidden = false;

                    } else {

                        option.hidden =
                            option.dataset.restaurant !== restaurantID;

                    }

                }

                menuSelect.value = "";

            });

        });

        customerSelect.addEventListener("change", function() {

            const customerID = this.value;

            addressDisplay.value = "";

            addressID.value = "";

            for (let i = 0; i < addressDisplay.options.length; i++) {

                const option = addressDisplay.options[i];

                if (option.value === "") {

                    option.hidden = false;

                } else {

                    option.hidden =
                        option.dataset.customer !== customerID;

                }

            }

            for (let i = 0; i < addressDisplay.options.length; i++) {

                const option = addressDisplay.options[i];

                if (
                    option.dataset.customer === customerID &&
                    customerID !== ""
                ) {

                    addressDisplay.value = option.value;

                    addressID.value = option.value;

                    break;

                }

            }

        });

        function addItem() {

    const container =
        document.getElementById("items_container");

    const firstRow =
        document.querySelector(".item_row");

    const newRow =
        firstRow.cloneNode(true);

    const newMenuSelect =
        newRow.querySelector(".menu_item");

    newMenuSelect.value = "";

    newRow.querySelector("input").value = 1;

    const restaurantID = restaurantSelect.value;

    for (let i = 0; i < newMenuSelect.options.length; i++) {

        const option = newMenuSelect.options[i];

        if (option.value === "") {

            option.hidden = false;

        } else {

            option.hidden =
                option.dataset.restaurant !== restaurantID;

        }

    }

    container.appendChild(newRow);

}

        function removeItem(button) {

            const rows =
                document.querySelectorAll(".item_row");

            if (rows.length > 1) {

                button.parentElement.remove();

            } else {

                alert("At least one item is required.");

            }

        }

    </script>

</body>

</html>