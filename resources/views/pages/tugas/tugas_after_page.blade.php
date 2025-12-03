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
                "url": "{{ url('alur-tugas/ajax-list') }}",
                "type": "POST",
                "data": function(dtRequest) {
                     dtRequest['_token'] = '{{ csrf_token() }}';
                     return dtRequest;
                  }
            },

            "columnDefs": [{
                "targets": [-1, 0, 3],
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

      $('#alur_tugas_form').submit(function(e) {
          e.preventDefault();
          $('#btnSave').text('Saving...');
          $('#btnSave').attr('disabled', true);
          
          if (save_method == 'add') {
            url = "<?php echo url('alur-tugas/ajax-add') ?>";
          } else {
            url = "<?php echo url('alur-tugas/ajax-update') ?>";
          }
          $.ajaxSetup({
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
          });
          $.ajax({
              url: url,
              type: 'POST',
              dataType: "JSON",
              data: $(this).serialize(),
              success: function(data) {
                  $('#btnSave').text('Submit');
                  $('#btnSave').attr('disabled', false);

                  if (data.status) {
                      $('#modal_alur_tugas_form').modal('hide');
                      reload_table();
                  } else {
                      for (var i = 0; i < data.inputerror.length; i++) {
                          $('[name="' + data.inputerror[i] + '"]').parent().addClass('has-error');
                          $('[name="' + data.inputerror[i] + '"]').next().text(data.error_string[i]);
                      }
                  }
              },
          });
      });

      $('#tugas_form').submit(function(e) {
          e.preventDefault();
          $('#btnSave').text('Saving...');
          $('#btnSave').attr('disabled', true);
          
          url = "<?php echo url('tugas/ajax-update') ?>";
         
          $.ajaxSetup({
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
          });
          $.ajax({
              url: url,
              type: 'POST',
              dataType: "JSON",
              data: $(this).serialize(),
              success: function(data) {
                  $('#btnSave').text('Submit');
                  $('#btnSave').attr('disabled', false);

                  if (data.status) {
                      $('#modal_tugas_form').modal('hide');
                      reload_table();
                  } else {
                      for (var i = 0; i < data.inputerror.length; i++) {
                          $('[name="' + data.inputerror[i] + '"]').parent().addClass('has-error');
                          $('[name="' + data.inputerror[i] + '"]').next().text(data.error_string[i]);
                      }
                  }
              },
          });
      });

});

function add_alur_tugas() {
        save_method = 'add';
        $('[name="_method"]').val("POST");
        $('#alur_tugas_form')[0].reset();
        $('#modal_alur_tugas_form').modal('show');
        $('.modal-title').text('Tambah Template Rangkaian Tugas');
    }

function edit_alur_tugas(id) {
   save_method = 'update';
   $('#alur_tugas_form')[0].reset();

   $.ajax({
      url: "<?php echo url('alur-tugas/ajax-edit') ?>/" +id,
      type: "GET",
      dataType: "JSON",
      success: function(data) {
            $('[name="_method"]').val("PUT");
            $('[name="id"]').val(data.alur_tugas.id);           
            $('[name="alur"]').val(data.alur_tugas.alur);       
            $('select[name="kategori"]').val(data.alur_tugas.kategori);     
            $('[name="sasaran"]').val(data.alur_tugas.sasaran);     
            $('[name="indikator"]').val(data.alur_tugas.indikator);     
            $('[name="jumlah_output"]').val(data.alur_tugas.jumlah_output);     
            $('select[name="satuan"]').val(data.alur_tugas.satuan_id);     
            $('#modal_alur_tugas_form').modal('show');
            $('.modal-title').text("Edit Template Rangkaian Tugas");
      },
      error: function(jqXHR, textStatus, errorThrown) {
            alert('Error get data from ajax');
      }
   });
}

function reload_table() {
  table.ajax.reload(null, false);
}

