<section class="section">
   <div class="card">
       <div class="card-header">
           Data Satuan
       </div>
       <div class="card-body">
         <div style="text-align: right; margin-bottom: 10px;">
           <button type="button" class="btn btn-primary" onclick="add_satuan()" style="margin-right: 10px;">
               <i class="fas fa-plus me-2"></i>
               Tambah Data
           </button>
           <button type="button" class="btn btn-success" onclick="reload_table()">
               <i class="fas fa-sync me-2"></i>
               Refresh
           </button>
         </div>
         <table class="table table-border" id="datatable_ajax" style="width: 100%;">
           <thead>
             <tr>
               <th scope="col">#</th>
               <th scope="col">Satuan</th>
               <th scope="col">Aksi</th>
             </tr>
           </thead>
           <tbody>
           </tbody>
       </table>
     </div>
   </div>
 </section>