<?php
session_start();
include("includes/header.php");
include("includes/config.php");
?>

<style>
    /* Remove conflicting body styles */
    body {
        background: #f2f2f2 !important;
    }

    .container {
        width: 90%;
        max-width: 1200px;
        margin: 40px auto;
        display: flex;
        gap: 25px;
    }

    /* LEFT SIDE */
    .left-panel {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .left-title {
        background: #000;
        color: #fff;
        padding: 15px;
        text-align: center;
        font-size: 24px;
        border-radius: 5px 5px 0 0;
        font-weight: bold;
    }

    .grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        background: white;
        border-radius: 0;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    .grid-box {
        text-align: center;
        padding: 20px 10px;
        border: 1px solid #ddd;
        background: white;
    }

    .grid-box i {
        font-size: 35px;
        margin-bottom: 8px;
    }

    .grid-title {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 4px;
    }

    .grid-text {
        font-size: 13px;
        color: #333;
    }

    /* FIXED IMAGE BANNER */
    .banner {
        width: 100%;
        height: 200px;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        border-radius: 5px;
        margin-top: 10px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        flex-shrink: 0;
    }

    /* RIGHT SIDE */
    .right-panel {
        flex: 1;
        background: white;
        border-radius: 5px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    .right-title {
        background: #000;
        color: #fff;
        padding: 15px;
        text-align: center;
        font-size: 22px;
        border-radius: 5px 5px 0 0;
        font-weight: bold;
    }

    .form {
        padding: 15px 20px;
    }

    .form-group {
        margin-bottom: 10px;
    }

    label {
        font-weight: bold;
        font-size: 13px;
        margin-bottom: 4px;
        display: block;
    }

    input, textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #aaa;
        border-radius: 4px;
        font-size: 14px;
    }

    textarea {
        height: 80px;
        resize: none;
    }

    .submit-btn {
        background: #000;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        font-size: 16px;
        cursor: pointer;
        display: block;
        margin: 12px auto 0;
        width: 130px;
    }

    .submit-btn:hover {
        background: #333;
    }

    /* Responsive */
    @media(max-width: 900px){
        .container {
            flex-direction: column;
        }
        .grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php
$banner = 'uploads/camera2.jpg'; 
if (!file_exists($banner)) $banner = 'uploads/camera2.jng';
$bannerCache = filemtime($banner);
?>

<div class="main-content">
<div class="container">

    <!-- LEFT SIDE -->
    <div class="left-panel">
        <div class="left-title">Get In Touch With Us!</div>

        <div class="grid">
            <div class="grid-box">
                <i>📞</i>
                <div class="grid-title">Phone Number</div>
                <div class="grid-text">+63 900 123 4567</div>
            </div>

            <div class="grid-box">
                <i>✉️</i>
                <div class="grid-title">Email</div>
                <div class="grid-text">support@camerahaven.com<br>sales@camerahaven.com</div>
            </div>

            <div class="grid-box">
                <i>📍</i>
                <div class="grid-title">Location</div>
                <div class="grid-text">Mall of Photography, QC, Philippines</div>
            </div>

            <div class="grid-box">
                <i>⏰</i>
                <div class="grid-title">Working Hours</div>
                <div class="grid-text">Mon–Sat • 9:00 AM – 7:00 PM</div>
            </div>
        </div>

        <div class="banner" style="background-image: url('<?= $banner ?>?v=<?= $bannerCache ?>');"></div>
    </div>

    <!-- RIGHT SIDE -->
    <div class="right-panel">
        <div class="right-title">Contact Us</div>

        <div class="form">
            <div class="form-group">
                <label>First Name *</label>
                <input type="text">
            </div>

            <div class="form-group">
                <label>Last Name *</label>
                <input type="text">
            </div>

            <div class="form-group">
                <label>Mobile No *</label>
                <input type="text">
            </div>

            <div class="form-group">
                <label>Email ID *</label>
                <input type="email">
            </div>

            <div class="form-group">
                <label>Message</label>
                <textarea></textarea>
            </div>

            <div class="form-group">
                <label>Verification *</label>
                <input type="text">
            </div>

            <button class="submit-btn">Submit</button>
        </div>
    </div>

</div>
</div>

<?php include("includes/footer.php"); ?>