function delete_alur_tugas(id) {
  if (confirm('Are you sure delete this data?')) {
      $.ajaxSetup({
         headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
         }
      });
      $.ajax({
          url: "<?php echo url('alur-tugas/ajax-delete') ?>/" + id,
          type: "DELETE",
          success: function(data) {
              reload_table();
          },
      });
  }
}

function edit_tugas(id) {
  save_method = 'add';
  $('[name="_method"]').val("POST");
  $('[name="id"]').val(id); 
  $('#tugas_form')[0].reset();
  var tugas = $('#tugas');
  tugas.empty(); // clear existing data from tbody
  $.ajax({
      url: "<?php echo url('tugas/ajax-edit') ?>/" +id,
      type: "GET",
      dataType: "JSON",
      success: function(data) {
         var tugas = '';
         if(data.tugas.length > 0) {
            var data = data.tugas
            tugas += '<table class="table">';
            tugas += '<thead><tr><th width="50%">Uraian Tugas</th><th>Urutan</th><th>Durasi Baku</th><th>Output</th><th>Waktu (hari)</th><th width="10%">Action</th></tr></thead>';
            tugas += '<tbody>';
            data.forEach(function(item, index) {
                tugas += '<tr>';
                tugas += '<td><textarea class="form-control" placeholder="Masukkan Tugas" name="tugas[]" id="pesan">' + item.nama_tugas + '</textarea></td>';
                tugas += '<td><input type="number" class="form-control" placeholder="Urutan" name="urutan[]" placeholder="0" value="' + item.urutan +'" required></td>';
                tugas += '<td><input type="text" class="form-control" placeholder="Durasi baku" name="durasi_baku[]" value="' + item.durasi_baku +'"></td>';
                tugas += '<td><input type="text" class="form-control" placeholder="Output" name="output[]" value="' + item.output +'"></td>';
                tugas += '<td><input type="number" class="form-control form-control-sm" name="lama_penyelesaian[]" value="' + item.lama_penyelesaian + '"></td>';
                tugas += '<td>';
                tugas += '<button class="btn btn-success" type="button" onclick="add_input(this)"><i class="fas fa-plus"></i></button>';
                tugas += '<button class="btn btn-danger" type="button" onclick="delete_input(this)"><i class="fas fa-trash"></i></button>';
                tugas += '</td>';
                tugas += '</tr>';
            });
            tugas += '</tbody></table>';
        } else {
            tugas += '<table class="table">';
              tugas += '<thead><tr><th width="50%">Uraian Tugas</th><th>Urutan</th><th>Durasi Baku</th><th>Output</th><th>Waktu (hari)</th><th width="10%">Action</th></tr></thead>';
            tugas += '<tbody>';
            tugas += '<tr>';
            tugas += '<td><textarea class="form-control" placeholder="Masukkan Tugas" name="tugas[]" id="pesan"></textarea></td>';
            tugas += '<td><input type="number" class="form-control" placeholder="Urutan" name="urutan[]" required></td>';
            tugas += '<td><input type="text" class="form-control" placeholder="Durasi baku" name="durasi_baku[]"></td>';
            tugas += '<td><input type="text" class="form-control" placeholder="Output" name="output[]"></td>';
            tugas += '<td><input type="number" class="form-control" placeholder="Hari" name="lama_penyelesaian[]" placeholder="0"></td>';
            tugas += '<td>';
            tugas += '<button class="btn btn-success" type="button" onclick="add_input(this)"><i class="fas fa-plus"></i></button>';
            tugas += '<button class="btn btn-danger" type="button" onclick="delete_input(this)"><i class="fas fa-trash"></i></button>';
            tugas += '</td>';
            tugas += '</tr>';
            tugas += '</tbody></table>';
        }

        $('#tugas').html(tugas);       
      },
      error: function(jqXHR, textStatus, errorThrown) {
            alert('Error get data from ajax');
      }
   });
  $('#modal_tugas_form').modal('show');
  $('.modal-title').text('Tambah Tugas');
}

