<script type="text/javascript">
   var save_method;
   var table;
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
                "url": "{{ url('satuan/ajax-list') }}",
                "type": "POST",
                "data": function(dtRequest) {
                     dtRequest['_token'] = '{{ csrf_token() }}';
                     return dtRequest;
                  }
            },

            "columnDefs": [{
                "targets": [-1, 0],
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

      $("input").change(function() {
         $('#satuan_form').find('.is-invalid').removeClass('is-invalid');
         $('#satuan_form').find('.invalid-feedback').text('');
      });

      $('#satuan_form').submit(function(e) {
          e.preventDefault();
          $('#btnSave').text('Saving...');
          $('#btnSave').attr('disabled', true);
          
          if (save_method == 'add') {
            url = "<?php echo url('satuan/ajax-add') ?>";
          } else {
            url = "<?php echo url('satuan/ajax-update') ?>";
          }
          $.ajaxSetup({
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
          });

          $.ajax({
               url: url,
               type: 'POST',
               dataType: 'JSON',
               data: new FormData(this),
               contentType: false,
               processData: false,
               success: function(data) {
                  $('#btnSave').text('Submit');
                  $('#btnSave').attr('disabled', false);

                  if (data.status) {
                     $('#modal_satuan_form').modal('hide');
                     reload_table();
                  } else {
                     $.each(data.inputerror, function(index, value) {
                        $('#' + value).addClass('is-invalid');
                     });
                     $.each(data.error_string, function(index, value) {
                        $('#' + data.inputerror[index] + '-error').text(value);
                     });
                  }
               },
            }); 
      });
})

function reload_table() {
  table.ajax.reload(null, false);
}

function add_satuan() {
   save_method = 'add';
   $('[name="_method"]').val("POST");
   $('#satuan_form')[0].reset();
   $('#satuan_form').find('.is-invalid').removeClass('is-invalid');
   $('#satuan_form').find('.invalid-feedback').text('');
   $('#modal_satuan_form').modal('show');
   $('.modal-title').text('Tambah Satuan');
}

function edit_satuan(id) {
   save_method = 'update';
   $('#satuan_form')[0].reset();
   $('#satuan_form').find('.is-invalid').removeClass('is-invalid');
   $('#satuan_form').find('.invalid-feedback').text('');
   $.ajax({
      url: "<?php echo url('satuan/ajax-edit') ?>/" +id,
      type: "GET",
      dataType: "JSON",
      success: function(data) {
            $('[name="_method"]').val("PUT");
            $('[name="id"]').val(data.satuan.id);           
            $('[name="satuan"]').val(data.satuan.satuan);                      
            $('#modal_satuan_form').modal('show');
            $('.modal-title').text("Edit Satuan");
      },
      error: function(jqXHR, textStatus, errorThrown) {
            alert('Error get data from ajax');
      }
   });
}

function delete_satuan(id) {
  if (confirm('Are you sure delete this data?')) {
      $.ajaxSetup({
         headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
         }
      });
      $.ajax({
          url: "<?php echo url('satuan/ajax-delete') ?>/" + id,
          type: "DELETE",
          success: function(data) {
              reload_table();
          },
      });
  }
}

function reload_table() {
  table.ajax.reload(null, false);
}



</script>

<!-- Satuan Modal -->
<div class="modal fade" id="modal_satuan_form" tab-index="-1" aria-hidden="true" style="display: none;">
   <div class="modal-dialog" role="document">
     <div class="modal-content">
       <div class="modal-header">
         <h5 class="modal-title" id="exampleModalLabel1">Satuan</h5>
         <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
       </div>
       <div class="modal-body">
         <div class="row">
           <div class="col mb-3">
             <form id="satuan_form" class="row g-3" enctype="multipart/form-data">
               @csrf
               <input type="hidden" value="" name="_method" />
               <input type="hidden" value="" name="id" />
               <div class="col-12">
                 <label class="form-label" for="name">Satuan</label>
                 <input type="text" id="satuan" name="satuan" class="form-control" />
                 <div class="invalid-feedback d-block" id="satuan-error"></div>
               </div>               
               <div class="col-12 text-center">
                 <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
                 <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
               </div>
             </form>
           </div>
         </div>
       </div>
      </div>
    </div>
  </div>
  <!-- Satuan Modal -->