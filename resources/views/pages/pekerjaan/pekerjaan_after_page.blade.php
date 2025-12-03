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
                "url": "{{ url('pekerjaan/ajax-list') }}",
                "type": "POST",
                "data": function(dtRequest) {
                     dtRequest['_token'] = '{{ csrf_token() }}';
                     return dtRequest;
                  }
            },

            "columnDefs": [{
                "targets": [-1, -2, 0, 7],
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

      $('#pekerjaan_form').submit(function(e) {
          e.preventDefault();
          $('#btnSave').text('Saving...');
          $('#btnSave').attr('disabled', true);
          
          if (save_method == 'add') {
            url = "<?php echo url('pekerjaan/ajax-add') ?>";
          } else {
            url = "<?php echo url('pekerjaan/ajax-update') ?>";
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
              data: new FormData(this),
              contentType: false,
              processData: false,
              success: function(data) {
                  $('#btnSave').text('Submit');
                  $('#btnSave').attr('disabled', false);

                  if (data.status) {
                      $('#modal_pekerjaan_form').modal('hide');
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

      $('#proses_pekerjaan_form').submit(function(e) {
          e.preventDefault();
          $('#btnSave').text('Saving...');
          $('#btnSave').attr('disabled', true);
          
          url = "<?php echo url('pekerjaan/ajax-add-proses-pekerjaan') ?>";

          $.ajaxSetup({
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
          });
          $.ajax({
              url: url,
              type: 'PUT',
              dataType: "JSON",
              data: $(this).serialize(),
              success: function(data) {
                  $('#btnSave').text('Submit');
                  $('#btnSave').attr('disabled', false);

                  if (data.status) {
                      $('#modal_proses_pekerjaan_form').modal('hide');
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
          
          if (save_method == 'add') {
            url = "<?php echo url('pekerjaan/ajax-add-tugas') ?>";
          } else {
            url = "<?php echo url('pekerjaan/ajax-update-tugas') ?>";
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

      $('#modal_pekerjaan_form').on('shown.bs.modal', function () {
        var akses_koordinator = "{{ $akses_koordinator }}";
        $("#selectKoordinator").select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#optionKoordinator'),
            placeholder: "Pilih Koordinator",
            ajax: {
              url: "{{ route('pekerjaan.koordinator.index') }}",
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
      });

      $('#modal_tugas_form').on('shown.bs.modal', function () {
        var selectedKategori = $('#tugas_form select[name="kategori"]').val();
        $("#selectAlur").select2({
          theme: 'bootstrap-5',
          dropdownParent: $('#optionAlur'),
          placeholder: "Pilih Alur",
          ajax: {
            url: "{{ route('pekerjaan.alur.index') }}",
            data: function(params) {
                return {
                    kategori: selectedKategori,
                    q: params.term,
                };
            },
            processResults: function(data) {
              return {
                results: $.map(data, function(item){
                  return {
                    id: item.id,
                    text: item.alur
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
      });

      $('#selectAlur').on('change', function() {
        if ($(this).val()) {
          var selectedOption = $(this).select2('data')[0];
          var id_alur = selectedOption.id;
          var id_pekerjaan = $('#tugas_form [name="id"]').val();

          $.ajax({
            url: "<?php echo url('selectTugas') ?>/" + id_alur +'/' + id_pekerjaan,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
              tugas = data.tugas;
              tugas_id = data.tugas_id;
              var tbody = $('#tugas tbody');
              tbody.empty(); // clear existing data from tbody
              var no = 1;
              $.each(tugas, function(index, tugas) {
                var row = $('<tr>').appendTo(tbody);
                var tugasIdValue = tugas_id[index] ?? '';
                $('<td>').text(no++).appendTo(row); // add index column
                $('<td>').html(`<input type="hidden" value="${tugasIdValue}" name='id_tugas[]'> <textarea class="form-control" name='tugas[]' readonly>${tugas.nama_tugas}</textarea>`).appendTo(row);
                $('<td>').html(`<input type="text" class="form-control" name='urutan[]' value="${tugas.urutan}" readonly>`).appendTo(row);
                $('<td>').html(`<input type="text" class="form-control" name='durasi[]' value="${tugas.lama_penyelesaian}">`).appendTo(row);
                $('<td>').html(`<select class="form-select selectPetugas" data-no=${no} id="selectPetugas${no}" name="petugas_id[]" style="width:100%;"></select>
                <div id="optionPetugas"></div>`).appendTo(row);
                $('<td>').html(`<select class="form-select selectAssessment" class="form-control" id="selectAssessment${no}" name="assessment_id[]" style="width:100%;"></select>
                <div id="optionAssessment"></div>`).appendTo(row);

                // inisialisasi select2 untuk input petugas
                $('#selectPetugas' + no).select2({
                  theme: 'bootstrap-5',
                  dropdownParent: $('#optionPetugas'),
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
              
              });
            },
            error: function(jqXHR, textStatus, errorThrown) {
              console.log(textStatus + ': ' + errorThrown);
            }
          });
        }
      });

      $(document).on('change', '.selectPetugas', function () {
        var no = $(this).data('no');
        var selectedPetugas = $(this).val();
        $('#selectAssessment' + no).select2({
          theme: 'bootstrap-5',
          dropdownParent: $('#optionAssessment'),
          placeholder: "Pilih Assessment",
          ajax: {
            url: "{{ route('pekerjaan.assessment.index') }}",
            data: function(params) {
              return {
                  petugas: selectedPetugas,
                  q: params.term
              };
            },
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
            },
            noResults: function () {
              return "Tidak ada hasil yang ditemukan.";
            }
          }
        });
      });
      
      $("#selectKategori").change(function() {
          var selectedKategori = $(this).val();
          
          $("#selectAlur").select2({
              theme: 'bootstrap-5',
              dropdownParent: $('#optionAlur'),
              placeholder: "Pilih Alur",
              ajax: {
                  url: "{{ route('pekerjaan.alur.index') }}",
                  data: function() {
                      return {
                          kategori: selectedKategori,
                      };
                  },
                  processResults: function(data) {
                      return {
                          results: $.map(data, function(item){
                              return {
                                  id: item.id,
                                  text: item.alur
                              };
                          })
                      };
                  }
              },
              language: {
                  searching: function () {
                      return "Mencari...";
                  },
                  noResults: function () {
                    return "Tidak ada hasil yang ditemukan.";
                  }
              }
          });
      });

});

function add_pekerjaan() {
        save_method = 'add';
        if ($('#file_dasar_pelaksanaan').length) {
            $('#file_dasar_pelaksanaan').remove();
        }
        $('[name="_method"]').val("POST");
        $('#pekerjaan_form')[0].reset();
        $('#selectKoordinator').val(null).trigger('change');
        var user_id = {{ $user_id }};
        var name = "{{ $name }}";
        var akses_koordinator = "{{ $akses_koordinator }}";
        $('#modal_pekerjaan_form').modal('show');
        $('.modal-title').text('Tambah Pekerjaan');
}

function edit_pekerjaan(id) {
   save_method = 'update';
   $('#pekerjaan_form')[0].reset();
   $('#selectKoordinator').val(null).trigger('change');

   $.ajax({
      url: "<?php echo url('pekerjaan/ajax-edit') ?>/" +id,
      type: "GET",
      dataType: "JSON",
      success: function(data) {
            if ($('#file_dasar_pelaksanaan').length) {
                $('#file_dasar_pelaksanaan').remove();
            }
            var koordinator_id = data.pekerjaan.koordinator_id;
            var koordinator_name = data.pekerjaan.koordinator.name;
            $('#selectKoordinator').append('<option value="'+ koordinator_id +'" selected="selected">'+ koordinator_name +'</option>');
            $('[name="_method"]').val("PUT");
            $('[name="id"]').val(data.pekerjaan.id);           
            $('[name="pekerjaan"]').val(data.pekerjaan.pekerjaan);           
            $('[name="start_date"]').val(data.pekerjaan.start_date);                 
            $('[name="no_sk"]').val(data.pekerjaan.no_sk);                 
            $('[name="deskripsi_pekerjaan"]').val(data.pekerjaan.deskripsi_pekerjaan);
            $('select[name="kategori"]').val(data.pekerjaan.kategori);

            var file = data.pekerjaan.file_dasar_pelaksanaan;
            var fileUrl  = '{{ url("/") }}' + '/storage/' + file;
            var extension = file.split('.').pop().toLowerCase();

            var downloadLink = '';
            if (extension === 'jpg' || extension === 'png') {
              downloadLink = '<div class="mb-2" id="file_dasar_pelaksanaan"><a href="' + fileUrl + '" download><i class="fas fa-file-image" style="font-size: 35px;"></i></a></div>';
            } else if (extension === 'pdf') {
              downloadLink = '<div class="mb-2" id="file_dasar_pelaksanaan"><a href="' + fileUrl + '" download><i class="fas fa-file-pdf" style="font-size: 35px;"></i></a></div>';
            }
            $('[name="dasar_pelaksanaan_file"]').before(downloadLink);
            $('#modal_pekerjaan_form').modal('show');
            $('.modal-title').text("Edit Pekerjaan");
      },
      error: function(jqXHR, textStatus, errorThrown) {
            alert('Error get data from ajax');
      }
   });
}

function reload_table() {
  table.ajax.reload(null, false);
}

function delete_pekerjaan(id) {
  if (confirm('Are you sure delete this data?')) {
      $.ajaxSetup({
         headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
         }
      });
      $.ajax({
          url: "<?php echo url('pekerjaan/ajax-delete') ?>/" + id,
          type: "DELETE",
          success: function(data) {
              reload_table();
          },
      });
  }
}

function add_tugas(id, kategori) {
  save_method = 'add';
  $('#selectAlur').val(null).trigger('change');
  $.ajax({
      url: "<?php echo url('selectPekerjaanDetail') ?>/" +id,
      type: "GET",
      dataType: "JSON",
      success: function(data) {
            var tbody = $('#tugas tbody');
            tbody.empty(); // clear existing data from tbody
            var no = 1;
            $.each(data, function(index, tugas) {
              var row = $('<tr>').appendTo(tbody);
              $('<td>').text(no++).appendTo(row); // add index column
              $('<td>').html(`<input type="hidden" value="${tugas.id}" name='id_tugas[]'> <textarea class="form-control" name='tugas[]' readonly>${tugas.nama_tugas}</textarea>`).appendTo(row);
              $('<td>').html(`<input type="text" name='urutan[]' class="form-control" value="${tugas.urutan}">`).appendTo(row);
              $('<td>').html(`<input type="text" name='durasi[]' class="form-control" value="${tugas.durasi_plan}">`).appendTo(row);
              $('<td>').html(`<select class="form-select selectPetugas" data-no=${no} id="selectPetugas${no}" name="petugas_id[]" style="width:100%;">
                <option value="${tugas.petugas_id}">${tugas.petugas_name}</option>  
              </select>
              <div id="optionPetugas"></div>`).appendTo(row);
              $('<td>').html(`<select class="form-select selectAssessment" class="form-control"id="selectAssessment${no}" name="assessment_id[]" style="width:100%;">
                <option value="${tugas.assessment_id}">${tugas.assessment_name}</option></select>
              <div id="optionAssessment"></div>`).appendTo(row);
                
              $('#selectPetugas' + no).select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#optionPetugas'),
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
            });

            $('#tugas_form [name="id"]').val(id);       
            $('#tugas_form select[name="kategori"]').val(kategori);                 
            $('#modal_tugas_form').modal('show');
            $('.modal-title').text('Tambah Tugas');
      },
      error: function(jqXHR, textStatus, errorThrown) {
            alert('Error get data from ajax');
      }
   });
  $('[name="_method"]').val("POST");
  $('#tugas_form')[0].reset();
}

function view_tugas(id) {
  $.ajax({
      url: "<?php echo url('selectPekerjaanDetail') ?>/" +id,
      type: "GET",
      dataType: "JSON",
      success: function(data) {
            var tbody = $('#tugas_detail tbody');
            tbody.empty(); // clear existing data from tbody
            var no = 1;
            $.each(data, function(index, tugas) {
              var row = $('<tr>').appendTo(tbody);
              $('<td>').text(no++).appendTo(row); // add index column
              $('<td>').text(tugas.nama_tugas).appendTo(row);
              $('<td class="text-center">').text(tugas.durasi_plan).appendTo(row);
              $('<td>').text(tugas.petugas_name).appendTo(row);
              $('<td>').text(tugas.assessment_name).appendTo(row);
              $('<td>').text(formatDate(tugas.start_date)).appendTo(row);
              $('<td>').text(formatDate(tugas.end_date)).appendTo(row);
            });
            $('#modal_tugas').modal('show');
            $('.modal-title').text('Timeline Tugas');
      },
      error: function(jqXHR, textStatus, errorThrown) {
            alert('Error get data from ajax');
      }
   });
}

function proses_pekerjaan(id) {
  $.ajax({
      url: "<?php echo url('pekerjaan/get-pesan-penugasan') ?>/" +id,
      type: "GET",
      dataType: "JSON",
      success: function(data) {
            $('[name="_method"]').val("PUT");          
            $('[name="pesan_penugasan"]').val(data.pesan_penugasan);           
            $('#proses_pekerjaan_form [name="id"]').val(id);         
            $('#modal_proses_pekerjaan_form').modal('show');
            $('.modal-title').text('Tambah Pesan Penugasan');
      },
      error: function(jqXHR, textStatus, errorThrown) {
            alert('Error get data from ajax');
      }
  });
}

function formatDate(dateString) {
  var date = new Date(dateString);
  var day = date.getDate().toString().padStart(2, '0');
  var month = (date.getMonth() + 1).toString().padStart(2, '0');
  var year = date.getFullYear();
  return day + '-' + month + '-' + year;
}

</script>

<!-- Pekerjaan Modal -->
<div class="modal fade" id="modal_pekerjaan_form" tab-index="-1" aria-hidden="true" style="display: none;">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel1">Pekerjaan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col mb-3">
            <form id="pekerjaan_form" class="row g-3" enctype="multipart/form-data">
              @csrf
              <input type="hidden" value="" name="_method" />
              <input type="hidden" value="" name="id" />
              <div class="col-12">
                <label class="form-label" for="pekerjaan">Pekerjaan</label>
                <input type="text" id="pekerjaan" name="pekerjaan" class="form-control" />
              </div>
              <div class="col-12">
                <label class="form-label" for="deskripsi_pekerjaan">Deskripsi Pekerjaan</label>
                <input type="text" id="deskripsi_pekerjaan" name="deskripsi_pekerjaan" class="form-control" />
              </div>
              <div class="col-12">
                <label class="form-label" for="start_date">Tanggal Mulai</label>
                <input type="date" id="start_date" name="start_date" class="form-control" />
              </div>
              <div class="col-12">
                <label class="form-label" for="no_sk">Dasar Pelaksanaan (No SK / No Peraturan)</label>
                <input type="text" id="no_sk" name="no_sk" class="form-control" />
              </div>
              <div class="col-12">
                <label class="form-label" for="dasar_pelaksanaan_file">Dokumen Dasar Pelaksanaan</label>
                <input type="file" id="dasar_pelaksanaan_file" name="dasar_pelaksanaan_file" class="form-control" />
              </div>
              <div class="col-12">
                <label class="form-label" for="selectKoordinator">Koordinator</label>
                <select class="form-select" id="selectKoordinator" name="koordinator_id">
                </select>
                <div id="optionKoordinator"></div>
              </div>
              <div class="col-12">
                <label class="form-label" for="kategori">Kategori</label>
                <select class="form-select" name="kategori">
                  <option value="RENJA">RENJA</option>
                  <option value="SOP">SOP</option>
                  <option value="NON KATEGORI">NON KATEGORI</option>
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
 <!-- Pekerjaan Modal -->

<!-- Alur Modal -->
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
              <div class="row g-3 mb-3">
                <div class="col-12">
                  <label class="form-label" for="kategori">Kategori</label>
                  <select class="form-select" id="selectKategori" name="kategori">
                    <option value="RENJA">RENJA</option>
                    <option value="SOP">SOP</option>
                    <option value="NON KATEGORI">NON KATEGORI</option>
                  </select>
                </div>
                <div class="col-12">
                  <label class="form-label" for="selectAlur">Pilih Alur</label>
                  <select class="form-select" id="selectAlur" name="alur_id">
                  </select>
                  <div id="optionAlur"></div>
                </div>
              </div>
               
                <input type="hidden" value="" name="_method" />
                <input type="hidden" value="" name="id" />
                <input type="hidden" value="" name="id_pekerjaan" />
                <div id="tugas" class="mt-3">
                  <table class="table">
                    <thead>
                      <tr>
                        <th scope="col">#</th>
                        <th scope="col" width="50%">Tugas</th>
                        <th scope="col">Urutan</th>
                        <th scope="col">Lama Penyelesaian (Hari)</th>
                        <th scope="col" width="20%">Petugas</th>
                        <th scope="col" width="20%">Assesor</th>
                      </tr>
                    </thead>
                    <tbody>
                      
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="col-12 mb-2">
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
 <!-- Alur Modal -->


<!-- Tugas Modal -->
<div class="modal fade" id="modal_tugas" tab-index="-1" aria-hidden="true" style="display: none;">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel1">Tugas</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col mb-3">
            <div class="col-12">
                <div id="tugas_detail" class="mt-3">
                  <table class="table">
                    <thead>
                      <tr>
                        <th scope="col">#</th>
                        <th scope="col">Tugas</th>
                        <th scope="col" class="text-center">Lama Penyelesaian (Hari)</th>
                        <th scope="col">Petugas</th>
                        <th scope="col">Assessment</th>
                        <th scope="col">Tanggal Mulai</th>
                        <th scope="col">Tanggal Selesai</th>
                      </tr>
                    </thead>
                    <tbody>
                      
                    </tbody>
                  </table>
                </div>
              </div>
          </div>
        </div>
      </div>
     </div>
   </div>
 </div>
 <!-- Tugas Modal -->

<!-- Pekerjaan Modal -->
<div class="modal fade" id="modal_proses_pekerjaan_form" tab-index="-1" aria-hidden="true" style="display: none;">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel1">Pesan Penugasan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col mb-3">
            <form id="proses_pekerjaan_form" class="row g-3">
              @csrf
              <input type="hidden" value="" name="_method" />
              <input type="hidden" value="" name="id" />
              <div class="col-12">
                <label class="form-label" for="pesan_penugasan">Pesan Penugasan</label>
                <input type="text" id="pesan_penugasan" name="pesan_penugasan" class="form-control" />
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
 <!-- Pekerjaan Modal -->