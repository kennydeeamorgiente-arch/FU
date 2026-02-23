<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= base_url('css/theme/theme.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/organization/login.css') ?>">
    <title>Document</title>
    <style>
        body {
            background-image: url(<?= base_url('img/login-bg.png') ?>);
            height: 100vh;
            margin: 0;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
    </style>
</head>

<body>
    <div class="container">

        <form id="loginForm" method="post" action="<?= base_url('/verifyLogin') ?>">
            <?= csrf_field() ?>
            <img src="<?= base_url('img/foundationu_logo.png') ?>" alt="Foundation University Logo">
            <h1>FU Events Management System</h1>

            <label for="email">Email</label>
            <input type="text" id="email" name="email" placeholder="Email">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Password">
            <div class="alert alert-danger"></div>
            <button type="submit">Sign In</button>
            <!-- Google Login Button -->
            <p>o r</p>
            <a href="<?= base_url('google/login') ?>">
                <img src="<?= base_url('img/google-icon.png') ?>" alt="google login" style="width:5vh">
            </a>



        </form>
    </div>

    <!-- Login Script -->
    <script src="<?= base_url('js/login.js') ?>"></script>
</body>

</html>