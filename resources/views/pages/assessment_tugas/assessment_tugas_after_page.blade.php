<script type="text/javascript">
   var save_method;
   var table;
   var TableAjax;
   var TableAjax2;


TableAjax = function() {
    var handleRecords = function() {
        table = $('#datatable_ajax').DataTable({
            "scrollX": true,
            "processing": true,
            "serverSide": true,
            "pagingType": "full_numbers",
            "order": [],
            "ajax": {
                "url": "{{ url('assessment-tugas/ajax-list') }}",
                "type": "POST",
                "data": function(dtRequest) {
                     dtRequest['_token'] = '{{ csrf_token() }}';
                     return dtRequest; 
                  }
            },

            "columnDefs": [{
                "targets": [-4, -3, -2, -1, 0],
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

var TableAjax2 = (function() {
  var initialized = false;
  var pekerjaan_id = null;
  var table_proses_tugas = null;

  function handleRecords(id_pekerjaan) {
    table_proses_tugas = $('#datatable_ajax_proses_tugas').DataTable({
      "scrollX": true,
      "processing": true,
      "serverSide": true,
      "pagingType": "full_numbers",
      "order": [],
      "ajax": {
         "url": "{{ url('realisasi-pekerjaan/proses-ajax-list/') }}" + "/" + id_pekerjaan,
         "type": "POST",
         "data": function(dtRequest) {
           dtRequest['_token'] = '{{ csrf_token() }}';
           return dtRequest;
         }
      },
      "columnDefs": [
         {
           "targets": 4, // Replace with the appropriate column index you want to target
           "className": "text-center"
         },
         {
          "targets": [-1, 0],
          "orderable": false
         }
         // Add more columnDefs as needed
      ]
    });
  }

  function init(id_pekerjaan) {
    if (pekerjaan_id !== id_pekerjaan) {
      pekerjaan_id = id_pekerjaan;
      initialized = false;
      if (table_proses_tugas !== null) {
        table_proses_tugas.destroy(); // Destroy the existing table instance
      }
    }
    if (!initialized) {
      handleRecords(id_pekerjaan);
      initialized = true;
    }
  }

  return {
    init: init
  };
})();

$(document).ready(function() {
      TableAjax.init();
});

function reload_table() {
  table.ajax.reload(null, false);
}

function view_timeline(id) {
    $('.timeline').empty();
    $.ajax({
      url: "<?php echo url('realisasi-pekerjaan/timeline-ajax') ?>/" +id,
      type: "GET",
      dataType: "JSON",
      success: function(data) {
        $.each(data.tugas, function(index, tugas) {
                var namaPetugas = tugas.petugas_name;
                var namaTugas = tugas.nama_tugas;
                var startDate = formatDate(tugas.start_date);
                var isActive = data.tugas_active && data.tugas_active.id ? data.tugas_active.id === tugas.id : false;
                var isFinished = tugas.status_tugas == 'approved';
                if(isActive) {
                  var activityItem = '<div class="timeline-item finished">';
                } else if(isFinished) {
                  var activityItem = '<div class="timeline-item active">';
                } else {
                  var activityItem = '<div class="timeline-item">';
                }

                activityItem += '<div class="timeline-badge"></div>';
                activityItem += `<div class="timeline-content">
                                  <h6 class="timeline-title">${namaTugas}</h6>
                                  <p class="timeline-description">${namaPetugas}</p>
                                  <span class="timeline-date">${startDate}</span>
                                </div>`
                activityItem += '</div>';

                // Menambahkan elemen activityItem ke dalam elemen timeline
                $('.timeline').append(activityItem);
            });
            $('#modal_timeline').modal('show');
            $('.modal-title').text('Timeline Pekerjaan');
      },
      error: function(jqXHR, textStatus, errorThrown) {
            alert('Error get data from ajax');
      }
   });
}

function view_dialog(id_pekerjaan) {
  $('#modal_dialog').modal('show');
  $('.modal-title').text('Pesan');
  TableAjax2.init(id_pekerjaan);
}

function formatDate(dateString) {
  var date = new Date(dateString);
  var day = date.getDate().toString().padStart(2, '0');
  var month = (date.getMonth() + 1).toString().padStart(2, '0');
  var year = date.getFullYear();
  return day + '-' + month + '-' + year;
}

function feedback_laporan(id, status, urutan, pekerjaan_id) {
   save_method = 'add';
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
   $('[name="id_pekerjaan"]').val(pekerjaan_id); 
   $('[name="status"]').val(status); 
   $('[name="urutan"]').val(urutan); 
   $('#modal_feedback_laporan_form').modal('show');
   $('.modal-title').text(`${status} Laporan`);
}

function feedback_stuck_laporan(id, urutan, pekerjaan_id) {
   save_method = 'add';
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
   $('[name="id_pekerjaan"]').val(pekerjaan_id); 
   $('[name="urutan"]').val(urutan); 
   $('[name="status"]').val('change'); 
   $('#modal_feedback_laporan_form').modal('show');
   $('.modal-title').text(`${status} Laporan`);
}

</script>

<!-- Timeline Modal -->
<div class="modal fade" id="modal_timeline" tab-index="-1" aria-hidden="true" style="display: none;">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel1">Timeline Pekerjaan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <section class="section dashboard">

          <div class="row">
            <div class="col mb-3">
              <div class="timeline">
                
              </div>
            </div>
          </div>
        </section>
      </div>
     </div>
   </div>
 </div>
<!-- Timeline Modal -->

<!-- Dialog Modal -->
<div class="modal fade" id="modal_dialog" tab-index="-1" aria-hidden="true" style="display: none;">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel1">Dialog Pekerjaan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <section class="section dashboard">

          <div class="row">
            <div class="col mb-3">
              <table class="table table-border" id="datatable_ajax_proses_tugas" style="width: 100%;">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Pengirim</th>
                    <th scope="col">Pesan</th>
                    <th scope="col">Tanggal Pesan</th>
                    <th scope="col">File</th>
                  </tr>
                </thead>
                <tbody>         
                </tbody>
              </table>
            </div>
          </div>
        </section>
      </div>
     </div>
   </div>
 </div>
<!-- Dialog Modal -->

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
               {{-- <input type="hidden" value="" name="_method" /> --}}
               <input type="hidden" value="" name="id" />
               <input type="hidden" value="" name="id_pekerjaan" />
               <input type="hidden" value="" name="status" />
               <input type="hidden" value="" name="urutan" />

               <div class="col-12 form-nilai">
                 <label class="form-label" for="niali">Nilai</label>
                 <input type="number" class="form-control" name="nilai" id="nilai"></input>
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



</script>
