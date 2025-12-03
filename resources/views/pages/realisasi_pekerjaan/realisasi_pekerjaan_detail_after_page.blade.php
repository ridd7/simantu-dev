<script type="text/javascript">
   var table_detail_pekerjaan;
   var table_proses_tugas;
   var TableAjax;


   TableAjax = function() {
    var handleRecords = function() {
      table_detail_pekerjaan = $('#datatable_ajax_detail_pekerjaan').DataTable({
            "scrollX": true,
            "processing": true,
            "serverSide": true,
            "pagingType": "full_numbers",
            "order": [],
            "ajax": {
               "url": "{{ url('realisasi-pekerjaan/detail-ajax-list/' . $id_pekerjaan) }}",
               "type": "POST",
               "data": function(dtRequest) {
                     dtRequest['_token'] = '{{ csrf_token() }}';
                     return dtRequest;
                  }
            },

            'columnDefs': [
               {
                  "targets": 5, // your case first column
                  "className": "text-center",
               },
               {
                  "targets": 6, // your case first column
                  "className": "text-center",
               },
               {
                "targets": [-4, -3, -2, -1, 0],
                "orderable": false,
               }
            ]

        });

        table_proses_tugas = $('#datatable_ajax_proses_tugas').DataTable({
            "scrollX": true,
            "processing": true,
            "serverSide": true,
            "pagingType": "full_numbers",
            "order": [],
            "ajax": {
               "url": "{{ url('realisasi-pekerjaan/proses-ajax-list/' . $id_pekerjaan) }}",
               "type": "POST",
               "data": function (dtRequest) {
                     dtRequest['_token'] = '{{ csrf_token() }}';
                     return dtRequest;
               }
            },
            'columnDefs': [
               {
                     "targets": 4, // Replace with the appropriate column index you want to target
                     "className": "text-center"
               },
               {
                "targets": [-1, 0],
                "orderable": false,
               }
               // Add more columnDefs as needed
            ]
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

        $('#progress_tugas_form').submit(function(e) {
         e.preventDefault();
         $('#btnSave').text('Saving...');
         $('#btnSave').attr('disabled', true);

         url = "{{ url('realisasi-pekerjaan/ajax-update-progress') }}";

         $.ajaxSetup({
            headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
         });

         $.ajax({
            url: url,
            type: 'POST',
            dataType: 'JSON',
            data: $(this).serialize(),
            success: function(data) {
                  $('#btnSave').text('Submit');
                  $('#btnSave').attr('disabled', false);

                  if (data.status) {
                     $('#modal_progress_tugas_form').modal('hide');
                     reload_table_detail_pekerjaan();
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

function reload_table_detail_pekerjaan() {
   table_detail_pekerjaan.ajax.reload(null, false);
}

function reload_table_proses_tugas() {
  table_proses_tugas.ajax.reload(null, false);
}

function formatDate(dateString) {
  var date = new Date(dateString);
  var day = date.getDate().toString().padStart(2, '0');
  var month = (date.getMonth() + 1).toString().padStart(2, '0');
  var year = date.getFullYear();
  return day + '-' + month + '-' + year;
}

function add_laporan(id, status) {
   save_method = 'add';
   // $('[name="_method"]').val("POST");
   $('#laporan_form')[0].reset();
   $('[name="id"]').val(id);    
   $('[name="id_pekerjaan"]').val({{ $id_pekerjaan }});    
   $('[name="status"]').val(status);    
   $('#modal_laporan_form').modal('show');
   $('.modal-title').text(`${status} Laporan`);

   $('.form-file').remove(); // Menghapus bagian file laporan
   if (status === 'Submit') {
      if ($('.form-file').length === 0) {
         // Membuat kembali elemen file laporan jika sebelumnya dihapus
         var fileInput = '<div class="col-12 form-file">' +
                           '<label class="form-label" for="file">File laporan</label>' +
                           '<input type="file" id="file" name="file" class="form-control" />' +
                        '</div>';
         $('#laporan_form').prepend(fileInput);
      }
   }
}

function feedback_laporan(id, status, urutan) {
   $('#feedback_laporan_form')[0].reset();
   if ($('.select-petugas').length) {
      $('.select-petugas').remove();
   }
   if ($('.form-nilai').length) {
      $('.form-nilai').remove();
   }

   if(status == 'approved') {
     var formNilai = `<div class="col-12 form-nilai">
       <label class="form-label" for="nilai">Nilai</label>
       <input type="number" class="form-control" name="nilai" id="nilai"></input>
     </div>`
     $('#feedback_laporan_form').prepend(formNilai);
   }
   
   $('[name="id"]').val(id);    
   $('[name="id_pekerjaan"]').val({{ $id_pekerjaan }}); 
   $('[name="status"]').val(status); 
   $('[name="urutan"]').val(urutan); 
   $('#modal_feedback_laporan_form').modal('show');
   $('.modal-title').text(`${status} Laporan`);
}

function feedback_stuck_laporan(id, urutan) {
   $('#feedback_laporan_form')[0].reset();
   if ($('.select-petugas').length) {
      $('.select-petugas').remove();
   }
   if ($('.form-nilai').length) {
      $('.form-nilai').remove();
   }
   var selectPetugas = `<div class="col-12 select-petugas">
                          <label class="form-label" for="selectPetugas">Petugas</label>
                          <select class="form-select" id="selectPetugas" name="petugas_id">
                          </select>
                        </div>`;
    $('#feedback_laporan_form').prepend(selectPetugas);
    
    // inisialisasi select2 untuk input petugas pada baris ini
    if ($('#selectPetugas').length) {
      $('#selectPetugas').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#modal_feedback_laporan_form'),
        placeholder: "Pilih Petugas",
        ajax: {
          url:"{{ url('selectPetugasByAssessment?level=' . $level_assessment) }}",
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
    }
    
   $('[name="id"]').val(id);    
   $('[name="id_pekerjaan"]').val({{ $id_pekerjaan }}); 
   $('[name="urutan"]').val(urutan); 
   $('[name="status"]').val('change'); 
   $('#modal_feedback_laporan_form').modal('show');
   $('.modal-title').text(`${status} Laporan`);
}

function add_progress(id) {
  $('[name="id"]').val(id);    
  
  $('#progress_tugas_form')[0].reset();

  $.ajax({
      url: "<?php echo url('realisasi-pekerjaan/ajax-edit-progress') ?>/" +id,
      type: "GET",
      dataType: "JSON",
      success: function(data) {
            $('[name="_method_progress"]').val("PUT");
            $('[name="id"]').val(data.tugas.id);           
            $('[name="progress"]').val(data.tugas.progress);                                
            $('#modal_progress_tugas_form').modal('show');
      },
      error: function(jqXHR, textStatus, errorThrown) {
            alert('Error get data from ajax');
      }
   });
  }

  function checkProgress(input) {
      if (input.value > 100) {
        input.value = 100;
      }
  }

</script>

<script>
  var events = @json($events);  
  var tasks = [
    <?php 
    foreach ($events as $key => $event): 
      $urutan = $event['urutan'];
      ?>
      {
        start: "<?php echo $event['start']; ?>",
        end: "<?php echo $event['end']; ?>",
        name: "<?php echo $event['title'] . ' ( ' . $event['name'] . ' )'; ?>",
        id: "Task <?php echo $urutan ?>",
        progress: "<?php echo $event['progress']; ?>",
        <?php if ($urutan - 1 != 0) : ?>
        dependencies: "Task <?php echo $urutan - 1; ?>",
        <?php endif; ?>
      },
    <?php endforeach; ?>
  ]
  var gantt = new Gantt('#gantt', tasks, {
    on_click: function (task) {
      // console.log(task);
    },
    on_date_change: function(task, start, end) {
      // console.log(task, start, end);
    },
    on_progress_change: function(task, progress) {
      // console.log(task, progress);
    },
    on_view_change: function(mode) {
      // console.log(mode);
    }
  });

  var tasks2 = [
    <?php 
    foreach ($events as $key => $event): 
      $urutan = $event['urutan'];
      ?>
      {
        start: "<?php echo $event['real_start']; ?>",
        end: "<?php echo $event['real_end']; ?>",
        name: "<?php echo $event['title'] . ' ( ' . $event['name'] . ' )'; ?>",
        id: "Task <?php echo $urutan ?>",
        progress: "<?php echo $event['progress']; ?>",
        <?php if ($urutan - 1 != 0) : ?>
        dependencies: "Task <?php echo $urutan - 1; ?>",
        <?php endif; ?>
      },
    <?php endforeach; ?>
  ]
  var gantt2 = new Gantt('#gantt2', tasks2, {
    on_click: function (task) {
      // console.log(task);
    },
    on_date_change: function(task, start, end) {
      // console.log(task, start, end);
    },
    on_progress_change: function(task, progress) {
      // console.log(task, progress);
    },
    on_view_change: function(mode) {
      // console.log(mode);
    }
  });
</script>



<!-- Submit Laporan Modal -->
<div class="modal fade" id="modal_laporan_form" tab-index="-1" aria-hidden="true" style="display: none;">
   <div class="modal-dialog" role="document">
     <div class="modal-content">
       <div class="modal-header">
         <h5 class="modal-title" id="exampleModalLabel1">Submit Laporan</h5>
         <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
       </div>
       <div class="modal-body">
         <div class="row">
           <div class="col mb-3">
             <form id="laporan_form" class="row g-3" action="{{ url('realisasi-pekerjaan/add-laporan') }}" method="POST" enctype="multipart/form-data">
               @csrf
               <input type="hidden" value="" name="id" />
               <input type="hidden" value="" name="id_pekerjaan" />
               <input type="hidden" value="" name="status" />
               <div class="col-12 form-file">
                  <label class="form-label" for="file">File laporan</label>
                  <input type="file" id="file" name="file" class="form-control" />
                </div>
               <div class="col-12">
                 <label class="form-label" for="pesan">Isi Pesan / Keterangan</label>
                 <textarea class="form-control" style="height: 100px" name="pesan" id="pesan"></textarea>
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
<!-- Submit Laporan Modal -->

<!-- Feedback Laporan Modal -->
<div class="modal fade" id="modal_feedback_laporan_form" tab-index="-1" aria-hidden="true" style="display: none;">
   <div class="modal-dialog" role="document">
     <div class="modal-content">
       <div class="modal-header">
         <h5 class="modal-title" id="exampleModalLabel1">Feedback Laporan</h5>
         <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
       </div>
       <div class="modal-body">
         <div class="row">
           <div class="col mb-3">
             <form id="feedback_laporan_form" class="row g-3" method="POST" action="{{ url('realisasi-pekerjaan/feedback-laporan') }}">
               @csrf
               <input type="hidden" value="" name="id" />
               <input type="hidden" value="" name="id_pekerjaan" />
               <input type="hidden" value="" name="status" />
               <input type="hidden" value="" name="urutan" />

               <div class="col-12 form-nilai">
                 <label class="form-label" for="nilai">Nilai</label>
                 <input type="number" class="form-control" name="nilai" id="nilai" min="0" max="100"></input>
               </div>
               <div class="col-12">
                 <label class="form-label" for="pesan">Keterangan</label>
                 <textarea class="form-control" style="height: 100px" name="pesan" id="pesan"></textarea>
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
  <!-- Feedback Laporan Modal -->

  <!-- Progress Tugas Modal -->
<div class="modal fade" id="modal_progress_tugas_form" tab-index="-1" aria-hidden="true" style="display: none;">
   <div class="modal-dialog" role="document">
     <div class="modal-content">
       <div class="modal-header">
         <h5 class="modal-title" id="exampleModalLabel1">Progress Tugas</h5>
         <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
       </div>
       <div class="modal-body">
         <div class="row">
           <div class="col mb-3">
             <form id="progress_tugas_form" class="row g-3" method="POST" action="{{ url('realisasi-pekerjaan/update-progress') }}">
               @csrf
               <input type="hidden" value="" name="_method_progress" />
               <input type="hidden" value="" name="id" />

               <div class="col-12">
                 <label class="form-label" for="progress">Input Persentase (%) Progress</label>
                 <input type="number" class="form-control" name="progress" id="progress" min="0" max="100" oninput="checkProgress(this)"></input>
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
  <!-- Progress Tugas Modal -->

