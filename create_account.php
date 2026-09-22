<?php

include "db.php";

$message = "";
$messageType = "";

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

        /* -------------------------
           CREATE CUSTOMER
        ------------------------- */

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


        /* -------------------------
           CREATE ADDRESS
        ------------------------- */

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


        /* -------------------------
           COMMIT TRANSACTION
        ------------------------- */

        $conn->commit();

        $message = "Account and address created successfully!";
        $messageType = "success";

    } catch (Exception $e) {

        $conn->rollback();

        $message = "Error: " . $e->getMessage();
        $messageType = "error";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Create Account | FoodExpress</title>

    <style>

        /* =========================
           GENERAL PAGE
        ========================= */

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;

            font-family: Arial, Helvetica, sans-serif;

            background: #eee9e3;

            color: #222;
        }


        /* =========================
           MAIN CONTAINER
        ========================= */

        .page-container {
            width: 90%;
            max-width: 1200px;

            margin: 35px auto 50px auto;
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

            box-shadow: 0 5px 12px rgba(0, 0, 0, 0.08);

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
            margin: 0 0 5px 0;

            font-size: 32px;

            color: #222;
        }

        .header-text p {
            margin: 0;

            font-size: 17px;

            color: #6c6c6c;
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

            font-size: 15px;

            transition: 0.2s;
        }

        .back-button:hover {
            background: #c9151c;

            transform: translateY(-2px);

            box-shadow: 0 5px 10px rgba(237, 28, 36, 0.25);
        }


        /* =========================
           FORM CARD
        ========================= */

        .form-card {
            background: white;

            border-radius: 20px;

            padding: 35px 45px;

            box-shadow: 0 5px 14px rgba(0, 0, 0, 0.08);
        }


        /* =========================
           FORM INTRO
        ========================= */

        .form-intro {
            margin-bottom: 30px;
        }

        .form-intro h2 {
            margin: 0 0 8px 0;

            font-size: 27px;

            color: #222;
        }

        .form-intro p {
            margin: 0;

            color: #777;

            font-size: 15px;
        }


        /* =========================
           SECTION HEADINGS
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

            color: #222;
        }


        /* =========================
           FORM GRID
        ========================= */

        .form-grid {
            display: grid;

            grid-template-columns: repeat(2, 1fr);

            gap: 20px 25px;
        }

        .form-group {
            display: flex;

            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        label {
            font-size: 14px;

            font-weight: bold;

            color: #555;

            margin-bottom: 8px;
        }

        label span {
            color: #ed1c24;
        }


        /* =========================
           INPUTS
        ========================= */

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

        input:focus {
            border-color: #ed1c24;

            background: white;

            box-shadow: 0 0 0 3px rgba(237, 28, 36, 0.10);
        }

        input::placeholder {
            color: #aaa;
        }


        /* =========================
           MESSAGE
        ========================= */

        .message {
            padding: 14px 18px;

            border-radius: 10px;

            margin-bottom: 25px;

            font-size: 14px;

            font-weight: bold;
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
           ACTIONS
        ========================= */

        .form-actions {
            display: flex;

            justify-content: flex-end;

            align-items: center;

            gap: 15px;

            border-top: 1px solid #e5e5e5;

            padding-top: 25px;

            margin-top: 10px;
        }

        .cancel-button {
            text-decoration: none;

            color: #555;

            background: #f0f0f0;

            padding: 13px 24px;

            border-radius: 25px;

            font-weight: bold;

            transition: 0.2s;
        }

        .cancel-button:hover {
            background: #dedede;
        }

        .submit-button {
            border: none;

            cursor: pointer;

            background: #ed1c24;

            color: white;

            padding: 14px 28px;

            border-radius: 28px;

            font-size: 15px;

            font-weight: bold;

            box-shadow: 0 4px 8px rgba(237, 28, 36, 0.20);

            transition: 0.2s;
        }

        .submit-button:hover {
            background: #c9151c;

            transform: translateY(-2px);

            box-shadow: 0 6px 12px rgba(237, 28, 36, 0.25);
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
           RESPONSIVE DESIGN
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

            .header-text h1 {
                font-size: 27px;
            }

            .form-card {
                padding: 25px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-group.full-width {
                grid-column: span 1;
            }

            .form-actions {
                flex-direction: column-reverse;

                align-items: stretch;
            }

            .submit-button,
            .cancel-button {
                text-align: center;

                width: 100%;
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

                <h1>Create Account</h1>

                <p>
                    Join FoodExpress and start ordering your favourite food.
                </p>

            </div>

        </div>


        <a href="index.php" class="back-button">
            ← Back to Home
        </a>

    </header>



    <!-- =========================
         FORM
    ========================== -->

    <main class="form-card">


        <div class="form-intro">

            <h2>Customer Registration</h2>

            <p>
                Enter your details below to create your FoodExpress account.
            </p>

        </div>


        <?php if ($message != ""): ?>

            <div class="message <?php echo $messageType; ?>">

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php endif; ?>



        <form method="POST">


            <!-- =========================
                 CUSTOMER DETAILS
            ========================== -->

            <section class="section">

                <div class="section-heading">

                    <h3>Customer Details</h3>

                </div>


                <div class="form-grid">


                    <div class="form-group">

                        <label>
                            Full Name <span>*</span>
                        </label>

                        <input
                            type="text"
                            name="name"
                            placeholder="Enter your full name"
                            required
                        >

                    </div>



                    <div class="form-group">

                        <label>
                            Email Address <span>*</span>
                        </label>

                        <input
                            type="email"
                            name="email"
                            placeholder="example@email.com"
                            required
                        >

                    </div>



                    <div class="form-group">

                        <label>
                            Phone Number
                        </label>

                        <input
                            type="text"
                            name="phone"
                            placeholder="Enter your phone number"
                        >

                    </div>



                    <div class="form-group">

                        <label>
                            Password <span>*</span>
                        </label>

                        <input
                            type="password"
                            name="password"
                            placeholder="Create a password"
                            required
                        >

                    </div>


                </div>

            </section>



            <!-- =========================
                 ADDRESS DETAILS
            ========================== -->

            <section class="section">

                <div class="section-heading">

                    <h3>Delivery Address</h3>

                </div>


                <div class="form-grid">


                    <div class="form-group">

                        <label>
                            House Number <span>*</span>
                        </label>

                        <input
                            type="text"
                            name="house_no"
                            placeholder="e.g. 12A"
                            required
                        >

                    </div>



                    <div class="form-group">

                        <label>
                            Area / Street <span>*</span>
                        </label>

                        <input
                            type="text"
                            name="area_street"
                            placeholder="Enter your area or street"
                            required
                        >

                    </div>



                    <div class="form-group full-width">

                        <label>
                            Landmark
                        </label>

                        <input
                            type="text"
                            name="landmark"
                            placeholder="Nearby landmark (optional)"
                        >

                    </div>



                    <div class="form-group">

                        <label>
                            City <span>*</span>
                        </label>

                        <input
                            type="text"
                            name="city"
                            placeholder="Enter your city"
                            required
                        >

                    </div>



                    <div class="form-group">

                        <label>
                            State <span>*</span>
                        </label>

                        <input
                            type="text"
                            name="state"
                            placeholder="Enter your state"
                            required
                        >

                    </div>



                    <div class="form-group">

                        <label>
                            PIN Code <span>*</span>
                        </label>

                        <input
                            type="text"
                            name="pincode"
                            placeholder="Enter PIN code"
                            required
                        >

                    </div>


                </div>

            </section>



            <!-- =========================
                 BUTTONS
            ========================== -->

            <div class="form-actions">

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
                    Create Account
                </button>

            </div>


        </form>

    </main>



    <div class="footer">

        FoodExpress &nbsp;•&nbsp; Manage customers, orders and deliveries from one place.

    </div>


</div>

</body>

</html>