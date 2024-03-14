# Задание:
https://github.com/rreeggeenntt4/imghosting/blob/main/Тесты.docx
<br/><br/>
# Решение:
## Установка Laravel
```
composer create-project laravel/laravel imghosting
```
Или инструкция: https://laravel.com/docs/10.x/installation

Запуск:
```
cd imghosting
php artisan serve
```
Переходим
>http://127.0.0.1:8000/
Текущая версия Laravel v10.48.1

Подключим БД в файле `.env`
```php
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=imghosting
DB_USERNAME=root
DB_PASSWORD=
```
---


## 1. Реализации формы для загрузки изображений с учетом всех требований.

1. Создадим маршрут для отображения страницы с формой загрузки изображений. Для этого добавим следующий маршрут в файле `routes/web.php`:

```php
Route::get('/upload', [App\Http\Controllers\ImageController::class, 'showUploadForm'])->name('upload.form');
```

2. Создадим контроллер `ImageController`, который будет обрабатывать запросы связанные с изображениями:

```bash
php artisan make:controller ImageController
```

3. В контроллере `ImageController` добавим метод для отображения формы загрузки изображений:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ImageController extends Controller
{
    public function showUploadForm()
    {
        return view('upload_form');
    }
}
```

4. Создадим файл `style.css` в папке `public/css`. Стили `alert` отсюда https://shihabiiuc.com/alert-component-html-css-javascript/ скоректированы пример `.alert.success`=>`.alert-success`. Создадим шаблон `upload_form.blade.php` в папке `resources/views`, где будет размещена форма загрузки изображений:

```html
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
```

5. Добавим маршрут и метод в контроллере для обработки загрузки изображений:

```php
Route::post('/upload', [App\Http\Controllers\ImageController::class, 'upload'])->name('upload.submit');
```
6. Для создания превью изображений воспользуемся библиотекой Intervention Image, которая предоставляет удобные инструменты для работы с изображениями в Laravel.   
Вот как можно добавить превью изображений к вашему проекту:
Установим библиотеку Intervention Image, выполнив следующую команду в терминале:
```bash
composer require intervention/image
```

7. Добавим в контроллер `app\Http\Controllers\ImageController.php`

```php
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;

    public function upload(Request $request)
    {
        // Проверяем, были ли загружены файлы
        if ($request->hasFile('images')) {
            // Получаем загруженные файлы
            $files = $request->file('images');

            // Проверяем количество загружаемых файлов
            if (count($files) > 5) {
                return redirect()->back()->with('error', 'Вы можете загрузить не более 5 файлов за раз.');
            }

            foreach ($files as $file) {
                // Получаем оригинальное название файла
                $originalName = $file->getClientOriginalName();

                // Транслитерируем название файла на английский язык
                $filename = transliterator_transliterate('Russian-Latin/BGN; Any-Latin; Latin-ASCII; NFD; [:Nonspacing Mark:] Remove; NFC; Any-Latin; Latin-ASCII; Lower()', $originalName);

                // Удаляем текущее расширение файла из имени файла
                $filenameWithoutExtension = pathinfo($filename, PATHINFO_FILENAME);

                // Генерируем уникальное имя для файла
                $randomName = Str::lower(Str::random(10));

                // Получаем расширение файла
                $extension = $file->getClientOriginalExtension();

                // Сохраняем файл на сервере с уникальным именем
                $filename = $filenameWithoutExtension . '_' . $randomName . '.' . $extension;
                $file->storeAs('public/uploads/images', $filename);

                // Создаем превью изображения
                $thumbnailPath = public_path('storage/uploads/images/thumbnails');
                if (!file_exists($thumbnailPath)) {
                    mkdir($thumbnailPath, 0755, true);
                }

                // create new manager instance with desired driver
                $manager = new ImageManager(new Driver());

                $thumbnail = $manager->read($file);
                // resize image instance
                $thumbnail->scale(200);
                $thumbnail->save($thumbnailPath . '/' . $filename);

                // Записываем информацию о файле в базу данных
                Image::create([
                    'filename' => $filename,
                ]);
            }

            return redirect()->route('images.show')->with('success', 'Изображения успешно загружены.');
        }

        return redirect()->back()->with('error', 'Не удалось загрузить изображения.');
    }
```

В этом примере превью изображения создаются с помощью библиотеки Intervention Image и сохраняются в папке public/uploads/images/thumbnails. Каждое превью имеет ширину 200 пикселей, а высота автоматически рассчитывается с сохранением пропорций благодаря `scale`. Примеры работы с библиотекой можно найти `https://image.intervention.io/v3/basics/instantiation`

Теперь после загрузки изображений на сервер будет создано и сохранено превью каждого изображения.

