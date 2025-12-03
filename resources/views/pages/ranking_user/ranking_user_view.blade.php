<section class="section">
  <div class="card">
      <div class="card-header">
        Ranking Koordinator
      </div>
      <div class="card-body">
        <div style="text-align: right; margin-bottom: 10px;">
          <button type="button" class="btn btn-success" onclick="reload_table_ranking_koordinator()">
              <i class="fas fa-sync me-2"></i>
              Refresh
          </button>
        </div>
        <div class="col-12 mb-5">
          <div class="col-6">
            <form id="filterFormKoordinator" onsubmit="filterData('koordinator'); return false;">
                  <div class="input-group">
                    <input type="date" id="start_date_koordinator" name="start_date" class="form-control" />
                    <span class="input-group-text">s.d</span>
                    <input type="date" id="end_date_koordinator" name="end_date" class="form-control" />
                    <button type="submit" class="btn btn-info me-lg-3 me-1">Filter</button>
                  </div>  
            </form>
          </div>
        </div>
        <table class="table table-border" id="datatable_ajax_ranking_koordinator" style="width: 100%;">
          <thead>
            <tr>
              <th scope="col">#</th>
              <th scope="col">Nama</th>
              <th scope="col">Jumlah Proyek Selesai</th>
              <th scope="col">In time</th>
              <th scope="col">On time</th>
              <th scope="col">Off time</th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>
  </div>
</section>

<section class="section">
  <div class="card">
      <div class="card-header">
        Ranking Petugas
      </div>
      <div class="card-body">
        <div style="text-align: right; margin-bottom: 10px;">
          <button type="button" class="btn btn-success" onclick="reload_table_ranking_petugas()">
              <i class="fas fa-sync me-2"></i>
              Refresh
          </button>
        </div>
        <div class="col-12 mb-5">
          <div class="col-6">
            <form id="filterFormPetugas" onsubmit="filterData('petugas'); return false;">
                  <div class="input-group">
                    <input type="date" id="start_date_petugas" name="start_date_petugas" class="form-control" />
                    <span class="input-group-text">s.d</span>
                    <input type="date" id="end_date_petugas" name="end_date_petugas" class="form-control" />
                    <button type="submit" class="btn btn-info me-lg-3 me-1">Filter</button>
                  </div>  
            </form>
          </div>
        </div>
        <table class="table table-border" id="datatable_ajax">
          <table class="table table-border" id="datatable_ajax_ranking_petugas" style="width: 100%;">
            <thead>
              <tr>
                <th scope="col">#</th>
                <th scope="col">Nama</th>
                <th scope="col">Jumlah Tugas Selesai</th>
                <th scope="col">Nilai Rata-Rata</th>
                <th scope="col">In time</th>
                <th scope="col">On time</th>
                <th scope="col">Off time</th>
              </tr>
            </thead>
            <tbody>
             
            </tbody>
          </table>
          <tbody>
          </tbody>
      </table>
    </div>
  </div>
</section>