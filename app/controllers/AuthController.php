<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserModel;

class AuthController extends Controller
{
    private $userModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->userModel = new UserModel();
    }
    
    // =========== REGISTER ===========
    public function registerForm()
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: /');
            exit;
        }
        echo $this->getRegisterForm();
    }
    
    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /register');
            exit;
        }
        
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $full_name = $_POST['full_name'] ?? '';
        
        // KERENTANAN SQL INJECTION di pengecekan username
        $existingUser = $this->userModel->findByUsernameVulnerable($username);
        
        if ($existingUser) {
            $_SESSION['error'] = 'Username sudah digunakan';
            header('Location: /register');
            exit;
        }
        
        $hashedPassword = md5($password);
        $data = [
            'username' => $username,
            'email' => $email,
            'password' => $hashedPassword,
            'full_name' => $full_name,
            'role' => 'user'
        ];
        
        if ($this->userModel->create($data)) {
            $_SESSION['success'] = 'Registrasi berhasil! Silakan login.';
            header('Location: /login');
            exit;
        } else {
            $_SESSION['error'] = 'Registrasi gagal';
            header('Location: /register');
            exit;
        }
    }
    
    // =========== LOGIN (RENTAN SQL INJECTION BYPASS) ===========
    public function loginForm()
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: /');
            exit;
        }
        echo $this->getLoginForm();
    }
    
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }
        
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $hashedPassword = md5($password);
        
        // KERENTANAN SQL INJECTION pada username dan password
        $user = $this->userModel->findByCredentialsVulnerable($username, $hashedPassword);
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            
            $_SESSION['success'] = 'Selamat datang, ' . $user['full_name'] . '!';
            
            if ($user['role'] === 'admin') {
                header('Location: /admin');
                exit;
            } else {
                $redirect = $_GET['redirect'] ?? '/';
                header('Location: ' . $redirect);
                exit;
            }
        } else {
            $_SESSION['error'] = 'Username atau password salah';
            header('Location: /login');
            exit;
        }
    }
    
    // =========== LOGOUT ===========
    public function logout()
    {
        session_destroy();
        header('Location: /login');
        exit;
    }
    
    // =========== FORGOT PASSWORD ===========
    public function forgotPasswordForm()
    {
        echo $this->getForgotPasswordForm();
    }
    
    public function forgotPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /forgot-password');
            exit;
        }
        
        $email = $_POST['email'] ?? '';
        $user = $this->userModel->findByEmail($email);
        
        if ($user) {
            // KERENTANAN HOST HEADER INJECTION
            $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/reset-password?email=" . urlencode($email);
            $_SESSION['success'] = "Link reset password telah dikirim ke $email: $resetLink";
        } else {
            $_SESSION['error'] = 'Email tidak ditemukan';
        }
        
        header('Location: /forgot-password');
        exit;
    }
    
    // =========== FORM HTML ===========
    private function getRegisterForm()
    {
        $error = $_SESSION['error'] ?? '';
        unset($_SESSION['error']);
        
        return '<!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Register | CobaEkspor</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
            <style>
                * { margin:0; padding:0; box-sizing:border-box; }
                body {
                    min-height:100vh;
                    background:linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
                    font-family:"Inter","Segoe UI",sans-serif;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    padding:2rem;
                }
                .register-card {
                    background:rgba(15,25,35,0.95);
                    backdrop-filter:blur(10px);
                    border-radius:2rem;
                    box-shadow:0 25px 50px -12px rgba(0,0,0,0.5);
                    overflow:hidden;
                    max-width:500px;
                    width:100%;
                    transition:transform 0.3s ease, box-shadow 0.3s ease;
                    border:1px solid rgba(255,140,0,0.3);
                }
                .register-card:hover {
                    transform:translateY(-5px);
                    box-shadow:0 30px 60px -15px rgba(255,80,0,0.3);
                }
                .card-header {
                    background:transparent;
                    border-bottom:none;
                    padding:2rem 2rem 0 2rem;
                    text-align:center;
                }
                .card-header .logo {
                    font-size:2.5rem;
                    font-weight:800;
                    background:linear-gradient(135deg, #ff8c00, #ff5500);
                    -webkit-background-clip:text;
                    background-clip:text;
                    color:transparent;
                    letter-spacing:-0.5px;
                }
                .card-header .sub {
                    color:#ccc;
                    margin-top:0.5rem;
                    font-size:0.9rem;
                }
                .card-body { padding:2rem; }
                .input-group-custom { margin-bottom:1.5rem; }
                .input-group-custom label {
                    font-weight:600;
                    margin-bottom:0.5rem;
                    display:block;
                    color:#e2e8f0;
                    font-size:0.9rem;
                }
                .input-group-custom .input-icon { position:relative; }
                .input-group-custom .input-icon i {
                    position:absolute;
                    left:15px;
                    top:50%;
                    transform:translateY(-50%);
                    color:#ff8c00;
                    font-size:1.1rem;
                }
                .input-group-custom input {
                    width:100%;
                    padding:0.8rem 1rem 0.8rem 2.8rem;
                    border:1px solid #334155;
                    border-radius:1.5rem;
                    font-size:1rem;
                    transition:all 0.2s ease;
                    background:#1e293b;
                    color:#f1f5f9;
                }
                .input-group-custom input:focus {
                    outline:none;
                    border-color:#ff8c00;
                    box-shadow:0 0 0 3px rgba(255,140,0,0.3);
                    background:#0f172a;
                }
                .btn-register {
                    background:linear-gradient(135deg, #ff8c00, #ff5500);
                    border:none;
                    border-radius:2rem;
                    padding:0.8rem;
                    font-weight:600;
                    font-size:1rem;
                    width:100%;
                    color:white;
                    transition:all 0.3s ease;
                    cursor:pointer;
                    box-shadow:0 4px 6px -1px rgba(0,0,0,0.3);
                }
                .btn-register:hover {
                    transform:scale(1.02);
                    box-shadow:0 10px 15px -3px rgba(255,85,0,0.5);
                    background:linear-gradient(135deg, #ff7700, #ff4400);
                }
                .login-link {
                    text-align:center;
                    margin-top:1.5rem;
                    font-size:0.9rem;
                    color:#9ca3af;
                }
                .login-link a {
                    color:#ff8c00;
                    text-decoration:none;
                    font-weight:600;
                }
                .login-link a:hover { text-decoration:underline; }
                .alert-custom {
                    border-radius:1rem;
                    padding:0.75rem 1rem;
                    margin-bottom:1.5rem;
                    font-size:0.9rem;
                    border-left:4px solid;
                }
                .alert-danger {
                    background:#450a0a;
                    border-left-color:#ff3333;
                    color:#ffcccc;
                }
                @media (max-width:480px) {
                    body { padding:1rem; }
                    .card-body { padding:1.5rem; }
                }
            </style>
        </head>
        <body>
            <div class="register-card">
                <div class="card-header">
                    <div class="logo">CobaEkspor</div>
                    <div class="sub">Daftar akun baru</div>
                </div>
                <div class="card-body">
                    ' . ($error ? '<div class="alert-custom alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>' . htmlspecialchars($error) . '</div>' : '') . '
                    <form method="POST" action="/register">
                        <div class="input-group-custom">
                            <label><i class="fas fa-user-plus me-1"></i> Username</label>
                            <div class="input-icon">
                                <i class="fas fa-user"></i>
                                <input type="text" name="username" placeholder="Pilih username" required>
                            </div>
                            <small class="text-muted" style="font-size:0.7rem; color:#aaa;">Contoh: john_doe</small>
                        </div>
                        <div class="input-group-custom">
                            <label><i class="fas fa-envelope me-1"></i> Email</label>
                            <div class="input-icon">
                                <i class="fas fa-envelope"></i>
                                <input type="email" name="email" placeholder="Email aktif" required>
                            </div>
                        </div>
                        <div class="input-group-custom">
                            <label><i class="fas fa-lock me-1"></i> Password</label>
                            <div class="input-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="password" placeholder="Minimal 6 karakter" required>
                            </div>
                        </div>
                        <div class="input-group-custom">
                            <label><i class="fas fa-id-card me-1"></i> Nama Lengkap</label>
                            <div class="input-icon">
                                <i class="fas fa-id-card"></i>
                                <input type="text" name="full_name" placeholder="Nama lengkap" required>
                            </div>
                        </div>
                        <button type="submit" class="btn-register">
                            <i class="fas fa-user-plus me-2"></i> Daftar
                        </button>
                    </form>
                    <div class="login-link">
                        Sudah punya akun? <a href="/login">Login sekarang</a>
                    </div>
                </div>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>';
    }
    
    private function getLoginForm()
    {
        $error = $_SESSION['error'] ?? '';
        $success = $_SESSION['success'] ?? '';
        unset($_SESSION['error'], $_SESSION['success']);
        $redirect = isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : '';
        
        return '<!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Login | CobaEkspor</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
            <style>
                * { margin:0; padding:0; box-sizing:border-box; }
                body {
                    min-height:100vh;
                    background:linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
                    font-family:"Inter","Segoe UI",sans-serif;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    padding:2rem;
                }
                .login-card {
                    background:rgba(15,25,35,0.95);
                    backdrop-filter:blur(10px);
                    border-radius:2rem;
                    box-shadow:0 25px 50px -12px rgba(0,0,0,0.5);
                    overflow:hidden;
                    max-width:450px;
                    width:100%;
                    transition:transform 0.3s ease, box-shadow 0.3s ease;
                    border:1px solid rgba(255,140,0,0.3);
                }
                .login-card:hover {
                    transform:translateY(-5px);
                    box-shadow:0 30px 60px -15px rgba(255,80,0,0.3);
                }
                .card-header {
                    background:transparent;
                    border-bottom:none;
                    padding:2rem 2rem 0 2rem;
                    text-align:center;
                }
                .card-header .logo {
                    font-size:2.5rem;
                    font-weight:800;
                    background:linear-gradient(135deg, #ff8c00, #ff5500);
                    -webkit-background-clip:text;
                    background-clip:text;
                    color:transparent;
                    letter-spacing:-0.5px;
                }
                .card-header .sub {
                    color:#ccc;
                    margin-top:0.5rem;
                    font-size:0.9rem;
                }
                .card-body { padding:2rem; }
                .input-group-custom { margin-bottom:1.5rem; }
                .input-group-custom label {
                    font-weight:600;
                    margin-bottom:0.5rem;
                    display:block;
                    color:#e2e8f0;
                    font-size:0.9rem;
                }
                .input-group-custom .input-icon { position:relative; }
                .input-group-custom .input-icon i {
                    position:absolute;
                    left:15px;
                    top:50%;
                    transform:translateY(-50%);
                    color:#ff8c00;
                    font-size:1.1rem;
                }
                .input-group-custom input {
                    width:100%;
                    padding:0.8rem 1rem 0.8rem 2.8rem;
                    border:1px solid #334155;
                    border-radius:1.5rem;
                    font-size:1rem;
                    transition:all 0.2s ease;
                    background:#1e293b;
                    color:#f1f5f9;
                }
                .input-group-custom input:focus {
                    outline:none;
                    border-color:#ff8c00;
                    box-shadow:0 0 0 3px rgba(255,140,0,0.3);
                    background:#0f172a;
                }
                .btn-login {
                    background:linear-gradient(135deg, #ff8c00, #ff5500);
                    border:none;
                    border-radius:2rem;
                    padding:0.8rem;
                    font-weight:600;
                    font-size:1rem;
                    width:100%;
                    color:white;
                    transition:all 0.3s ease;
                    cursor:pointer;
                    box-shadow:0 4px 6px -1px rgba(0,0,0,0.3);
                }
                .btn-login:hover {
                    transform:scale(1.02);
                    box-shadow:0 10px 15px -3px rgba(255,85,0,0.5);
                    background:linear-gradient(135deg, #ff7700, #ff4400);
                }
                .register-link {
                    text-align:center;
                    margin-top:1.5rem;
                    font-size:0.9rem;
                    color:#9ca3af;
                }
                .register-link a {
                    color:#ff8c00;
                    text-decoration:none;
                    font-weight:600;
                }
                .register-link a:hover { text-decoration:underline; }
                .forgot-link {
                    text-align:right;
                    margin-top:-0.8rem;
                    margin-bottom:1rem;
                    font-size:0.8rem;
                }
                .forgot-link a {
                    color:#9ca3af;
                    text-decoration:none;
                }
                .forgot-link a:hover { color:#ff8c00; }
                .alert-custom {
                    border-radius:1rem;
                    padding:0.75rem 1rem;
                    margin-bottom:1.5rem;
                    font-size:0.9rem;
                    border-left:4px solid;
                }
                .alert-danger {
                    background:#450a0a;
                    border-left-color:#ff3333;
                    color:#ffcccc;
                }
                .alert-success {
                    background:#0a3a1a;
                    border-left-color:#00cc44;
                    color:#ccffcc;
                }
                @media (max-width:480px) {
                    body { padding:1rem; }
                    .card-body { padding:1.5rem; }
                }
            </style>
        </head>
        <body>
            <div class="login-card">
                <div class="card-header">
                    <div class="logo">CobaEkspor</div>
                    <div class="sub">Marketplace belajar keamanan siber</div>
                </div>
                <div class="card-body">
                    ' . ($error ? '<div class="alert-custom alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>' . htmlspecialchars($error) . '</div>' : '') . '
                    ' . ($success ? '<div class="alert-custom alert-success"><i class="fas fa-check-circle me-2"></i>' . htmlspecialchars($success) . '</div>' : '') . '
                    <form method="POST" action="/login' . $redirect . '">
                        <div class="input-group-custom">
                            <label><i class="fas fa-user me-1"></i> Username</label>
                            <div class="input-icon">
                                <i class="fas fa-user"></i>
                                <input type="text" name="username" placeholder="Masukkan username" required>
                            </div>
                            <small class="text-muted" style="font-size:0.7rem; color:#aaa;">Contoh: admin\' -- -</small>
                        </div>
                        <div class="input-group-custom">
                            <label><i class="fas fa-lock me-1"></i> Password</label>
                            <div class="input-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="password" placeholder="••••••" required>
                            </div>
                        </div>
                        <div class="forgot-link">
                            <a href="/forgot-password">Lupa password?</a>
                        </div>
                        <button type="submit" class="btn-login">
                            <i class="fas fa-sign-in-alt me-2"></i> Masuk
                        </button>
                    </form>
                    <div class="register-link">
                        Belum punya akun? <a href="/register">Daftar sekarang</a>
                    </div>
                </div>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>';
    }
    
    private function getForgotPasswordForm()
    {
        $error = $_SESSION['error'] ?? '';
        $success = $_SESSION['success'] ?? '';
        unset($_SESSION['error'], $_SESSION['success']);
        
        return '<!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Lupa Password | CobaEkspor</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
            <style>
                * { margin:0; padding:0; box-sizing:border-box; }
                body {
                    min-height:100vh;
                    background:linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
                    font-family:"Inter","Segoe UI",sans-serif;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    padding:2rem;
                }
                .forgot-card {
                    background:rgba(15,25,35,0.95);
                    backdrop-filter:blur(10px);
                    border-radius:2rem;
                    box-shadow:0 25px 50px -12px rgba(0,0,0,0.5);
                    overflow:hidden;
                    max-width:450px;
                    width:100%;
                    transition:transform 0.3s ease, box-shadow 0.3s ease;
                    border:1px solid rgba(255,140,0,0.3);
                }
                .forgot-card:hover {
                    transform:translateY(-5px);
                    box-shadow:0 30px 60px -15px rgba(255,80,0,0.3);
                }
                .card-header {
                    background:transparent;
                    border-bottom:none;
                    padding:2rem 2rem 0 2rem;
                    text-align:center;
                }
                .card-header .logo {
                    font-size:2.5rem;
                    font-weight:800;
                    background:linear-gradient(135deg, #ff8c00, #ff5500);
                    -webkit-background-clip:text;
                    background-clip:text;
                    color:transparent;
                    letter-spacing:-0.5px;
                }
                .card-header .sub {
                    color:#ccc;
                    margin-top:0.5rem;
                    font-size:0.9rem;
                }
                .card-body { padding:2rem; }
                .input-group-custom { margin-bottom:1.5rem; }
                .input-group-custom label {
                    font-weight:600;
                    margin-bottom:0.5rem;
                    display:block;
                    color:#e2e8f0;
                    font-size:0.9rem;
                }
                .input-group-custom .input-icon { position:relative; }
                .input-group-custom .input-icon i {
                    position:absolute;
                    left:15px;
                    top:50%;
                    transform:translateY(-50%);
                    color:#ff8c00;
                    font-size:1.1rem;
                }
                .input-group-custom input {
                    width:100%;
                    padding:0.8rem 1rem 0.8rem 2.8rem;
                    border:1px solid #334155;
                    border-radius:1.5rem;
                    font-size:1rem;
                    transition:all 0.2s ease;
                    background:#1e293b;
                    color:#f1f5f9;
                }
                .input-group-custom input:focus {
                    outline:none;
                    border-color:#ff8c00;
                    box-shadow:0 0 0 3px rgba(255,140,0,0.3);
                    background:#0f172a;
                }
                .btn-submit {
                    background:linear-gradient(135deg, #ff8c00, #ff5500);
                    border:none;
                    border-radius:2rem;
                    padding:0.8rem;
                    font-weight:600;
                    font-size:1rem;
                    width:100%;
                    color:white;
                    transition:all 0.3s ease;
                    cursor:pointer;
                    box-shadow:0 4px 6px -1px rgba(0,0,0,0.3);
                }
                .btn-submit:hover {
                    transform:scale(1.02);
                    box-shadow:0 10px 15px -3px rgba(255,85,0,0.5);
                    background:linear-gradient(135deg, #ff7700, #ff4400);
                }
                .back-link {
                    text-align:center;
                    margin-top:1.5rem;
                    font-size:0.9rem;
                }
                .back-link a {
                    color:#ff8c00;
                    text-decoration:none;
                    font-weight:600;
                }
                .back-link a:hover { text-decoration:underline; }
                .alert-custom {
                    border-radius:1rem;
                    padding:0.75rem 1rem;
                    margin-bottom:1.5rem;
                    font-size:0.9rem;
                    border-left:4px solid;
                }
                .alert-danger {
                    background:#450a0a;
                    border-left-color:#ff3333;
                    color:#ffcccc;
                }
                .alert-success {
                    background:#0a3a1a;
                    border-left-color:#00cc44;
                    color:#ccffcc;
                }
                @media (max-width:480px) {
                    body { padding:1rem; }
                    .card-body { padding:1.5rem; }
                }
            </style>
        </head>
        <body>
            <div class="forgot-card">
                <div class="card-header">
                    <div class="logo">CobaEkspor</div>
                    <div class="sub">Reset password</div>
                </div>
                <div class="card-body">
                    ' . ($error ? '<div class="alert-custom alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>' . htmlspecialchars($error) . '</div>' : '') . '
                    ' . ($success ? '<div class="alert-custom alert-success"><i class="fas fa-check-circle me-2"></i>' . htmlspecialchars($success) . '</div>' : '') . '
                    <form method="POST" action="/forgot-password">
                        <div class="input-group-custom">
                            <label><i class="fas fa-envelope me-1"></i> Email</label>
                            <div class="input-icon">
                                <i class="fas fa-envelope"></i>
                                <input type="email" name="email" placeholder="Email terdaftar" required>
                            </div>
                        </div>
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-paper-plane me-2"></i> Kirim link reset
                        </button>
                    </form>
                    <div class="back-link">
                        <a href="/login"><i class="fas fa-arrow-left me-1"></i> Kembali ke login</a>
                    </div>
                </div>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>';
    }
}
