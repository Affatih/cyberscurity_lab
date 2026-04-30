<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-warning">
                    <h4 class="mb-0">Lupa Password</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>
                    
                    <form method="POST" action="/forgot-password">
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required>
                            <small class="text-muted">Coba cek email mana yang terdaftar</small>
                        </div>
                        <button type="submit" class="btn btn-warning w-100">Kirim Reset Link</button>
                    </form>
                    
                    <div class="mt-3 text-center">
                        <a href="/login">Kembali ke Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
