<script type="text/javascript">
   var save_method;
   var table;
   var start_date = $('#start_date').val();
   var end_date = $('#end_date').val();
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
               "url": "{{ url('rekapitulasi-output/ajax-list') }}",
               "type": "POST",
               "data": function(dtRequest) {
                  dtRequest['_token'] = '{{ csrf_token() }}';
                  dtRequest['filter_start_date'] = start_date;
                  dtRequest['filter_end_date'] = end_date;
                  return dtRequest;
               },
               "dataSrc": function(response) {
                 total_output = response.total_output;
                 var totalOutputTable = $('#total_output'); 
                 totalOutputTable.find("tr:not(:first-child)").remove();
                 no = 1;
                 total_output.forEach(item => {
                     var row = '<tr>' +
                        '<td>' + no + '. </td>' +
                        '<td>' + item.satuan + '</td>' +
                        '<td>: <span>' + item.total_output + '</span></td>' +
                        '</tr>';
                     $('#total_output').append(row); // Append the row to the table
                     no++;
                  });
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
})

function reload_table() {
   table.ajax.reload(null, false);
}

function filterData() {
   start_date = $('#start_date').val();
   end_date = $('#end_date').val();
   table.ajax.reload(null, false);
}

</script>