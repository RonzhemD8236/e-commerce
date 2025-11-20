<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professional Footer - Lensify</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .main-content {
            flex: 1;
            padding: 40px;
            background: #f5f5f5;
        }

         
        .footer {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            color: #ffffff;
            padding: 60px 0 20px 0;
            margin-top: 0;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 40px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-section h3 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: #ffffff;
        }

        .footer-section p,
        .footer-section a {
            font-size: 0.95rem;
            line-height: 1.8;
            color: #b8b8b8;
            text-decoration: none;
            display: block;
            margin-bottom: 10px;
            transition: color 0.3s ease;
        }

        .footer-section a:hover {
            color: #ffffff;
        }

        .footer-logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 15px;
        }

        .footer-description {
            font-size: 0.9rem;
            color: #b8b8b8;
            margin-bottom: 20px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
        }

        .contact-item i {
            font-size: 1.1rem;
            margin-right: 12px;
            color: #4a90e2;
            min-width: 20px;
        }

        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            color: #ffffff;
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            background: #4a90e2;
            transform: translateY(-3px);
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .footer-bottom-links {
            display: flex;
            gap: 25px;
            flex-wrap: wrap;
        }

        .footer-bottom-links a {
            color: #b8b8b8;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .footer-bottom-links a:hover {
            color: #ffffff;
        }

        .copyright {
            color: #888888;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .footer-content {
                grid-template-columns: 1fr;
            }

            .footer-bottom {
                flex-direction: column;
                text-align: center;
            }

            .footer-bottom-links {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
     
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-content">
                 
                <div class="footer-section">
                    <div class="footer-logo">Lensify</div>
                    <p class="footer-description">
                        Your trusted partner for premium camera equipment and photography gear. Capturing moments, creating memories.
                    </p>
                    <div class="social-links">
                        <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                        <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                        <a href="#" aria-label="Twitter"><i class="bi bi-twitter"></i></a>
                        <a href="#" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>

                 
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <a href="#">Products</a>
                    <a href="#">Categories</a>
                </div>

                 
                <div class="footer-section">
                    <h3>Customer Service</h3>
                    <a href="#">Contact Us</a>
                    <a href="#">Shipping & Delivery</a>
                    <a href="#">Returns & Exchanges</a>
                    <a href="#">Warranty Information</a>
                    <a href="#">FAQ</a>
                </div>

                 
                <div class="footer-section">
                    <h3>Get In Touch</h3>
                    <div class="contact-item">
                        <i class="bi bi-geo-alt-fill"></i>
                        <span>22 Narra Street, Taguig City<br>Metro Manila, Philippines</span>
                    </div>
                    <div class="contact-item">
                        <i class="bi bi-envelope-fill"></i>
                        <span>support@lensify.ph</span>
                    </div>
                    <div class="contact-item">
                        <i class="bi bi-telephone-fill"></i>
                        <span>+63 917 654 3210</span>
                    </div>
                    <div class="contact-item">
                        <i class="bi bi-clock-fill"></i>
                        <span>Mon-Fri: 9AM - 6PM<br>Sat: 10AM - 4PM</span>
                    </div>
                </div>
            </div>

             
            <div class="footer-bottom">
                <p class="copyright">© 2025 Lensify. All rights reserved.</p>
                <div class="footer-bottom-links">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                    <a href="#">Cookie Policy</a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>