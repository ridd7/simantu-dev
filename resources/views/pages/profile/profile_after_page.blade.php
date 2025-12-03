<script>
  $(document).ready(function() {
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

   function change_password() {
      $('#modal_change_password').modal('show');
      $('#change_password_form')[0].reset();
   }

   function edit_photo() {
      $('#preview_photo').html('');
      $('#preview_photo').hide();
      $('#modal_edit_photo_profile').modal('show');
      $('#edit_photo_profile_form')[0].reset();
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
             <form id="change_password_form" class="row g-3" method="POST" action="{{ url('profile/change-password') }}">
               @csrf
               <div class="col-12">
                 <label class="form-label" for="password">Password Lama</label>
                 <input type="password" id="password" name="old_password" class="form-control" />
               </div>
               <div class="col-12">
                 <label class="form-label" for="new_password">Password Baru</label>
                 <input type="password" id="new_password" name="new_password" class="form-control" />
               </div>
               <div class="col-12">
                 <label class="form-label" for="confirm_new_password">Konfirmasi Password Baru</label>
                 <input type="password" id="confirm_new_password" name="confirm_new_password" class="form-control" />
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

<!-- Edit Photo Modal -->
<div class="modal fade" id="modal_edit_photo_profile" tab-index="-1" aria-hidden="true" style="display: none;">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel1">Ubah Foto Profil</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col mb-3">
            <form id="edit_photo_profile_form" class="row g-3" method="POST" action="{{ url('profile/update-photo-profile') }}" enctype="multipart/form-data">
              @csrf
              <div id="preview_photo" class="mb-2"></div>
              <div class="col-12 form-file">
               <label class="form-label" for="file">Foto Profil</label>
               <input type="file" id="file_photo" name="file_photo" class="form-control" />
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
 <!-- Edit Photo Modal -->