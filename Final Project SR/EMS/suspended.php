<?php
require_once '../PARTS/background_worker.php';
require_once '../PARTS/config.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Account Suspended</title>
  <!-- CSS.PHP -->
  <?php
  require_once '../PARTS/CSS.php';
  ?>
  <style>
    body {
      background: linear-gradient(45deg, #FF416C, #FF4B2B);
      font-family: 'Roboto', sans-serif;
      color: #fff;
      text-align: center;
      overflow: hidden;
    }

    .container {
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
    }

    .alert {
      border-radius: 15px;
      box-shadow: 0px 0px 30px rgba(0, 0, 0, 0.4);
      background-color: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      padding: 50px;
      max-width: 80%;
    }

    .alert h1 {
      font-size: 3.5rem;
      margin-bottom: 30px;
    }

    .alert p {
      font-size: 1.2rem;
      margin-bottom: 20px;
    }

    .btn {
      font-size: 1.2rem;
      padding: 10px 30px;
      border-radius: 30px;
      transition: all 0.3s ease;
    }

    .btn:hover {
      transform: translateY(-5px);
      box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.4);
    }

    .btn-primary {
      background-color: #ff0033;
      border-color: #ff0033;
    }

    .btn-primary:hover {
      background-color: #ff0044;
      border-color: #ff0044;
    }

    .illustration {
      width: 80%;
      max-width: 500px;
      margin-bottom: 50px;
      animation: fade-in 1s ease-out;
    }

    .bounce {
      animation: bounce 1s infinite alternate;
    }

    @keyframes bounce {
      0% {
        transform: translateY(0);
      }
      100% {
        transform: translateY(-10px);
      }
    }

    @keyframes fade-in {
      from {
        opacity: 0;
        transform: translateY(-50px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes fade-in-overlay {
      from {
        opacity: 0;
      }
      to {
        opacity: 1;
      }
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

  </style>
</head>
<body>

<div class="container">
  <div class="alert text-center bounce">
    <img src="../SVG/exclamation-triangle-fill.svg" alt="Account Suspended" class="illustration">
    <h1>Uh-oh! Your Account Has Been Suspended</h1>
    <p>We're sorry, but your account has been temporarily suspended for violating our terms of service.</p>
    <p>Please contact support for further assistance or logout and try again later.</p>
    <form method="post">
        <button href="#" class="btn btn-primary btn-lg">Contact Support</button>
        <button type="submit" name="logout_EMS" class="btn btn-outline-light btn-lg">Logout</button>
    </form>
  </div>
</div>


<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
