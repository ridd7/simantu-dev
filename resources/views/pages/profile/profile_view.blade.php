<div class="row">
   <div class="col-lg-12">
      @if (session('success'))
         <div class="alert alert-success">
               {{ session('success') }}
         </div>
      @endif
      @if (session('error'))
         <div class="alert alert-danger">
               {{ session('error') }}
         </div>
      @endif
     <div class="card">
       <div class="card-body">
         <h5 class="card-title">Profil</h5>
         <button type="button" class="btn btn-primary" onclick="change_password()" style="margin-bottom: 10px;float: right; margin-right: 10px;">
            <i class="fas fa-key me-2"></i>
            Ubah Password
        </button>
        <button type="button" class="btn btn-primary" onclick="edit_photo()" style="margin-bottom: 10px;float: right; margin-right: 10px;">
         <i class="fas fa-camera me-2"></i>
         Ubah Foto Profil
        </button>
         <table class="table table-striped">
            <tr>
               <th>Nama</th>
               <td>{{ $user->name }}</td>
            </tr>
            <tr>
               <th>Username</th>
               <td>{{ $user->username }}</td>
            </tr>
            <tr>
               <th>Email</th>
               <td>{{ $user->email }}</td>
            </tr>
            <tr>
               <th>Pangkat</th>
               <td>{{ $user->pangkat }}</td>
            </tr>
            <tr>
               <th>Jabatan</th>
               <td>{{ $user->jabatan }}</td>
            </tr>
          </table>
       </div>
     </div>

   </div>
 </div>