<?php
session_start();
$conn = new mysqli("localhost", "root", "", "kursov_proektbd");
$conn->set_charset("utf8");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=checkout.php");
    exit();
}

$shipping_fee = 0;
$checkout_total = 0;

// Изчисляване на сумата на продуктите
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $item_total = $item['price'] * $item['quantity'];
        $checkout_total += $item_total;
    }
}

// Определяне на таксата за доставка според метода и стойността
if (isset($_POST['shipping_method'])) {
    $method = $_POST['shipping_method'];

    if ($checkout_total < 100) {
        if ($method === 'pickup') {
            $shipping_fee = 4.99;
        } elseif ($method === 'home') {
            $shipping_fee = 6.99;
        }
        // store -> без такса
    }
}

$grand_total = $checkout_total + $shipping_fee;
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Confirm Order</title>
    <header>
        <div class="header-container">
            <div class="logo">
                <a href="index.php"><h1>Dressify</h1></a>
            </div>
            <div class="nav-icons">
                <a href="index.php"><img src="images/home.png" alt="Home"></a>
                <a href="cart.php"><img src="images/shoppingcart.png" alt="Cart"></a>
                <a href="user_redirect.php"><img src="images/profile_picture.png" alt="Профил" class="login-icon"></a>
            </div>
        </div>
    </header>
    <link rel="stylesheet" href="nachalna_stranica.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: white;
            margin: 0;
            padding: 0;
        }

        .order-confirmation {
            max-width: 700px;
            margin: 40px auto;
            background-color: #fff0f5;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.1);
        }

        .order-confirmation h2 {
            text-align: center;
            color: #ff4d79;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        label {
            font-weight: bold;
            color: #333;
        }

        select, input[type="text"], input[type="number"], input[type="month"] {
            padding: 10px;
            border: 1px solid #ff99b3;
            border-radius: 8px;
            font-size: 16px;
            width: 100%;
        }

        .section {
            background-color: #ffe6ec;
            padding: 20px;
            border-radius: 12px;
        }

        .shipping-options label,
        .payment-options label {
            display: block;
            margin-bottom: 8px;
        }

        .shipping-details,
        .card-details {
            display: none;
        }

        .shipping-details.active,
        .card-details.active {
            display: block;
        }

        button {
            padding: 12px;
            background-color: #ff80a1;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #ff4d79;
        }
    </style>
</head>
<body>

<div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 30px; margin: 40px auto; max-width: 1200px; padding: 20px;">
    <!-- Cart with products -->
    <div class="cart-summary" style="flex: 1; min-width: 300px; background-color: #fff0f5; padding: 20px; border-radius: 15px; box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.1);">
        <h2 style="text-align: center; color: #ff4d79; margin-bottom: 20px;">Your Items</h2>

        <?php if (!empty($_SESSION['cart'])): ?>
            <table style="width: 100%; border-collapse: collapse;">
                <tr style="background-color: #ff80a1; color: white;">
                    <th style="padding: 10px;">Image</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
                <?php foreach ($_SESSION['cart'] as $item): 
                    $item_total = $item['price'] * $item['quantity'];
                ?>
                    <tr style="text-align: center; background-color: #fff;">
                        <td style="padding: 8px;">
                            <?php if (!empty($item['image'])): ?>
                                <img src="<?= htmlspecialchars($item['image']) ?>" alt="Product Image" style="max-width: 60px; max-height: 60px; border-radius: 8px;">
                            <?php else: ?>
                                <span>No image</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td><?= number_format($item['price'], 2) ?> BGN</td>
                        <td><?= number_format($item_total, 2) ?> BGN</td>
                    </tr>
                <?php endforeach; ?>
                <tr style="font-weight: bold; background-color: #ffe6ec;">
                    <td colspan="4" style="text-align: right; padding: 10px;">Total:</td>
                    <td><?= number_format($checkout_total, 2) ?> BGN</td>
                </tr>
                <tr style="font-weight: bold; background-color: #ffe6ec;">
    <td colspan="4" style="text-align: right; padding: 10px;">Shipping Fee:</td>
    <td id="shipping-fee"><?= number_format($shipping_fee, 2) ?> BGN</td>
</tr>
<tr style="font-weight: bold; background-color: #ffe6ec;">
    <td colspan="4" style="text-align: right; padding: 10px;">Grand Total:</td>
    <td id="grand-total"><?= number_format($grand_total, 2) ?> BGN</td>
</tr>
<script>
    const checkoutTotal = <?= $checkout_total ?>;
