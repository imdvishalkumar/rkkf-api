@php
$url = url('/') . '/';
@endphp
<!-- jQuery -->
<script src="{{ $url }}plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="{{ $url }}plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
    $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="{{ $url }}plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- ChartJS -->
<script src="{{ $url }}plugins/chart.js/Chart.min.js"></script>
<!-- Sparkline -->
<script src="{{ $url }}plugins/sparklines/sparkline.js"></script>
<!-- JQVMap -->
<script src="{{ $url }}plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="{{ $url }}plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
<!-- jQuery Knob Chart -->
<script src="{{ $url }}plugins/jquery-knob/jquery.knob.min.js"></script>
<!-- daterangepicker -->
<script src="{{ $url }}plugins/moment/moment.min.js"></script>
<script src="{{ $url }}plugins/daterangepicker/daterangepicker.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="{{ $url }}plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- Summernote -->
<script src="{{ $url }}plugins/summernote/summernote-bs4.min.js"></script>
<!-- overlayScrollbars -->
<script src="{{ $url }}plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- AdminLTE App -->
<script src="{{ $url }}dist/js/adminlte.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="{{ $url }}dist/js/demo.js"></script>
<!-- DataTables -->
<script src="{{ $url }}plugins/datatables/jquery.dataTables.min.js"></script>
<script src="{{ $url }}plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="{{ $url }}plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="{{ $url }}plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="{{ $url }}plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="{{ $url }}plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<!-- Select2 -->
<script src="{{ $url }}plugins/select2/js/select2.full.min.js"></script>
<!-- SweetAlert -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>