function add_input(button) {
    // Clone the row containing the clicked button
    var currentRow = $(button).closest("tr");
    var newRow = currentRow.clone();
    
    // Reset values of input elements in the cloned row
    newRow.find("textarea[name='tugas[]']").val("");
    newRow.find("input[name='urutan[]']").val("");
    newRow.find("input[name='durasi_baku[]']").val("");
    newRow.find("input[name='output[]']").val("");
    newRow.find("input[name='lama_penyelesaian[]']").val("");
    
    // Add the new row right after the current row
    currentRow.after(newRow);
}

function delete_input(button) {
    var tugas = $(button).closest('#tugas table');
    var numRows = tugas.find('tbody tr').length;
    if (numRows > 1) {
        $(button).closest('tr').remove();
    }
}
</script>

<!-- Alur tugas Modal -->
<div class="modal fade" id="modal_alur_tugas_form" tab-index="-1" aria-hidden="true" style="display: none;">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel1">Template Rangkaian Tugas</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col mb-3">
            <form id="alur_tugas_form" class="row g-3">
              @csrf
              <input type="hidden" value="" name="_method" />
              <input type="hidden" value="" name="id" />
              <div class="col-12">
                <label class="form-label" for="alur">Nama Rangkaian Tugas</label>
                <input type="text" id="alur" name="alur" class="form-control" />
              </div>
              <div class="col-12">
                <label class="form-label" for="kategori">Kategori</label>
                <select class="form-select" name="kategori">
                  <option value="RENJA">RENJA</option>
                  <option value="SOP">SOP</option>
                  <option value="NON KATEGORI">NON KATEGORI</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label" for="sasaran">Sasaran</label>
                <input type="text" id="sasaran" name="sasaran" class="form-control" />
              </div>
              <div class="col-12">
                <label class="form-label" for="indikator">Indikator</label>
                <input type="text" id="indikator" name="indikator" class="form-control" />
              </div>
              <div class="col-12">
                <label class="form-label" for="jumlah_output">Jumlah Output</label>
                <input type="number" id="jumlah_output" name="jumlah_output" class="form-control" />
              </div>
              <div class="col-12">
                <label class="form-label" for="kategori">Satuan</label>
                <select class="form-select" name="satuan">
                  @foreach ($satuan as $row)
                    <option value="{{ $row->id }}">{{ $row->satuan }}</option>
                  @endforeach
                </select>
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
 <!-- Alur Tugas Modal -->

 <!-- Tugas Modal -->
<div class="modal fade" id="modal_tugas_form" tab-index="-1" aria-hidden="true" style="display: none;">
   <div class="modal-dialog modal-full" role="document">
     <div class="modal-content">
       <div class="modal-header">
         <h5 class="modal-title" id="exampleModalLabel1">Tugas</h5>
         <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
       </div>
       <div class="modal-body">
         <div class="row">
           <div class="col mb-3">
             <form id="tugas_form" class="row g-3">
               @csrf
               <input type="hidden" value="" name="_method" />
               <input type="hidden" value="" name="id" />
               <div id="tugas">
                  <div class="mt-2">
                     <textarea class="form-control" style="height: 100px" placeholder="Masukkan Tugas" name="tugas[]" id="pesan"></textarea>
                     <input type="number" class="form-control" placeholder="Urutan" name="urutan[]">
                     <input type="text" class="form-control" placeholder="Durasi baku" name="durasi_baku[]">
                     <input type="text" class="form-control" placeholder="Output" name="output[]">
                     <input type="text" class="form-control" placeholder="Hari" name="lama_penyelesaian[]">
                     <button class="btn btn-success" type="button" onclick="add_input(this)"><i class='bx bx-plus'></i></button>
                     <button class="btn btn-danger" type="button" onclick="delete_input(this)"><i class='bx bx-trash'></i></button>
                   </div>
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
  <!-- Tugas Modal -->
 