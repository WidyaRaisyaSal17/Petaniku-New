@extends('layout.app')

@section('title', 'Data Slider')

@section('content')
    <div class="card shadow">
        <div class="card-header">
            <h4 class="card-title">
                Data Slider
            </h4>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-end mb-4">
                <a href="#modal-form" class="btn btn-primary modal-tambah">Tambah Data</a>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-stripped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Slider</th>
                            <th>Deskripsi</th>
                            <th>Gambar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-form" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Form slider</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <form class="form-slider" id="formDropzone" class="dropzone" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="">Nama slider</label>
                                    <input type="text" class="form-control" name="nama_slider"
                                        placeholder="Nama slider" required>
                                </div>
                                <div class="form-group">
                                    <label for="">Deskripsi</label>
                                    <textarea name="deskripsi" placeholder="Deskripsi" class="form-control" id="" cols="30" rows="2"
                                        required></textarea>
                                </div>
                                <div class="form-group mb-4">
                                    <label for="">Gambar</label>
                                    <div class="dropzone-drag-area">
                                        <div id="previews" class="dropzone-previews"></div>
                                        <div class="dz-message text-muted opacity-50" data-dz-message>
                                            <span>Drag file here to upload</span>
                                        </div>
                                    </div>
                                </div>
                             
                                    <button type="submit" class="btn btn-primary btn-block form-control" id="search-btn">Submit</button>
                              
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
<script>
    // Initialize Dropzone
    Dropzone.autoDiscover = false;
    var myDropzone = new Dropzone("#formDropzone", {
        url: '/api/sliders',
        paramName: "gambar", // The name that will be used to transfer the file
        maxFilesize: 5, // MB
        maxFiles: 1, // Maximum number of files
        acceptedFiles: 'image/*', // Specify accepted file types
        addRemoveLinks: true, // Add remove links for uploaded files
        dictDefaultMessage: "Drag file here to upload", // Default message
        previewsContainer: "#previews", // Specify where previews should be displayed
        previewTemplate: '<div class="dz-preview dz-file-preview"><img data-dz-thumbnail /></div>',
        init: function() {
            this.on("success", function(file) {
                // Hide the message after successful upload
                $(".dropzone-drag-area .dz-message").hide();
            });
        }
    });

    $(function() {
        $.ajax({
            url: '/api/sliders',
            success: function({data}) {
                let row = '';
                data.map(function(val, index) {
                    row += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${val.nama_slider}</td>
                            <td>${val.deskripsi}</td>
                            <td><img src="/uploads/sliders/${val.gambar}" width="150"></td>
                            <td>
                                <a data-toggle="modal" href="#modal-form" data-id="${val.id}" class="btn btn-warning modal-ubah">Edit</a>
                                <a href="#" data-id="${val.id}" class="btn btn-danger btn-hapus">Hapus</a>
                            </td>
                        </tr>`;
                });
                $('tbody').append(row);
            }
        });

        $(document).on('click', '.btn-hapus', function() {
            const id = $(this).data('id');
            const token = localStorage.getItem('token');

            if (confirm('Apakah anda yakin?')) {
                $.ajax({
                    url: '/api/sliders/' + id,
                    type: "DELETE",
                    headers: {
                        "Authorization": 'Bearer ' + token
                    },
                    success: function(data) {
                        if (data.message == 'success') {
                            alert('Data berhasil dihapus');
                            location.reload();
                        }
                    }
                });
            }
        });

        $('.modal-tambah').click(function() {
            $('#modal-form').modal('show');
            $('input[name="nama_slider"]').val('');
            $('textarea[name="deskripsi"]').val('');
            myDropzone.removeAllFiles(true);

            $('.form-slider').off('submit').on('submit', function(e) {
                e.preventDefault();
                const token = localStorage.getItem('token');
                const formData = new FormData(this);

                const uploadedFiles = myDropzone.getAcceptedFiles();
                if (uploadedFiles.length > 0) {
                    formData.append('gambar', uploadedFiles[0]);
                }

                $.ajax({
                    url: '/api/sliders',
                    type: 'POST',
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    headers: {
                        "Authorization": 'Bearer ' + token
                    },
                    success: function(data) {
                        if (data.success) {
                            alert('Data berhasil ditambah');
                            location.reload();
                        }
                    }
                });
            });
        });

        $(document).on('click', '.modal-ubah', function() {
            $('#modal-form').modal('show');
            const id = $(this).data('id');

            $.get(`/api/sliders/${id}`, function(data) {
                $('input[name="nama_slider"]').val(data.nama_slider);
                $('textarea[name="deskripsi"]').val(data.deskripsi);
                myDropzone.removeAllFiles(true);
            });

            $('.form-slider').off('submit').on('submit', function(e) {
                e.preventDefault();
                const token = localStorage.getItem('token');
                const formData = new FormData(this);

                const uploadedFiles = myDropzone.getAcceptedFiles();
                if (uploadedFiles.length > 0) {
                    formData.append('gambar', uploadedFiles[0]);
                }

                $.ajax({
                    url: `/api/sliders/${id}?_method=PUT`,
                    type: 'POST',
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    headers: {
                        "Authorization": 'Bearer ' + token
                    },
                    success: function(data) {
                        if (data.success) {
                            alert('Data berhasil diubah');
                            location.reload();
                        }
                    }
                });
            });
        });
    });
</script>
@endpush
