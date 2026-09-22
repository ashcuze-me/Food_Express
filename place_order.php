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
$messageType = "";

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
            $messageType = "error";
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

            $messageType = "success";

        } catch (Exception $e) {

            $conn->rollback();

            $message = "Order failed.";
            $messageType = "error";
        }

    } elseif ($message == "") {

        $message = "Please select at least one menu item.";
        $messageType = "error";
    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Place Order | FoodExpress</title>


    <style>

        * {
            box-sizing: border-box;
        }

        body {

            margin: 0;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background: #eee9e3;

            color: #222;
        }


        /* =========================
           PAGE
        ========================= */

        .page-container {

            width: 90%;

            max-width: 1200px;

            margin: 35px auto 50px;
        }


        /* =========================
           HEADER
        ========================= */

        .header {

            background: white;

            border-top: 5px solid #ed1c24;

            border-radius: 20px;

            padding: 20px 35px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            box-shadow:
                0 5px 12px rgba(0,0,0,0.08);

            margin-bottom: 25px;
        }

        .brand-section {

            display: flex;

            align-items: center;

            gap: 25px;
        }

        .logo {

            width: 150px;

            height: auto;

            object-fit: contain;
        }

        .header-text h1 {

            margin: 0 0 5px;

            font-size: 32px;
        }

        .header-text p {

            margin: 0;

            color: #6c6c6c;

            font-size: 17px;
        }


        /* =========================
           BACK BUTTON
        ========================= */

        .back-button {

            text-decoration: none;

            background: #ed1c24;

            color: white;

            padding: 14px 24px;

            border-radius: 30px;

            font-weight: bold;

            transition: 0.2s;
        }

        .back-button:hover {

            background: #c9151c;

            transform: translateY(-2px);

            box-shadow:
                0 5px 10px
                rgba(237,28,36,0.25);
        }


        /* =========================
           ORDER CARD
        ========================= */

        .order-card {

            background: white;

            border-radius: 20px;

            padding: 35px 45px;

            box-shadow:
                0 5px 14px rgba(0,0,0,0.08);
        }


        .intro {

            margin-bottom: 30px;
        }

        .intro h2 {

            margin: 0 0 8px;

            font-size: 27px;
        }

        .intro p {

            margin: 0;

            color: #777;

            font-size: 15px;
        }


        /* =========================
           MESSAGE
        ========================= */

        .message {

            padding: 15px 18px;

            border-radius: 10px;

            margin-bottom: 25px;

            font-weight: bold;

            font-size: 14px;
        }

        .message.success {

            background: #e9f9ef;

            color: #168544;

            border: 1px solid #a8e5bd;
        }

        .message.error {

            background: #fff0f0;

            color: #d71920;

            border: 1px solid #f1b1b1;
        }


        /* =========================
           SECTION
        ========================= */

        .section {

            margin-bottom: 35px;
        }

        .section-heading {

            display: flex;

            align-items: center;

            gap: 12px;

            margin-bottom: 20px;

            padding-bottom: 10px;

            border-bottom: 1px solid #e5e5e5;
        }

        .section-heading::before {

            content: "";

            width: 6px;

            height: 30px;

            background: #ed1c24;

            border-radius: 5px;
        }

        .section-heading h3 {

            margin: 0;

            font-size: 20px;
        }


        /* =========================
           GRID
        ========================= */

        .form-grid {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 20px 25px;
        }

        .form-group {

            display: flex;

            flex-direction: column;
        }

        .full-width {

            grid-column: span 2;
        }


        /* =========================
           LABELS
        ========================= */

        label {

            font-size: 14px;

            font-weight: bold;

            color: #555;

            margin-bottom: 8px;
        }


        /* =========================
           SELECTS + INPUTS
        ========================= */

        select,
        input {

            width: 100%;

            padding: 13px 15px;

            border: 1px solid #d6d6d6;

            border-radius: 10px;

            font-size: 15px;

            color: #333;

            background: #fafafa;

            outline: none;

            transition: 0.2s;
        }

        select:focus,
        input:focus {

            border-color: #ed1c24;

            background: white;

            box-shadow:
                0 0 0 3px
                rgba(237,28,36,0.10);
        }

        select:disabled {

            background: #eeeeee;

            color: #999;

            cursor: not-allowed;
        }


        /* =========================
           ITEMS
        ========================= */

        .items-container {

            display: flex;

            flex-direction: column;

            gap: 15px;
        }

        .item-row {

            background: #fafafa;

            border: 1px solid #e4e4e4;

            border-radius: 14px;

            padding: 18px;

            display: grid;

            grid-template-columns:
                1fr 150px auto;

            gap: 15px;

            align-items: end;

            transition: 0.2s;
        }

        .item-row:hover {

            border-color: #ed1c24;

            box-shadow:
                0 3px 10px
                rgba(0,0,0,0.05);
        }

        .item-field {

            display: flex;

            flex-direction: column;
        }

        .item-field label {

            margin-bottom: 7px;
        }


        /* =========================
           REMOVE BUTTON
        ========================= */

        .remove-button {

            border: none;

            background: #f2dede;

            color: #c62828;

            padding: 12px 18px;

            border-radius: 22px;

            font-weight: bold;

            cursor: pointer;

            transition: 0.2s;
        }

        .remove-button:hover {

            background: #c62828;

            color: white;
        }


        /* =========================
           ADD ITEM
        ========================= */

        .add-button {

            margin-top: 15px;

            border: 2px dashed #ed1c24;

            background: white;

            color: #ed1c24;

            padding: 12px 20px;

            border-radius: 25px;

            font-weight: bold;

            cursor: pointer;

            transition: 0.2s;
        }

        .add-button:hover {

            background: #fff1f1;
        }


        /* =========================
           PAYMENT
        ========================= */

        .payment-box {

            background: #fafafa;

            border: 1px solid #e5e5e5;

            border-radius: 14px;

            padding: 20px;
        }


        /* =========================
           ACTIONS
        ========================= */

        .actions {

            border-top: 1px solid #e5e5e5;

            padding-top: 25px;

            margin-top: 10px;

            display: flex;

            justify-content: flex-end;

            gap: 15px;
        }

        .cancel-button {

            text-decoration: none;

            color: #555;

            background: #eeeeee;

            padding: 14px 25px;

            border-radius: 28px;

            font-weight: bold;

            transition: 0.2s;
        }

        .cancel-button:hover {

            background: #dddddd;
        }

        .submit-button {

            border: none;

            background: #ed1c24;

            color: white;

            padding: 14px 30px;

            border-radius: 28px;

            font-size: 15px;

            font-weight: bold;

            cursor: pointer;

            box-shadow:
                0 4px 8px
                rgba(237,28,36,0.20);

            transition: 0.2s;
        }

        .submit-button:hover {

            background: #c9151c;

            transform: translateY(-2px);

            box-shadow:
                0 6px 12px
                rgba(237,28,36,0.25);
        }


        /* =========================
           FOOTER
        ========================= */

        .footer {

            text-align: center;

            margin-top: 25px;

            color: #888;

            font-size: 13px;
        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 800px) {

            .page-container {

                width: 94%;

                margin-top: 20px;
            }

            .header {

                flex-direction: column;

                gap: 20px;

                text-align: center;

                padding: 25px;
            }

            .brand-section {

                flex-direction: column;

                gap: 10px;
            }

            .logo {

                width: 130px;
            }

            .order-card {

                padding: 25px;
            }

            .form-grid {

                grid-template-columns: 1fr;
            }

            .full-width {

                grid-column: span 1;
            }

            .item-row {

                grid-template-columns: 1fr;
            }

            .actions {

                flex-direction: column-reverse;
            }

            .submit-button,
            .cancel-button {

                width: 100%;

                text-align: center;
            }
        }

    </style>

</head>


<body>


<div class="page-container">


    <!-- =========================
         HEADER
    ========================== -->

    <header class="header">

        <div class="brand-section">

            <img
                src="logo.png"
                alt="FoodExpress Logo"
                class="logo"
            >

            <div class="header-text">

                <h1>Place an Order</h1>

                <p>
                    Select your customer, restaurant and favourite dishes.
                </p>

            </div>

        </div>


        <a href="index.php" class="back-button">
            ← Back to Home
        </a>

    </header>



    <!-- =========================
         ORDER FORM
    ========================== -->

    <main class="order-card">


        <div class="intro">

            <h2>New Food Order</h2>

            <p>
                Fill in the details below to create and place a new order.
            </p>

        </div>



        <?php if ($message != ""): ?>

            <div class="message <?php echo $messageType; ?>">

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php endif; ?>



        <form method="POST">


            <!-- =========================
                 CUSTOMER INFORMATION
            ========================== -->

            <section class="section">

                <div class="section-heading">

                    <h3>Customer & Delivery</h3>

                </div>


                <div class="form-grid">


                    <div class="form-group">

                        <label>
                            Customer
                        </label>

                        <select
                            name="customer_id"
                            id="customer_id"
                            required
                        >

                            <option value="">
                                Select Customer
                            </option>

                            <?php while ($row = $customers->fetch_assoc()) { ?>

                                <option
                                    value="<?php echo $row["CustomerID"]; ?>"
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $row["FullName"]
                                    );
                                    ?>

                                </option>

                            <?php } ?>

                        </select>

                    </div>



                    <div class="form-group">

                        <label>
                            Delivery Address
                        </label>

                        <select
                            id="address_display"
                            disabled
                        >

                            <option value="">
                                Select Customer First
                            </option>

                            <?php while ($row = $addresses->fetch_assoc()) { ?>

                                <option
                                    value="<?php echo $row["AddressID"]; ?>"
                                    data-customer="<?php echo $row["CustomerID"]; ?>"
                                >

                                    <?php

                                    echo htmlspecialchars(
                                        $row["HouseNo"] .
                                        ", " .
                                        $row["AreaStreet"] .
                                        ", " .
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

                    </div>


                </div>

            </section>



            <!-- =========================
                 RESTAURANT
            ========================== -->

            <section class="section">

                <div class="section-heading">

                    <h3>Restaurant</h3>

                </div>


                <div class="form-group">

                    <label>
                        Select Restaurant
                    </label>

                    <select
                        name="restaurant_id"
                        id="restaurant_id"
                        required
                    >

                        <option value="">
                            Select Restaurant
                        </option>

                        <?php while ($row = $restaurants->fetch_assoc()) { ?>

                            <option
                                value="<?php echo $row["RestaurantID"]; ?>"
                            >

                                <?php
                                echo htmlspecialchars(
                                    $row["RestaurantName"]
                                );
                                ?>

                            </option>

                        <?php } ?>

                    </select>

                </div>

            </section>



            <!-- =========================
                 MENU ITEMS
            ========================== -->

            <section class="section">

                <div class="section-heading">

                    <h3>Order Items</h3>

                </div>


                <div id="items_container" class="items-container">


                    <div class="item-row">


                        <div class="item-field">

                            <label>
                                Menu Item
                            </label>

                            <select
                                name="menu_item_id[]"
                                class="menu_item"
                                required
                            >

                                <option value="">
                                    Select Item
                                </option>

                                <?php

                                $menuItems->data_seek(0);

                                while (
                                    $row =
                                    $menuItems->fetch_assoc()
                                ) {

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

                        </div>



                        <div class="item-field">

                            <label>
                                Quantity
                            </label>

                            <input
                                type="number"
                                name="quantity[]"
                                min="1"
                                value="1"
                                required
                            >

                        </div>



                        <button
                            type="button"
                            class="remove-button"
                            onclick="removeItem(this)"
                        >
                            Remove
                        </button>


                    </div>


                </div>


                <button
                    type="button"
                    class="add-button"
                    onclick="addItem()"
                >
                    + Add Another Item
                </button>


            </section>



            <!-- =========================
                 PAYMENT
            ========================== -->

            <section class="section">

                <div class="section-heading">

                    <h3>Payment</h3>

                </div>


                <div class="payment-box">

                    <div class="form-group">

                        <label>
                            Payment Method
                        </label>

                        <select
                            name="payment_method"
                            required
                        >

                            <option value="">
                                Select Payment Method
                            </option>

                            <option value="Online Payment">
                                Online Payment
                            </option>

                            <option value="Cash on Delivery">
                                Cash on Delivery
                            </option>

                        </select>

                    </div>

                </div>

            </section>



            <!-- =========================
                 ACTIONS
            ========================== -->

            <div class="actions">

                <a
                    href="index.php"
                    class="cancel-button"
                >
                    Cancel
                </a>


                <button
                    type="submit"
                    class="submit-button"
                >
                    Place Order
                </button>

            </div>


        </form>

    </main>



    <div class="footer">

        FoodExpress
        &nbsp;•&nbsp;
        Manage customers, orders and deliveries from one place.

    </div>


</div>



<script>


    /* =========================
       ELEMENTS
    ========================== */

    const restaurantSelect =
        document.getElementById("restaurant_id");

    const customerSelect =
        document.getElementById("customer_id");

    const addressDisplay =
        document.getElementById("address_display");

    const addressID =
        document.getElementById("address_id");



    /* =========================
       RESTAURANT → MENU ITEMS
    ========================== */

    restaurantSelect.addEventListener(
        "change",
        function () {

            const restaurantID = this.value;

            const menuSelects =
                document.querySelectorAll(".menu_item");


            menuSelects.forEach(
                function (menuSelect) {

                    for (
                        let i = 0;
                        i < menuSelect.options.length;
                        i++
                    ) {

                        const option =
                            menuSelect.options[i];


                        if (option.value === "") {

                            option.hidden = false;

                        } else {

                            option.hidden =
                                option.dataset.restaurant !==
                                restaurantID;
                        }
                    }


                    menuSelect.value = "";

                }
            );

        }
    );



    /* =========================
       CUSTOMER → ADDRESS
    ========================== */

    customerSelect.addEventListener(
        "change",
        function () {

            const customerID = this.value;

            addressDisplay.value = "";

            addressID.value = "";


            if (customerID === "") {

                addressDisplay.disabled = true;

                return;
            }


            addressDisplay.disabled = false;


            for (
                let i = 0;
                i < addressDisplay.options.length;
                i++
            ) {

                const option =
                    addressDisplay.options[i];


                if (option.value === "") {

                    option.hidden = false;

                } else {

                    option.hidden =
                        option.dataset.customer !==
                        customerID;
                }
            }


            for (
                let i = 0;
                i < addressDisplay.options.length;
                i++
            ) {

                const option =
                    addressDisplay.options[i];


                if (
                    option.dataset.customer ===
                    customerID
                ) {

                    addressDisplay.value =
                        option.value;

                    addressID.value =
                        option.value;

                    break;
                }
            }

        }
    );



    /* =========================
       ADD ITEM
    ========================== */

    function addItem() {

        const container =
            document.getElementById(
                "items_container"
            );

        const firstRow =
            document.querySelector(
                ".item-row"
            );

        const newRow =
            firstRow.cloneNode(true);


        const newMenuSelect =
            newRow.querySelector(
                ".menu_item"
            );


        newMenuSelect.value = "";


        newRow.querySelector(
            "input"
        ).value = 1;


        const restaurantID =
            restaurantSelect.value;


        for (
            let i = 0;
            i < newMenuSelect.options.length;
            i++
        ) {

            const option =
                newMenuSelect.options[i];


            if (option.value === "") {

                option.hidden = false;

            } else {

                option.hidden =
                    option.dataset.restaurant !==
                    restaurantID;
            }
        }


        container.appendChild(newRow);
    }



    /* =========================
       REMOVE ITEM
    ========================== */

    function removeItem(button) {

        const rows =
            document.querySelectorAll(
                ".item-row"
            );


        if (rows.length > 1) {

            button
                .parentElement
                .remove();

        } else {

            alert(
                "At least one item is required."
            );
        }
    }


</script>


</body>

</html>