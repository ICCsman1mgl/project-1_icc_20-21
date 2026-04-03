<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' - ' : '' ?>Sistem Perpustakaan</title>
    
    <!-- Bootstrap CSS -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://code.jquery.com" crossorigin>
    <link rel="preconnect" href="https://cdn.datatables.net" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?= isset($cssPath) ? $cssPath : (defined('BASE_URL') ? BASE_URL . '/assets/css/style.css' : '/assets/css/style.css') ?>" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Meta tags -->
    <?php
        $metaDescription = isset($pageDescription) ? $pageDescription : 'Sistem Manajemen Perpustakaan Digital';
        $metaRobots = isset($metaRobots) ? $metaRobots : ((function_exists('isLoggedIn') && isLoggedIn()) ? 'noindex,nofollow' : 'index,follow');
        $canonicalUrl = isset($canonicalUrl) ? $canonicalUrl : null;
    ?>
    <meta name="description" content="<?= htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="author" content="Perpustakaan Digital">
    <meta name="robots" content="<?= htmlspecialchars($metaRobots, ENT_QUOTES, 'UTF-8') ?>">
    <?php if ($canonicalUrl): ?>
        <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= defined('BASE_URL') ? BASE_URL . '/assets/img/favicon.ico' : '/assets/img/favicon.ico' ?>">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
</head>
<body class="bg-light">
    <a class="visually-hidden-focusable" href="#main-content">Lewati ke konten utama</a>
    <main id="main-content">
