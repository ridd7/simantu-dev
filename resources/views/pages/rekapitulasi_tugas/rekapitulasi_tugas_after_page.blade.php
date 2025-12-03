<script type="text/javascript">
   var save_method;
   var table;
   var start_date = $('#start_date').val();
   var end_date = $('#end_date').val();
   var user_id = $('#selectUser').val();
   var TableAjax;

   TableAjax = function() {
    var handleRecords = function() {
      table = $('#datatable_ajax').DataTable({
         "scrollX": true,
         "processing": true,
         "serverSide": true,
         "pagingType": "full_numbers",
         "order": [],
         "ajax": {
               "url": "{{ url('rekapitulasi-tugas/ajax-list') }}",
               "type": "POST",
               "data": function(dtRequest) {
                  dtRequest['_token'] = '{{ csrf_token() }}';
                  dtRequest['filter_start_date'] = start_date;
                  dtRequest['filter_end_date'] = end_date;
                  dtRequest['user_id'] = user_id;
                  return dtRequest;
               },
               "dataSrc": function(response) {
                  if (user_id) {
                     $('#jumlah_tugas').text(response.recordsFiltered); // Menampilkan nilai recordsFiltered di dalam elemen dengan id 'jumlah_tugas
                     var totalNilai = 0;
                     var countNilai = 0;
                     $.each(response.data, function(index, item) {
                        var nilai = parseInt(item[6]); // Mengakses nilai pada index 6 (kolom nilai)
                        if (!isNaN(nilai)) {
                           totalNilai += nilai;
                           countNilai++;
                        }
                     });
                     var rataRata = countNilai > 0 ? totalNilai / countNilai : 0;
                     $('#nilai_rata_rata').text(rataRata.toFixed(2)); // Menampilkan rata-rata dengan 2 angka di belakang koma
                  }
                  return response.data;
               }
         },

         "columnDefs": [{
               "targets": [0],
               "orderable": false,
         }, ],

      });
    }

    return {
        init: function() {
            handleRecords();
        }

    };

   }();

$(document).ready(function() {
      TableAjax.init();
      $('#selectUser').select2({
         theme: 'bootstrap-5',
         placeholder: "Pilih Petugas",
         ajax: {
            url: "{{ route('pekerjaan.petugas.index') }}",
            processResults: function(data) {
               return {
               results: $.map(data, function(item){
                  return {
                     id: item.id,
                     text: item.name
                  }
               })
               }
            }
         },
         language: {
            searching: function () {
               return "Mencari...";
            }
         }
      });
})

function reload_table() {
   table.ajax.reload(null, false);
}

function filterData() {
   start_date = $('#start_date').val();
   end_date = $('#end_date').val();
   user_id = $('#selectUser').val();
   table.ajax.reload(null, false);
}

</script>