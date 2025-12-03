<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Simantu</title>
    @include('../../_layout/_head_css')
</head>

<body>
    <div id="auth">
        
<div class="row h-100">
    <div class="col-lg-5 col-12">
        <div id="auth-left">
            <div class="auth-logo">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 30">
                <text x="30" y="20" font-size="20" font-weight="bold" fill="#435ebe">Simantu</text>
              </svg>
            </div>
            <h1 class="auth-title mt-5">Login</h1>
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
                @endif
                @if (session('loginError'))
                <div class="alert alert-danger">
                    {{ session('loginError') }}
                </div>
            @endif

            <form action="{{ url('/login') }}" method="POST">
                @csrf
                <div class="form-group position-relative has-icon-left mb-4">
                    <input type="text" class="form-control form-control-xl" name="username" placeholder="Username">
                    <div class="form-control-icon">
                        <i class="bi bi-person"></i>
                    </div>
                </div>
                <div class="form-group position-relative has-icon-left mb-4">
                    <input type="password" class="form-control form-control-xl" name="password" placeholder="Password">
                    <div class="form-control-icon">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                </div>
                <button class="btn btn-primary btn-block btn-lg shadow-lg mt-1">Log in</button>
            </form>
        </div>
    </div>
    <div class="col-lg-7 d-none d-lg-block">
      <div id="auth-right" class="d-flex flex-column justify-content-center align-items-center h-100">
          <div class="py-4">
              <div class="pt-4 pb-2">
                  <div class="d-flex justify-content-center">
                      <img src="{{ url('assets/images/logo/pemko-medan.png') }}" class="d-block" alt="" style="max-width: 25%; object-fit: contain;">
                  </div>
                  <div class="d-flex justify-content-center">
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 55" style="width: 100%; height: 100%;">
                          <text x="50%" y="27%" text-anchor="middle" alignment-baseline="middle" font-size="20" font-weight="bold" fill="#FFFFFF">Simantu</text>
                          <text x="50%" y="50%" text-anchor="middle" alignment-baseline="middle" font-size="9" fill="#FFFFFF">Sistem Manajemen Tugas</text>
                      </svg>
                  </div>
              </div>
          </div><!-- End Logo -->
      </div>
  </div>
</div>

    </div>
</body>

</html>
