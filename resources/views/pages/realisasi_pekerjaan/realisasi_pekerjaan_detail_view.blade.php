{{-- Start Detail Pekerjaan  --}}
<section class="section">
  <div class="card">
      <div class="card-header">
          Detail Pekerjaan
      </div>
      <div class="card-body">
        @foreach ($pekerjaan as $item)
         <table>
            <tr>
              <td>No. SK</td>
              <td> : {{ $item->no_sk }}</td>
            </tr>
            <tr>
              <td>Pekerjaan</td>
              <td> : {{ $item->pekerjaan }}</td>
            </tr>
            <tr>
              <td>Deskripsi Pekerjaan</td>
              <td> : {{ $item->deskripsi_pekerjaan }}</td>
            </tr>
            <tr>
              <td>Koordinator</td>
              <td> : {{ $item->koordinator_name }}</td>
            </tr>
            <tr>
              <td>Tanggal Mulai</td>
              <td> : {{ date('d-m-Y', strtotime($item->start_date)) }}</td>
            </tr>
            <tr>
              <td>Tanggal Selesai</td>
              <td> : {{ date('d-m-Y', strtotime($item->end_date)) }}</td>
            </tr>
            <tr>
              <td>File Dasar Pelaksanaan</td>
              <td> : 
                @php
                    $file = $item->file_dasar_pelaksanaan;
                    $extension = pathinfo($file, PATHINFO_EXTENSION);
                @endphp
                @if ($file)    
                  @if ($extension == 'jpg' || $extension == 'png')
                    <a href="{{ url('storage/' . $file) }}" download><i class="fas fa-file-image" style="font-size: 35px;"></i></a>
                  @elseif($extension == 'pdf')
                    <a href="{{ url('storage/' . $file) }}" download><i class="fas fa-file-pdf" style="font-size: 35px;"></i></a>
                  @endif
                @else
                  -
                @endif
              </td>
            </tr>
        </table>
        @endforeach
        <div style="text-align: right; margin-bottom: 10px;">
          <button type="button" class="btn btn-success" onclick="reload_table_detail_pekerjaan()">
              <i class="fas fa-sync me-2"></i>
              Refresh
          </button>
        </div>
        <table class="table table-border" id="datatable_ajax_detail_pekerjaan" style="width: 100%;">
          <thead>
            <tr>
              <th scope="col">#</th>
              <th scope="col">Tugas</th>
              <th scope="col">Petugas</th>
              <th scope="col">Tanggal Mulai (Planning)</th>
              <th scope="col">Tanggal Selesai (Planning)</th>
              <th scope="col">Tanggal Mulai (Actual)</th>
              <th scope="col">Tanggal Selesai (Actual)</th>
              <th scope="col">Status</th>
              <th scope="col">File</th>
              <th scope="col">Nilai</th>
              <th scope="col">Aksi</th>
            </tr>
          </thead>
          <tbody>
           
          </tbody>
        </table>
    </div>
  </div>
</section>
{{-- End Detail Pekerjaan  --}}

{{-- Start Gant Chart  --}}
<section class="section">
  <div class="card">
      <div class="card-header">
        Timeline Waktu Pekerjaan
      </div>
      <div class="card-body">
        <div>
          <h5>Timeline Perencanaan</h5>
          <svg id="gantt"></svg>
        </div>
        <div class="mt-5">
          <h5>Timeline Realisasi</h5>
          <svg id="gantt2"></svg>   
        </div>
    </div>
  </div>
</section> 
{{-- End Gant Chart  --}}

{{-- Start Proses Tugas  --}}
<section class="section">
  <div class="card">
      <div class="card-header">
          Proses Tugas
      </div>
      <div class="card-body">
        <div style="text-align: right; margin-bottom: 10px;">
          <button type="button" class="btn btn-success" onclick="reload_table_proses_tugas()">
              <i class="fas fa-sync me-2"></i>
              Refresh
          </button>
        </div>
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
{{-- End Proses Tugas  --}}


