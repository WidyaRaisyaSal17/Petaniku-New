@extends('layout.app')

@section('title', 'Data Pesanan Baru')

@section('content')

    <div class="card shadow">
        <div class="card-header">
            <h4 class="card-title">
                Data Pesanan Baru
            </h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-stripped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal Pesanan</th>
                            <th>Nama</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
@push('js')
    <script>
        $(function() {

            function rupiah(angka){
                const format = angka.toString().split('').reverse().join('');
                const convert = format.match(/\d{1,3}/g);
                return 'Rp ' + convert.join('.').split('').reverse().join('');
            }

            function date(date){
                var date = new Date(date);
                var day = date.getDate();
                var month = date.getMonth();
                var year = date.getFullYear()

                return `${day}-${month}-${year}`;
            }

            const token = localStorage.getItem('token')
            $.ajax({
                url: '/api/pesanan/baru',
                headers: {
                    "Authorization": 'Bearer' + token
                },
                success: function({
                    data
                }) {
                    let row = '';
                    data.map(function(val, index) {
                        row += `
            <tr>
               <td>${index + 1}</td>
               <td>${date(val.created_at)}</td>
               <td>${val.id_user}</td> <!-- Perubahan di sini -->
               <td>${val.status}</td>
               <td>${rupiah(val.total_harga)}</td>
               <td>
                    <a href="#" data-id="${val.id}" class="btn btn-success btn-aksi">Konfirmasi</a>
                </td>
            </tr>
            `;
                    });
                    $('tbody').append(row);
                }
        });

        $(document).on('click','.btn-aksi',function(){
            const id = $(this).data('id')
            console.log(id);

            $.ajax({
                url: '/api/pesanan/ubah_status/' +  id,
                type: 'POST', // Menggunakan metode POST
                data : {
                    status : "dikonfirmasi"
                },
                headers: {
                    "Authorization": 'Bearer ' + token // Pastikan ada spasi setelah 'Bearer'
                },
                success : function(data){
                    location.reload()
                }
            })
        })
        });
    </script>
@endpush
