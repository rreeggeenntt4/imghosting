<!-- resources/views/upload_form.blade.php -->
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Загрузка изображений</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>

<body>
    <h2>Загрузить изображения</h2>

    <!-- Сообщения -->
    @if(session()->has('error'))
    <div class="alert alert-danger">
        {{ session()->get('error') }}
    </div>
    @endif
    <!-- /Сообщения -->

    <form action="{{ route('upload.submit') }}" method="post" enctype="multipart/form-data">
        @csrf
        <input type="file" name="images[]" multiple accept="image/*"><br><br>
        <button type="submit">Загрузить</button>
    </form>
</body>

</html>