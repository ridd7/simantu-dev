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
                "url": "{{ url('users/ajax-list') }}",
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
         $('#users_form').find('.is-invalid').removeClass('is-invalid');
         $('#users_form').find('.invalid-feedback').text('');
      });

      $('#users_form').submit(function(e) {
          e.preventDefault();
          $('#btnSave').text('Saving...');
          $('#btnSave').attr('disabled', true);
          
          if (save_method == 'add') {
            url = "<?php echo url('users/ajax-add') ?>";
          } else {
            url = "<?php echo url('users/ajax-update') ?>";
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
                     $('#modal_users_form').modal('hide');
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

      $('#change_password_form').submit(function(e) {
          e.preventDefault();
          $('#btnSave').text('Saving...');
          $('#btnSave').attr('disabled', true);
          
          url = "<?php echo url('users/change-password') ?>";
         
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
                      $('#modal_change_password').modal('hide');
                      reload_table();
                  } else {
                     $('#change_password_form').find('.is-invalid').removeClass('is-invalid');
                     $('#change_password_form').find('.invalid-feedback').text('');
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

      // Mengamati perubahan pada input file
      $('#file_photo').on('change', function() {
          var file = $(this)[0].files[0];
          var reader = new FileReader();
          
          reader.onload = function(e) {
              var fileUrl = e.target.result;
              var photo_profile = `<img src="${fileUrl}" class="rounded-circle" alt="Foto Profil" style="width:100px; height:100px">`;
              $('#preview_photo').html(photo_profile);
              $('#preview_photo').show();
          }
          
          reader.readAsDataURL(file);
      });
})

function reload_table() {
  table.ajax.reload(null, false);
}

function add_user() {
   save_method = 'add';
   $('[name="_method"]').val("POST");
   $('#preview_photo').html('');
   $('#preview_photo').hide();
   $('#users_form')[0].reset();
   $('#users_form').find('.is-invalid').removeClass('is-invalid');
   $('#users_form').find('.invalid-feedback').text('');
   $('#modal_users_form').modal('show');
   $('.modal-title').text('Tambah User');
   // Add password and confirm password sections if they don't exist
    if (!$('#password-section').length) {
      var passwordSectionHtml = `
        <div id="password-section" class="col-12">
          <label class="form-label" for="password">Password</label>
          <input type="password" id="password" name="password" class="form-control" />
          <div class="invalid-feedback d-block" id="password-error"></div>
        </div>
      `;
      $('#users_form #submit').before(passwordSectionHtml);
    }
    if (!$('#confirm-password-section').length) {
      var confirmPasswordSectionHtml = `
        <div id="confirm-password-section" class="col-12">
          <label class="form-label" for="confirm_password">Konfirmasi Password</label>
          <input type="password" id="confirm_password" name="confirm_password" class="form-control" />
          <div class="invalid-feedback d-block" id="confirm_password-error"></div>
        </div>
      `;
      $('#users_form #submit').before(passwordSectionHtml);
    }
}

function edit_user(id) {
   save_method = 'update';
   $('#preview_photo').html('');
   $('#preview_photo').hide();
   $('#password-section, #confirm-password-section').remove();
   $('#users_form')[0].reset();
   $('#users_form').find('.is-invalid').removeClass('is-invalid');
   $('#users_form').find('.invalid-feedback').text('');
   $.ajax({
      url: "<?php echo url('users/ajax-edit') ?>/" +id,
      type: "GET",
      dataType: "JSON",
      success: function(data) {
            $('[name="_method"]').val("PUT");
            $('[name="id"]').val(data.user.id);           
            $('[name="name"]').val(data.user.name);                      
            $('[name="username"]').val(data.user.username);                      
            $('[name="email"]').val(data.user.email);                      
            $('[name="nip"]').val(data.user.nip);                      
            $('[name="pangkat"]').val(data.user.pangkat);                      
            $('[name="jabatan"]').val(data.user.jabatan);     
            if (data.user.koordinator_akses === 'Y') {
              $('[name="koordinator_akses"]').filter('[value="Y"]').prop('checked', true);
            } else {
              $('[name="koordinator_akses"]').filter('[value="N"]').prop('checked', true);
            }           
            if (data.user.manager_akses === 'Y') {
              $('[name="manager_akses"]').filter('[value="Y"]').prop('checked', true);
            } else {
              $('[name="manager_akses"]').filter('[value="N"]').prop('checked', true);
            }   
            $('[name="level"]').val(data.user.level);           
            var file_photo = data.user.photo_profile;
            if(file_photo) {
              var fileUrl  = '{{ url("/") }}' + '/storage/' + file_photo;
              var photo_profile = `<img src="${fileUrl}" class="rounded-circle" width="100px" height="100px">`
              $('#preview_photo').html(photo_profile);
              $('#preview_photo').show();
            }
            $('#modal_users_form').modal('show');
            $('.modal-title').text("Edit User");
      },
      error: function(jqXHR, textStatus, errorThrown) {
            alert('Error get data from ajax');
      }
   });
}

function delete_user(id) {
  if (confirm('Are you sure delete this data?')) {
      $.ajaxSetup({
         headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
         }
      });
      $.ajax({
          url: "<?php echo url('users/ajax-delete') ?>/" + id,
          type: "DELETE",
          success: function(data) {
              reload_table();
          },
      });
  }
}

function change_password($id) {
      $('#modal_change_password').modal('show');
      $('#change_password_form').find('.is-invalid').removeClass('is-invalid');
      $('#change_password_form').find('.invalid-feedback').text('');
      $('#change_password_form')[0].reset();
      $('[name="id_user"]').val($id);
}

</script>

<!-- Change Password Modal -->
<div class="modal fade" id="modal_change_password" tab-index="-1" aria-hidden="true" style="display: none;">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel1">Ubah Password</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col mb-3">
            <form id="change_password_form" class="row g-3">
              @csrf
              <input type="hidden" name="id_user">
              <div class="col-12">
                <label class="form-label" for="password">Password Baru</label>
                <input type="password" id="password" name="password" class="form-control" />
                <div class="invalid-feedback d-block" id="password-error"></div>
              </div>
              <div class="col-12">
                <label class="form-label" for="confirm_password">Konfirmasi Password Baru</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" />
                <div class="invalid-feedback d-block" id="confirm_password-error"></div>
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
 <!-- Change Password Modal -->

<!-- Users Modal -->
<div class="modal fade" id="modal_users_form" tab-index="-1" aria-hidden="true" style="display: none;">
   <div class="modal-dialog" role="document">
     <div class="modal-content">
       <div class="modal-header">
         <h5 class="modal-title" id="exampleModalLabel1">Users</h5>
         <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
       </div>
       <div class="modal-body">
         <div class="row">
           <div class="col mb-3">
             <form id="users_form" class="row g-3" enctype="multipart/form-data">
               @csrf
               <input type="hidden" value="" name="_method" />
               <input type="hidden" value="" name="id" />
               <div class="col-12">
                 <label class="form-label" for="name">Nama</label>
                 <input type="text" id="name" name="name" class="form-control" />
                 <div class="invalid-feedback d-block" id="name-error"></div>
               </div>
               <div class="col-12">
                 <label class="form-label" for="nip">NIP</label>
                 <input type="text" id="nip" name="nip" class="form-control" />
                 <div class="invalid-feedback d-block" id="nip-error"></div>
               </div>
               <div class="col-12">
                  <label class="form-label" for="username">Username</label>
                  <input type="text" id="username" name="username" class="form-control" />
                  <div class="invalid-feedback d-block" id="username-error"></div>
                </div>
               <div class="col-12">
                 <label class="form-label" for="email">Email</label>
                 <input type="email" id="email" name="email" class="form-control" />
                 <div class="invalid-feedback d-block" id="email-error"></div>
               </div>
               <div class="col-12">
                 <label class="form-label" for="pangkat">Pangkat</label>
                 <input type="pangkat" id="pangkat" name="pangkat" class="form-control" />
                 <div class="invalid-feedback d-block" id="pangkat-error"></div>
               </div>
               <div class="col-12">
                 <label class="form-label" for="email">Jabatan</label>
                 <input type="jabatan" id="jabatan" name="jabatan" class="form-control" />
                 <div class="invalid-feedback d-block" id="jabatan-error"></div>
               </div>
               <div class="row mt-4">
                   <div class="col-5">
                     <label class="form-label" for="manager_akses">Akses Manager</label>
                   </div>
                   <div class="col-7">
                     <ul class="list-unstyled mb-0">
                       <li class="d-inline-block me-2 mb-1">
                         <div class="form-check">
                           <input class="form-check-input" type="radio" name="manager_akses" id="manager_akses" value="Y">
                           <label class="form-check-label" for="manager_akses">
                             Ya
                           </label>
                         </div>
                       </li>
                       <li class="d-inline-block me-2 mb-1">
                         <div class="form-check">
                           <input class="form-check-input" type="radio" name="manager_akses" id="manager_akses" value="N">
                           <label class="form-check-label" for="manager_akses">
                             Tidak
                           </label>
                         </div>
                       </li>
                     </ul>
                   </div>
                   <div class="invalid-feedback d-block" id="manager_akses-error"></div>
               </div>
               <div class="row mt-4">
                   <div class="col-5">
                     <label class="form-label" for="koordinator_akses">Akses Koordinator</label>
                   </div>
                   <div class="col-7">
                     <ul class="list-unstyled mb-0">
                       <li class="d-inline-block me-2 mb-1">
                         <div class="form-check">
                           <input class="form-check-input" type="radio" name="koordinator_akses" id="koordinator_akses" value="Y">
                           <label class="form-check-label" for="koordinator_akses">
                             Ya
                           </label>
                         </div>
                       </li>
                       <li class="d-inline-block me-2 mb-1">
                         <div class="form-check">
                           <input class="form-check-input" type="radio" name="koordinator_akses" id="koordinator_akses" value="N">
                           <label class="form-check-label" for="koordinator_akses">
                             Tidak
                           </label>
                         </div>
                       </li>
                     </ul>
                   </div>
                   <div class="invalid-feedback d-block" id="koordinator_akses-error"></div>
               </div>
               <div id="preview_photo" class="mb-2"></div>
               <div class="col-12 form-file">
                <label class="form-label" for="file">Foto Profil</label>
                <input type="file" id="file_photo" name="file_photo" class="form-control" />
               </div>
               <div id="password-section" class="col-12">
                 <label class="form-label" for="passsword">Password</label>
                 <input type="password" id="password" name="password" class="form-control" />
                 <div class="invalid-feedback d-block" id="password-error"></div>
               </div>
               <div id="confirm-password-section" class="col-12">
                 <label class="form-label" for="confirm_password">Konfirmasi Password</label>
                 <input type="password" id="confirm_password" name="confirm_password" class="form-control" />
                 <div class="invalid-feedback d-block" id="confirm_password-error"></div>
               </div>   
               <div class="col-12">
                <label class="form-label" for="level">Level</label>
                <input type="number" id="confirm_password" name="level" class="form-control" />
              </div>    
               <div class="col-12 text-center" id="submit">
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
  <!-- Users Modal -->