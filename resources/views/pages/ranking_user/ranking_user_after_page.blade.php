<script type="text/javascript">
   var save_method;
   var table_ranking_koordinator;
   var table_ranking_petugas;
   var start_date_koordinator = $('#start_date_koordinator').val();
   var end_date_koordinator = $('#end_date_koordinator').val();
   var start_date_petugas = $('#start_date_petugas').val();
   var end_date_petugas = $('#end_date_petugas').val();
   var TableAjax;

   TableAjax = function() {
    var handleRecords = function() {
      table_ranking_koordinator = $('#datatable_ajax_ranking_koordinator').DataTable({
            "scrollX": true,
            "processing": true,
            "serverSide": true,
            "pagingType": "full_numbers",
            "order": [],
            "ajax": {
                "url": "{{ url('ranking-user/ajax-list-ranking-koordinator') }}",
                "type": "POST",
                "data": function(dtRequest) {
                     dtRequest['_token'] = '{{ csrf_token() }}';
                     dtRequest['filter_start_date'] = start_date_koordinator;
                     dtRequest['filter_end_date'] = end_date_koordinator;
                     return dtRequest;
                  }
            },

            "columnDefs": [{
                "targets": [0],
                "orderable": false,
            }, ],

        });

        table_ranking_petugas = $('#datatable_ajax_ranking_petugas').DataTable({
            "scrollX": true,
            "processing": true,
            "serverSide": true,
            "pagingType": "full_numbers",
            "order": [],
            "ajax": {
                "url": "{{ url('ranking-user/ajax-list-ranking-petugas') }}",
                "type": "POST",
                "data": function(dtRequest) {
                     dtRequest['_token'] = '{{ csrf_token() }}';
                     dtRequest['filter_start_date'] = start_date_petugas;
                     dtRequest['filter_end_date'] = end_date_petugas;
                     return dtRequest;
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

function reload_table_ranking_koordinator() {
   table_ranking_koordinator.ajax.reload(null, false);
}

function reload_table_ranking_petugas() {
   table_ranking_petugas.ajax.reload(null, false);
}

function filterData(user_role) {
   if(user_role === 'koordinator') {
      start_date_koordinator = $('#start_date_koordinator').val();
      end_date_koordinator = $('#end_date_koordinator').val();
      table_ranking_koordinator.ajax.reload(null, false);
   }
   if(user_role === 'petugas') {
      start_date_petugas = $('#start_date_petugas').val();
      end_date_petugas = $('#end_date_petugas').val();
      table_ranking_petugas.ajax.reload(null, false);
   }
}

</script>