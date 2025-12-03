<script>
   $(document).ready(function() {
      function updateDateTime() {
         var currentDateTime = new Date();
         var hari = currentDateTime.toLocaleDateString('id-ID', {weekday:'long'});
         var tanggal = currentDateTime.getDate();
         var bulan = currentDateTime.toLocaleDateString('id-ID', {month:'long'});
         var tahun = currentDateTime.getFullYear();
         var jam = currentDateTime.getHours();
         var menit = currentDateTime.getMinutes().toString().padStart(2, '0'); // Add leading zero if necessary
         var detik = currentDateTime.getSeconds().toString().padStart(2, '0'); // Add leading zero if necessary
         var dateTimeString = hari + ', ' + tanggal + ' ' + bulan + ' ' + tahun + ' ' + jam + ':' + menit + ':' + detik;
         $('#currentDateTime').html(dateTimeString);
      }

      setInterval(updateDateTime, 1000);
   });
</script>
