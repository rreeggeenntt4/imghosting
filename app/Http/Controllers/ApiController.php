<?php
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