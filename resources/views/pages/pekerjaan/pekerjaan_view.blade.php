  <section class="section">
      <div class="card">
          <div class="card-header">
              Data Pekerjaan
          </div>
          <div class="card-body">
            <div style="text-align: right; margin-bottom: 10px;">
              @can('admin_or_manager')
              <button type="button" class="btn btn-primary" onclick="add_pekerjaan()" style="margin-right: 10px;">
                  <i class="fas fa-plus me-2"></i>
                  Tambah Data
              </button>
              @endcan
              <button type="button" class="btn btn-success" onclick="reload_table()">
                  <i class="fas fa-sync me-2"></i>
                  Refresh
              </button>
            </div>
            <table class="table table-border" id="datatable_ajax" style="width: 100%;">
              <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">No SK</th>
                    <th scope="col">Pekerjaan</th>
                    <th scope="col">Kategori</th>
                    <th scope="col">Tanggal Mulai</th>
                    <th scope="col">Tanggal Selesai</th>
                    <th scope="col">Koordinator</th>
                    <th scope="col">File</th>
                    <th scope="col">Status</th>
                    <th scope="col">Tugas</th>
                    <th scope="col">Aksi</th>
                  </tr>
              </thead>
              <tbody>
              </tbody>
          </table>
        </div>
      </div>

  </section>