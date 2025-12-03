<section class="section">
   <div class="card">
       <div class="card-header">
         Rekapitulasi Tugas
       </div>
       <div class="card-body">
         <div style="text-align: right; margin-bottom: 10px;">
           <button type="button" class="btn btn-success" onclick="reload_table()">
               <i class="fas fa-sync me-2"></i>
               Refresh     
           </button>
         </div>
         <div class="col-12 mb-5">
            <form id="filterData" onsubmit="filterData(); return false;">
               <div class="row col-12">
                  <div class="col-4">
                     <div class="input-group">
                        <input type="date" id="start_date" name="start_date" class="form-control">
                        <span class="input-group-text">s.d</span>
                        <input type="date" id="end_date" name="end_date" class="form-control">
                     </div>
                  </div>
                  <div class="col-2">
                     <select class="form-select" id="selectUser" name="user_id"></select>
                  </div>
                  <div class="col-4">
                     <button type="submit" class="btn btn-info me-lg-3 me-1">Filter</button>
                  </div>
               </div>
            </form>

           <table class="mt-4">
            <tr>
               <td>
                  Jumlah tugas
               </td>
               <td>: <span id="jumlah_tugas"></span></td>
            </tr>
            <tr>
               <td>
                  Nilai Rata-rata
               </td>
               <td>: <span id="nilai_rata_rata"></span></td>
            </tr>
           </table>
         </div>
         <table class="table table-border" id="datatable_ajax" style="width: 100%;">
           <thead>
             <tr>
               <th scope="col">#</th>
               <th scope="col">Pekerjaan</th>
               <th scope="col">Tugas</th>
               <th scope="col">Tanggal Mulai</th>
               <th scope="col">Tanggal Selesai</th>
               <th scope="col">Nama Petugas</th>
               <th scope="col">Nilai</th>
             </tr>
           </thead>
           <tbody>
           </tbody>
         </table>
       </div>
   </div>
 </section>
