<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Image;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;

use ZipArchive;
use Illuminate\Support\Facades\Storage;


class ImageController extends Controller
{
    public function showUploadForm()
    {
        return view('upload_form');
    }

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




    public function showImages()
    {
        /* $images = Image::orderBy('created_at', 'desc')->paginate(10); */
        $images = Image::orderBy('created_at', 'desc')->get();
        return view('images', compact('images'));
    }



    public function downloadZip($filename)
    {
        // Создаем временный каталог для хранения файлов
        $tempPath = storage_path('app/temp');
        if (!is_dir($tempPath)) {
            mkdir($tempPath, 0755, true);
        }

        // Путь к файлу для архивирования
        $sourceFilePath = storage_path('app/public/uploads/images/') . $filename;

        // Проверяем существует ли файл
        if (!Storage::exists('public/uploads/images/' . $filename)) {
            return redirect()->back()->with('error', 'Файл не найден');
        }

        // Создаем имя и путь для временного zip архива
        $zipFileName = $filename . '.zip';
        $zipFilePath = $tempPath . '/' . $zipFileName;

        // Создаем новый экземпляр ZipArchive
        $zip = new ZipArchive;
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            // Добавляем файл в архив
            $zip->addFile($sourceFilePath, $filename);
            $zip->close();
        } else {
            return redirect()->back()->with('error', 'Не удалось создать zip архив');
        }

        // Отправляем zip архив пользователю
        return response()->download($zipFilePath, $zipFileName)->deleteFileAfterSend(true);
    }
}
