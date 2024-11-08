<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crypto Payment Page</title>
    <style>
        /* Basic Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
        }

        .container {
            width: 90%;
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h1 {
            font-size: 1.8rem;
            color: #333;
            text-align: center;
            margin-bottom: 1rem;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
        }

        .back-link {
            color: #0066cc;
            text-decoration: none;
            font-size: 0.9rem;
        }

        /* Payment Summary */
        .summary {
            border-bottom: 1px solid #ddd;
            padding-bottom: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .summary h2 {
            font-size: 1.4rem;
            margin-bottom: 0.5rem;
        }

        .summary p {
            font-size: 1rem;
            color: #555;
        }

        /* Choose Cryptocurrency */
        .crypto-options {
            display: flex;
            justify-content: space-around;
            margin-bottom: 1.5rem;
        }

        .crypto-option {
            padding: 0.5rem 1rem;
            border: 2px solid #ddd;
            border-radius: 6px;
            cursor: pointer;
            text-align: center;
        }

        .crypto-option.selected {
            border-color: #0066cc;
            background-color: #e6f0ff;
        }

        /* Payment Instructions */
        .instructions {
            border-top: 1px solid #ddd;
            padding-top: 1.5rem;
            margin-top: 1.5rem;
        }

        .instructions h3 {
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }

        .instructions p {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 1rem;
        }

        /* Wallet Address and QR Code */
        .wallet {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f4f4f9;
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
        }

        .wallet input {
            border: none;
            background: transparent;
            color: #333;
            width: 100%;
            margin-right: 10px;
            font-size: 0.9rem;
        }

        .wallet button {
            background: #0066cc;
            color: #ffffff;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        /* Timer and Status */
        .status {
            text-align: center;
            margin-top: 1rem;
        }

        .timer {
            font-size: 1.2rem;
            color: #ff3333;
            font-weight: bold;
        }

        /* Footer */
        .footer {
            margin-top: 2rem;
            text-align: center;
            font-size: 0.8rem;
            color: #777;
        }

        .footer a {
            color: #0066cc;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Header -->
    <div class="header">
        <div class="logo">YourCompany</div>
        <a href="#" class="back-link">Back to Homepage</a>
    </div>

    <!-- Page Title -->
    <h1>Complete Payment</h1>

    <!-- Payment Summary -->
    <div class="summary">
        <h2>Order Summary</h2>
        <p>Item: Premium Service Package</p>
        <p>Total Amount: <strong>$200 USD</strong></p>
        <p>Transaction ID: #123456</p>
    </div>

    <!-- Choose Cryptocurrency -->
    <div class="crypto-options">
        <div class="crypto-option" onclick="selectCrypto('btc')">
            Bitcoin (BTC)
        </div>
        <div class="crypto-option" onclick="selectCrypto('eth')">
            Ethereum (ETH)
        </div>
        <div class="crypto-option" onclick="selectCrypto('usdt')">
            Tether (USDT)
        </div>
    </div>

    <!-- Payment Instructions -->
    <div class="instructions">
        <h3>Payment Instructions</h3>
        <p>Send the exact amount to the wallet address shown below. Your order will be processed once the payment is confirmed.</p>

        <!-- Wallet Address and QR Code -->
        <div class="wallet">
            <input type="text" id="walletAddress" value="Wallet address will appear here" readonly>
            <button onclick="copyAddress()">Copy</button>
        </div>

        <p>Amount to Send: <strong id="amount">0.0045 BTC</strong></p>
        <p>Time Remaining: <span class="timer" id="timer">15:00</span></p>
    </div>

    <!-- Transaction Status -->
    <div class="status">
        <p>Status: <span id="statusMessage">Awaiting Payment</span></p>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Crypto payments are non-reversible. <a href="#">Terms & Conditions</a> | <a href="#">Privacy Policy</a></p>
    </div>
</div>

<script>
    function selectCrypto(crypto) {
        document.querySelectorAll('.crypto-option').forEach(option => option.classList.remove('selected'));
        document.querySelector(`.crypto-option[onclick="selectCrypto('${crypto}')"]`).classList.add('selected');
        document.getElementById('walletAddress').value = crypto === 'btc' ? 'BTC Wallet Address' : crypto === 'eth' ? 'ETH Wallet Address' : 'USDT Wallet Address';
        document.getElementById('amount').textContent = crypto === 'btc' ? '0.0045 BTC' : crypto === 'eth' ? '0.07 ETH' : '200 USDT';
    }

    function copyAddress() {
        const walletAddress = document.getElementById("walletAddress");
        walletAddress.select();
        document.execCommand("copy");
        alert("Wallet address copied to clipboard!");
    }
</script>

</body>
</html>