</script>

            </table>
        <?php else: ?>
            <p>The cart is empty.</p>
        <?php endif; ?>
    </div>

    <!-- Order form -->
    <div class="order-confirmation" style="flex: 1; min-width: 350px; background-color: #fff0f5; padding: 30px; border-radius: 15px; box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.1);">
        <h2 style="text-align: center; color: #ff4d79; margin-bottom: 20px;">Confirm Order</h2>
        <form action="process_order.php" method="post">
            <!-- Shipping -->
            <div class="section">
                <h3>Shipping Method</h3>
                <div class="shipping-options">
                    <label><input type="radio" name="shipping_method" value="store" required> Pick up from store</label>
                    <label><input type="radio" name="shipping_method" value="pickup"> Delivery point (Speedy office)</label>
                    <label><input type="radio" name="shipping_method" value="home"> Home delivery</label>
                </div>

                <div id="store-addresses" class="shipping-details">
                    <label>Select a store:</label>
                    <select name="store_location">
                        <option value="Grand Mall Varna">Grand Mall Varna</option>
                        <option value="Serdika Mall Sofia">Serdika Mall Sofia</option>
                        <option value="Plaza Mall Plovdiv">Plaza Mall Plovdiv</option>
                    </select>
                </div>

                <div id="pickup-addresses" class="shipping-details">
                    <label>Select Speedy office:</label>
                    <select name="pickup_location">
                        <option value="Speedy Office 1 - Bulgaria Blvd 55, Sofia">Bulgaria Blvd 55, Sofia</option>
                        <option value="Speedy Office 2 - Vardar St 15, Plovdiv">Vardar St 15, Plovdiv</option>
                        <option value="Speedy Office 3 - Tsar Osvoboditel 7, Varna">Tsar Osvoboditel 7, Varna</option>
                        <option value="Speedy Office 4 - Rakovski 100, Burgas">Rakovski 100, Burgas</option>
                        <option value="Speedy Office 5 - Dunav 22, Ruse">Dunav 22, Ruse</option>
                    </select>
                </div>

                <div id="home-address" class="shipping-details">
                    <label>Enter delivery address:</label>
                    <input type="text" name="home_address" placeholder="Street, number, entrance, floor, apartment">
                </div>
            </div>

            <!-- Payment -->
            <div class="section">
                <h3>Payment Method</h3>
                <div class="payment-options">
                    <label><input type="radio" name="payment_method" value="Cash on Delivery" required> Cash on delivery</label>
                    <label><input type="radio" name="payment_method" value="Credit/Debit card"> Pay by card</label>
                </div>

                <div id="card-details" class="card-details">
                    <label>Card number:</label>
                    <input type="text" name="card_number" pattern="\d{16}" placeholder="1234 5678 9012 3456">

                    <label>Valid until:</label>
                    <input type="month" name="card_expiry">

                    <label>CVV:</label>
                    <input type="number" name="card_cvv" min="100" max="999" placeholder="123">
                </div>
            </div>

            <button type="submit">Complete Order</button>
        </form>
    </div>
</div>

<script>
    const shippingRadios = document.querySelectorAll('input[name="shipping_method"]');
    const store = document.getElementById('store-addresses');
    const pickup = document.getElementById('pickup-addresses');
    const home = document.getElementById('home-address');

    shippingRadios.forEach(radio => {
        radio.addEventListener('change', () => {
            store.classList.remove('active');
            pickup.classList.remove('active');
            home.classList.remove('active');

            if (radio.value === 'store') store.classList.add('active');
            else if (radio.value === 'pickup') pickup.classList.add('active');
            else if (radio.value === 'home') home.classList.add('active');

            updateShipping(); // обнови таксата!
        });
    });

    const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
    const cardDetails = document.getElementById('card-details');

    paymentRadios.forEach(radio => {
        radio.addEventListener('change', () => {
            if (radio.value === 'Credit/Debit card') {
                cardDetails.classList.add('active');
            } else {
                cardDetails.classList.remove('active');
            }
        });
    });

    const shippingFeeEl = document.getElementById('shipping-fee');
    const grandTotalEl = document.getElementById('grand-total');

    function updateShipping() {
        let fee = 0;
        const method = document.querySelector('input[name="shipping_method"]:checked')?.value;

        if (checkoutTotal < 100) {
            if (method === 'pickup') {
                fee = 4.99;
            } else if (method === 'home') {
                fee = 6.99;
            }
        }

        const grandTotal = checkoutTotal + fee;

        shippingFeeEl.textContent = fee.toFixed(2) + ' BGN';
        grandTotalEl.textContent = grandTotal.toFixed(2) + ' BGN';
    }

    document.querySelector('form').addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('process_order.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.text())
        .then(text => {
            const message = document.createElement('div');
            message.style.padding = '20px';
            message.style.marginTop = '20px';
            message.style.textAlign = 'center';
            message.style.fontSize = '18px';
            message.style.borderRadius = '10px';
            message.style.backgroundColor = '#d4edda';
            message.style.color = '#155724';
            message.innerText = text;

            document.querySelector('.order-confirmation').appendChild(message);

            // Обнови стойностите първо
            shippingFeeEl.textContent = '0.00 BGN';
            grandTotalEl.textContent = '0.00 BGN';

            // Изчисти формата и количката
            document.querySelector('form').reset();
            document.querySelector('.cart-summary').innerHTML = '<p style="text-align:center;">Вашата количка е празна.</p>';
        })
        .catch(err => {
            alert('Възникна грешка. Вижте конзолата.');
            console.error(err);
        });
    });
</script>


</body>
</html>
