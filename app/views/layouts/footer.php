<!-- app/views/layouts/footer.php -->
    </main>
    
    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>CobaEkspor</h5>
                    <p>Website e-commerce untuk pelatihan cybersecurity.</p>
                </div>
                <div class="col-md-3">
                    <h5>Menu</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?= url('') ?>" class="text-white">Home</a></li>
                        <li><a href="<?= url('products') ?>" class="text-white">Produk</a></li>
                        <li><a href="<?= url('cart') ?>" class="text-white">Cart</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Kontak</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-envelope"></i> info@cobaekspor.com</li>
                        <li><i class="fas fa-phone"></i> 0812-3456-7890</li>
                    </ul>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p class="mb-0">&copy; 2024 CobaEkspor. Hak Cipta Dilindungi.</p>
                <small class="text-muted">*Website ini sengaja dibuat rentan untuk pelatihan cybersecurity*</small>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Custom JS -->
    <script src="<?= asset('js/main.js') ?>"></script>
</body>
</html>
