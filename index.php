<?php

session_start();
if(isset($_SESSION['user_id'])){
    header("Location: dashboard.php");
    exit();
}

if (isset($_SESSION['errors'])) {
    $errors = $_SESSION['errors'];
    unset($_SESSION['errors']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Integrated Healthcare Management System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
  <style>
    /* Additional styles for the login form */
    .login-section {
      background: rgba(255, 255, 255, 0.95);
      padding: 2rem;
      border-radius: 1rem;
      box-shadow: 0 10px 30px rgba(44, 107, 237, 0.1);
      position: relative;
      z-index: 100;
      max-width: 400px;
      margin: 0 auto;
    }
    .input-group {
      position: relative;
      margin-bottom: 1.5rem;
    }
    .input-group input {
      width: 100%;
      padding: 0.75rem 2.5rem;
      border: 2px solid var(--primary-color);
      border-radius: 0.5rem;
      outline: none;
      transition: all 0.3s ease;
    }
    .input-group i {
      position: absolute;
      left: 1rem;
      top: 50%;
      transform: translateY(-50%);
      color: var(--primary-color);
    }
    .input-group i.fa-eye {
      left: auto;
      right: 1rem;
      cursor: pointer;
    }
    .recover {
      text-align: right;
      margin-bottom: 1rem;
    }
    .recover a {
      color: var(--primary-color);
      text-decoration: none;
    }
    .or {
      text-align: center;
      margin: 1rem 0;
      color: var(--secondary-color);
    }
    .icons {
      display: flex;
      justify-content: center;
      gap: 1rem;
      margin-bottom: 1rem;
    }
    .icons i {
      font-size: 1.5rem;
      color: var(--primary-color);
      cursor: pointer;
      transition: transform 0.3s ease;
    }
    .icons i:hover {
      transform: scale(1.1);
    }
    .links {
      text-align: center;
    }
    .links a {
      color: var(--primary-color);
      text-decoration: none;
      font-weight: 600;
    }
    .error {
      color: #dc3545;
      font-size: 0.875rem;
      margin-top: 0.25rem;
    }
  </style>
</head>

<body>
  <!-- Navigation Bar -->
  <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
    <div class="container">
      <a class="navbar-brand" href="#">IHMS</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="nav-link" href="#home">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#about">About</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="#login">Login</a>
          </li>
          <li class="nav-item">
            <a class="nav-link btn btn-primary text-white px-4" href="register.php">Sign Up</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <header id="home" class="hero">
    <div class="design-element design-circle-1"></div>
    <div class="design-element design-circle-2"></div>
    <div class="design-element design-square"></div>
    <div class="design-element design-dots"></div>
    
    <div class="container h-100">
      <div class="row h-100 align-items-center">
        <div class="col-lg-6 text-center text-lg-start animate__animated animate__fadeInLeft">
          <h1 class="display-4 fw-bold text-gradient mb-4">Integrated Healthcare Management System</h1>
          <p class="lead mb-4">Revolutionizing healthcare management with advanced technology and seamless integration. Experience the future of healthcare today.</p>
        </div>
        <div class="col-lg-6 animate__animated animate__fadeInRight">
          <div class="login-section" id="login">
            <h2 class="text-center mb-4">Sign In</h2>
            <?php if (isset($errors['login'])): ?>
              <div class="alert alert-danger">
                <p><?php echo $errors['login']; ?></p>
              </div>
            <?php endif; ?>
            <form method="POST" action="user-account.php">
              <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" id="email" placeholder="Email" autocomplete="email" required>
                <?php if (isset($errors['email'])): ?>
                  <div class="error">
                    <p><?php echo $errors['email']; ?></p>
                  </div>
                <?php endif; ?>
              </div>
              <div class="input-group password">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" id="password" placeholder="Password" autocomplete="current-password" required>
                <i id="eye" class="fa fa-eye"></i>
                <?php if (isset($errors['password'])): ?>
                  <div class="error">
                    <p><?php echo $errors['password']; ?></p>
                  </div>
                <?php endif; ?>
              </div>
              <p class="recover">
                <a href="#">Recover Password</a>
              </p>
              <button type="submit" class="btn btn-primary w-100" name="signin">Sign In</button>
            </form>
            <p class="or">
              ----------or--------
            </p>
            <div class="icons">
              <i class="fab fa-google"></i>
              <i class="fab fa-facebook"></i>
            </div>
            <div class="links">
              <p>Don't have account yet?</p>
              <a href="register.php">Sign Up</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- About Section -->
  <section id="about" class="about-section">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-8 text-center mb-5">
          <h2 class="section-title">Why Choose Us?</h2>
          <p class="section-subtitle">Transforming Healthcare Management with Innovation</p>
        </div>
      </div>
      <div class="row g-4">
        <div class="col-md-6 col-lg-3">
          <div class="feature-card">
            <div class="icon-wrapper">
              <i class="bi bi-shield-check"></i>
            </div>
            <h3>Secure & Reliable</h3>
            <p>Advanced security measures to protect sensitive medical data and ensure privacy compliance.</p>
          </div>
        </div>
        <div class="col-md-6 col-lg-3">
          <div class="feature-card">
            <div class="icon-wrapper">
              <i class="bi bi-lightning-charge"></i>
            </div>
            <h3>Fast & Efficient</h3>
            <p>Streamlined processes for quick access to patient information and medical records.</p>
          </div>
        </div>
        <div class="col-md-6 col-lg-3">
          <div class="feature-card">
            <div class="icon-wrapper">
              <i class="bi bi-graph-up"></i>
            </div>
            <h3>Analytics</h3>
            <p>Comprehensive reporting and analytics tools for informed decision-making.</p>
          </div>
        </div>
        <div class="col-md-6 col-lg-3">
          <div class="feature-card">
            <div class="icon-wrapper">
              <i class="bi bi-people"></i>
            </div>
            <h3>User-Friendly</h3>
            <p>Intuitive interface designed for healthcare professionals and administrators.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer">
    <div class="container">
      <div class="row gy-4">
        <div class="col-lg-4">
          <h4 class="text-gradient">IHMS</h4>
          <p class="mb-4">Empowering healthcare professionals with cutting-edge management solutions.</p>
        </div>
        <div class="col-lg-4">
          <h5>Quick Links</h5>
          <ul class="footer-links">
            <li><a href="#home">Home</a></li>
            <li><a href="#about">About</a></li>
            <li><a href="#login">Login</a></li>
            <li><a href="register.php">Sign Up</a></li>
          </ul>
        </div>
        <div class="col-lg-4">
          <h5>Contact</h5>
          <ul class="footer-contact">
            <li><i class="bi bi-envelope"></i> contact@ihms.com</li>
            <li><i class="bi bi-telephone"></i> +1 234 567 890</li>
            <li><i class="bi bi-geo-alt"></i> 123 Healthcare Street, Medical Center</li>
          </ul>
        </div>
      </div>
      <hr>
      <div class="row">
        <div class="col-md-6 text-center text-md-start">
          <p class="mb-0">&copy; 2024 IHMS. All rights reserved.</p>
        </div>
        <div class="col-md-6 text-center text-md-end">
          <div class="social-links">
            <a href="#"><i class="bi bi-facebook"></i></a>
            <a href="#"><i class="bi bi-twitter"></i></a>
            <a href="#"><i class="bi bi-linkedin"></i></a>
          </div>
        </div>
      </div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="script.js"></script>
</body>

</html>