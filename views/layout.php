<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Akademik Yayın Görüntüleyicisi' ?></title>
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <h1 class="logo">
                <a href="/">Akademik Yayın Görüntüleyicisi</a>
            </h1>
            <nav class="nav">
                <a href="/" class="nav-link">Projeler</a>
            </nav>
        </div>
    </header>

    <main class="main">
        <div class="container">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($_SESSION['success']) ?>
                    <?php unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <?= $content ?? '' ?>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> Akademik Yayın Görüntüleyicisi</p>
        </div>
    </footer>

    <script src="/public/js/app.js"></script>
</body>
</html>
