<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">Login</h4>
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
                    
                    <form method="POST" action="/login<?= isset($_GET['redirect']) ? '?redirect='.$_GET['redirect'] : '' ?>">
                        <div class="mb-3">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control" required>
                            <small class="text-muted">Coba inject: admin' -- -</small>
                        </div>
                        <div class="mb-3">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Login</button>
                    </form>
                    
                    <div class="mt-3 text-center">
                        Belum punya akun? <a href="/register">Register</a><br>
                        <a href="/forgot-password">Lupa password?</a>
                    </div>
                    
                    <hr>
                    <div class="alert alert-info">
                        <strong>Demo Akun:</strong><br>
                        Admin: admin / admin123<br>
                        User: john_doe / password123
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
