k<!-- app/views/home/index.php -->
<div class="container mt-5">
    <div class="jumbotron bg-light p-5 rounded">
        <h1 class="display-4"><?= $title ?></h1>
        <p class="lead"><?= $message ?></p>
        <hr class="my-4">
        <p>Website ini sengaja dibuat rentan untuk pelatihan cybersecurity.</p>
        <a class="btn btn-primary btn-lg" href="<?= url('products') ?>" role="button">Lihat Produk</a>
        <a class="btn btn-success btn-lg" href="<?= url('login') ?>" role="button">Login</a>
    </div>
</div>
