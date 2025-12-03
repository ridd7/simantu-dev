<section class="section dashboard">
  <div class="col-12 col-lg-12">
    <div class="row">
      <!-- All Pekerjaan Card -->
      <div class="col-4 col-lg-4 col-md-4">
          <div class="card">
              <div class="card-body px-4 py-4-5">
                  <div class="row">
                      <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                          <div class="stats-icon blue mb-2">
                              <i class="iconly-boldWork"></i>
                          </div>
                      </div>
                      <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                          <h6 class="text-muted font-semibold">Pekerjaan | All</h6>
                          <h6 class="font-extrabold mb-0">{{ $countPekerjaan['countPekerjaanAll'] }}</h6>
                      </div>
                  </div>
              </div>
          </div>
      </div>
      {{-- End All Pekerjaan Card  --}}

      <!-- Finish Pekerjaan Card -->
      <div class="col-4 col-lg-4 col-md-4">
        <div class="card">
            <div class="card-body px-4 py-4-5">
                <div class="row">
                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                        <div class="stats-icon green mb-2">
                            <i class="iconly-boldWork"></i>
                        </div>
                    </div>
                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                        <h6 class="text-muted font-semibold">Pekerjaan | Finish</h6>
                        <h6 class="font-extrabold mb-0">{{ $countPekerjaan['countPekerjaanFinish'] }}</h6>
                    </div>
                </div>
            </div>
        </div>
      </div>
      <!-- End Finish Pekerjaan Card -->

      <!-- On Progress Pekerjaan Card -->
      <div class="col-4 col-lg-4 col-md-4">
        <div class="card">
            <div class="card-body px-4 py-4-5">
                <div class="row">
                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                        <div class="stats-icon red mb-2">
                            <i class="iconly-boldWork"></i>
                        </div>
                    </div>
                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                        <h6 class="text-muted font-semibold">Pekerjaan | On Progress</h6>
                        <h6 class="font-extrabold mb-0">{{ $countPekerjaan['countPekerjaanOnProgress'] }}</h6>
                    </div>
                </div>
            </div>
        </div>
      </div>
      <!-- End On Progress Pekerjaan Card -->

      <!-- All Tugas Card -->
      <div class="col-4 col-lg-4 col-md-4">
        <div class="card">
            <div class="card-body px-4 py-4-5">
                <div class="row">
                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                        <div class="stats-icon blue mb-2">
                            <i class="iconly-boldShow"></i>
                        </div>
                    </div>
                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                        <h6 class="text-muted font-boldDocument">Tugas | All</h6>
                        <h6 class="font-extrabold mb-0">{{ $countTugas['countTugasAll'] }}</h6>
                    </div>
                </div>
            </div>
        </div>
      </div>
      {{-- End All Tugas Card --}}

      {{-- Approved Tugas Card --}}
      <div class="col-4 col-lg-4 col-md-4">
        <div class="card">
            <div class="card-body px-4 py-4-5">
                <div class="row">
                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                        <div class="stats-icon green mb-2">
                            <i class="iconly-boldDocument"></i>
                        </div>
                    </div>
                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                        <h6 class="text-muted font-semibold">Tugas | Approved</h6>
                        <h6 class="font-extrabold mb-0">{{ $countTugas['countTugasApproved'] }}</h6>
                    </div>
                </div>
            </div>
        </div>
      </div>
      {{-- End Approved Tugas Card --}}

      {{-- On Going Tugas Card --}}
      <div class="col-4 col-lg-4 col-md-4">
        <div class="card">
            <div class="card-body px-4 py-4-5">
                <div class="row">
                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                        <div class="stats-icon red mb-2">
                            <i class="iconly-boldDocument"></i>
                        </div>
                    </div>
                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                        <h6 class="text-muted font-semibold">Tugas | On Going</h6>
                        <h6 class="font-extrabold mb-0">{{ $countTugas['countTugasOnGoing'] }}</h6>
                    </div>
                </div>
            </div>
        </div>
      </div>
      {{-- End On Going Tugas Card --}}
    </div>
  </div>
</section>