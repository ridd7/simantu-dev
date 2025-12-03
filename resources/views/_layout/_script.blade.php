<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>

<script src="{{ url('assets/vendor/frappe-gantt/frappe-gantt.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.full.min.js"></script>

<script src="{{  url("assets/js/bootstrap.js") }}"></script>
<script src="{{  url("assets/js/app.js") }}"></script>
    
<script src="https://cdn.datatables.net/v/bs5/dt-1.12.1/datatables.min.js"></script>
<script src="assets/js/pages/datatables.js"></script>




   @if (isset($after_page))
      @include($after_page)
   @endif
