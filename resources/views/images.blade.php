<!-- resources/views/images.blade.php -->
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Информация об изображениях</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- jquery -->
    <script src="https://code.jquery.com/jquery-3.7.1.slim.min.js"></script>
    <!-- bootstrap 5 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- dataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.2/css/dataTables.dataTables.css" />
    <script src="https://cdn.datatables.net/2.0.2/js/dataTables.js"></script>
    <!-- styles -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>

<body>
    <!-- Сообщения -->
    @if(session()->has('success'))
    <div class="alert alert-success">
        {{ session()->get('success') }}
    </div>
    @endif
    <!-- /Сообщения -->

    <script>
        $(document).ready(function() {
            $('#myTable').DataTable();
        });
    </script>
    <div class="container" style="margin-top: 20px; margin-bottom: 40px;">
        <h2>Информация об изображениях.</h2> <a href="{{route('upload.form')}}" style="float:right;">Загрузить изображения</a>
        <table class="table" id="myTable">
            <thead>
                <tr>
                    <th scope="col">id</th>
                    <th scope="col">Картинка</th>
                    <th scope="col">Название</th>
                    <th scope="col">Дата и время загрузки</th>
                    <th scope="col" data-orderable="false">zip</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($images as $image)
                <tr>
                    <th scope="col">
                        {{$image->id}}
                    </th>
                    <td>
                        <a href="{{ asset('storage/uploads/images/' . $image->filename) }}" target="_blank">
                            <img src="{{ asset('storage/uploads/images/thumbnails/' . $image->filename) }}" alt="{{ $image->filename }}">
                        </a>
                    </td>
                    <td>
                        {{ $image->filename }}
                    </td>
                    <td>
                        {{ $image->created_at }}
                    </td>
                    <td>
                        <a href="{{ route('download.file', ['filename' => $image->filename]) }}" class="btn btn-primary">Скачать в ZIP</a>
                    </td>
                </tr>
                @endforeach

            </tbody>
        </table>
    </div>
</body>

</html>