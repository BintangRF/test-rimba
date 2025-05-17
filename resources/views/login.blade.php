<!-- login.html -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login | Task Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-4">
        <div class="card">
          <div class="card-header">Login</div>
          <div class="card-body">
            <form id="loginForm">
              <div class="mb-3">
                <label>Email</label>
                <input type="email" class="form-control" id="email" required />
              </div>
              <div class="mb-3">
                <label>Password</label>
                <input type="password" class="form-control" id="password" required />
              </div>
              <button class="btn btn-primary w-100">Login</button>
            </form>
          </div>
        </div>
        <div id="error" class="text-danger mt-2 text-center"></div>
      </div>
    </div>
  </div>

  <script>
    document.getElementById('loginForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      const res = await fetch('http://localhost:8000/api/login', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          email: document.getElementById('email').value,
          password: document.getElementById('password').value
        })
      });

      const data = await res.json();

      if (res.ok) {
        localStorage.setItem('token', data.access_token);
        localStorage.setItem('user', JSON.stringify(data.user));
        window.location.href = '/dashboard';
      } else {
        document.getElementById('error').textContent = data.message || 'Login gagal';
      }
    });
  </script>
</body>
</html>
