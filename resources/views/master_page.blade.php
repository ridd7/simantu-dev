<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="" name="description">
    <meta content="" name="keywords">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>  
    @include('../../_layout/_head_css')
</head>

<body>
    <div id="app">
      {{-- Sidebar --}}
      @include('_layout/_main_menu')
      {{-- End Sidebar --}}
        <div id="main" class="layout-navbar">
            <header>
                <nav class="navbar navbar-expand navbar-light navbar-top">
                  <div class="container-fluid">
                    <a href="#" class="burger-btn d-block">
                      <i class="bi bi-justify fs-3"></i>
                    </a>
      
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                      <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                      <div class="dropdown ms-auto mb-lg-0">
                        <a href="#" data-bs-toggle="dropdown" aria-expanded="false">
                          <div class="user-menu d-flex">
                            <div class="user-name text-end me-3">
                              <h6 class="mb-0 text-gray-600">{{ auth()->user()->name }}</h6>
                              <p class="mb-0 text-sm text-gray-600">{{ auth()->user()->jabatan }}</p>
                            </div>
                            <div class="user-img d-flex align-items-center">
                              <div class="avatar avatar-md">
                               <img src="{{ url('/storage/' . auth()->user()->photo_profile) }}" alt="Photo Profile">
                              </div>
                            </div>
                          </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton" style="min-width: 11rem">
                          <li>
                            <h6 class="dropdown-header">Hello, {{ auth()->user()->name }}!</h6>
                          </li>
                          <li>
                            <a class="dropdown-item" href="{{ url('profile') }}"><i class="icon-mid bi bi-person me-2"></i> My Profile</a>
                          </li>
                          <li>
                            <hr class="dropdown-divider">
                          </li>
                          <li>
                            <a class="dropdown-item" href="{{ url('logout') }}"><i class="icon-mid bi bi-box-arrow-left me-2"></i>
                              Logout</a>
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </nav>
              </header>
            {{-- Content  --}}
            <div id="main-content">
                <div class="page-heading">
                  <div class="page-title">
                      <div class="row mb-4">
                          <div class="col-12 col-md-6 order-md-1 order-last">
                              <h3>{{ $title }}</h3>
                          </div>
                          <div class="col-12 col-md-6 order-md-2 order-first">
                              <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                                  <ol class="breadcrumb">
                                      <li class="breadcrumb-item"><a href="index.html">Dashboard</a></li>
                                      <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
                                  </ol>
                              </nav>
                          </div>
                      </div>
                  </div>
    
                  @include($content)
    
                </div>
            </div>
        </div>
    </div>

    @include('../../_layout/_script')
    


    
</body>

</html>