9. Для использования transliterator_transliterate() в настройках `php.ini` раскомментировать
```
extension = intl
```



Теперь у вас есть простая форма для загрузки изображений, и запросы на ее обработку будут направляться в контроллер `ImageController`. В следующем шаге вы можете реализовать обработку загружаемых изображений с учетом всех указанных правил.








## 2. Реализации страницы просмотра информации об изображениях.

1. Создадим маршрут для отображения страницы с информацией об изображениях в файле `routes/web.php`:

```php
Route::get('/images', [App\Http\Controllers\ImageController::class, 'showImages'])->name('images.show');
```

2. В контроллере `ImageController` добавим метод для отображения страницы с информацией об изображениях:

```php
public function showImages()
{
    $images = Image::orderBy('created_at', 'desc')->paginate(10);
    return view('images', compact('images'));
}
```

3. Создадим шаблон `images.blade.php` в папке `resources/views`, где будет размещена страница с информацией об изображениях:

```html
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
    <div class="container">
        <h2>Информация об изображениях</h2>
        <table class="table" id="myTable">
            <thead>
                <tr>
                    <th scope="col">id</th>
                    <th scope="col">Картинка</th>
                    <th scope="col">Название</th>
                    <th scope="col">Дата и время загрузки</th>
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
                </tr>
                @endforeach

            </tbody>
        </table>
    </div>
</body>

</html>
```

4. Создадим модель `Image` и миграцию для хранения информации о загруженных изображениях:

```bash
php artisan make:model Image -m
```

5. В миграции добавим столбцы для хранения имени файла и времени загрузки:

```php
public function up()
{
    Schema::create('images', function (Blueprint $table) {
        $table->id();
        $table->string('filename');
        $table->timestamps();
    });
}
```

6. Выполним миграцию для создания таблицы в базе данных:

```bash
php artisan migrate
```
7. Добавим в модель `app\Models\Image.php`

```php
protected $fillable = [
    'filename',
];
```
8. Картинки не доступны по http запросу к папке storage, создаем симлинк
```bash
php artisan storage:link
```

Видео в помощь для работы с изображениями
https://www.youtube.com/watch?v=NWfFL01rn9Q

9. Для очистки кеша:
```bash
php artisan optimize:clear
```
<blockquote>

10. Добавим в `app\Providers\AppServiceProvider.php`
```php
use Illuminate\Pagination\Paginator;
public function boot(): void
    {
        Paginator::useBootstrapFive();
    }
```
> Если не используем dataTables. Достаточно добавить в view `{{ $images->links() }}` 
</blockquote>

11. Добавим сортировку `https://www.datatables.net/` в `image.blade.php` и в таблице установим `id="myTable"`
```php
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.2/css/dataTables.dataTables.css" />
<script src="https://cdn.datatables.net/2.0.2/js/dataTables.js"></script>

<body>
...
    <script>
        $(document).ready(function() {
            $('#myTable').DataTable();
        });
    </script>
```
12. Убираем сортировку для столбца zip `data-orderable="false"`
13. Добавим возможность скачаивания файла в zip архиве для этого добавим в `route/web.php`
    ```php
    <a href="{{ route('download.zip') }}" class="btn btn-primary">Скачать изображения в ZIP</a>
    ```
    В `image.blade.php` добавим
    ```php
    <a href="{{ route('download.zip') }}" class="btn btn-primary">Скачать в ZIP</a>
    ```
    
    

Теперь есть страница, где отображается информация о загруженных изображениях. 

## 3. Создадим API для вывода информации о загруженных файлах в формате JSON. 
1. Создаем контроллер `ApiController`.
```bash
php artisan make:controller ApiController
```
2. Отредактируем `ApiController`.
```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Image;

class ApiController extends Controller
{
    public function getAllImages()
    {
        $images = Image::all();
        return response()->json($images);
    }

    public function getImageById($id)
    {
        $image = Image::find($id);
        if (!$image) {
            return response()->json(['error' => 'Изображение не найдено'], 404);
        }
        return response()->json($image);
    }
}
```
3. Регистрируем контроллер в маршрутах. Добавляем следующие строки в файл routes/api.php:
```php
use App\Http\Controllers\ApiController;

Route::get('/images', [ApiController::class, 'getAllImages']);
Route::get('/images/{id}', [ApiController::class, 'getImageById']);
```

Теперь, когда перейдем по /api/images, выведется JSON-список всех загруженных изображений, а когда перейдем по /api/images/{id}, выведется информация о конкретном изображении по его идентификатору в формате JSON.

-----
### Тестовое завершено

Демонстрция <a href="https://youtu.be/IYFxhqdfSPw">https://youtu.be/IYFxhqdfSPw</a>














            
            





