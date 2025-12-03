<link rel="stylesheet" href="{{ url("assets/css/main/app.css") }}">
<link rel="stylesheet" href="{{ url("assets/css/main/app-dark.css") }}">
<link rel="stylesheet" href="{{ url("assets/css/style.css") }}">
<link rel="shortcut icon" href="{{ url("assets/images/logo/pemko-medan.png") }}" type="image/x-icon">
@if (request()->segment(1) == '' || request()->segment(1) == 'login')
  <link rel="stylesheet" href="{{ url("assets/css/pages/auth.css") }}">
@else
  <link href="{{ url('assets/vendor/frappe-gantt/frappe-gantt.css') }}" rel="stylesheet">
  <link rel="stylesheet" href="{{ url("assets/extensions/datatables.net-bs5/css/dataTables.bootstrap5.min.css") }}">
  <link rel="stylesheet" href="{{ url("assets/css/pages/datatables.css") }}">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
  <link rel="stylesheet" href="{{ url("assets/extensions/@fortawesome/fontawesome-free/css/all.min.css") }}">
  <link rel="stylesheet" href="{{ url("assets/css/shared/iconly.css") }}">
@endif

