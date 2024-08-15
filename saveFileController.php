<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SaveFileController extends Controller
{
    public function saveFile(Request $request)
    {
        $request->validate([
            'file' => ['required'],
        ]);
        // Let's assume we sent bellow file  to the backend
        // dummy.jpg 15 MB

        //convert bytes to readable file size
        $size = $this->getReadableFileSize($request->file('file')->getSize()); // returns "15 MB"

        //get the file type
        $type = $request->file('file')->extension(); // returns "jpg"

        $savePath = "path/to/file";

        // generate uniqe name for the file, so if dummy.jpg exist in our database, this returns dummy(1).jpg and so on...
        $fileName = $this->generateUniqeFileName($request->file('file'), $savePath);

        //store the file in current machine and returns store url
        $filePath = $this->UploadFile($request->file('file'), 'public', $fileName, $savePath);

        // now we can store file path with it's size and original name (if you need) in database

    }

    /**
     * get the readable file size based on bytes of file
     */
    protected function getReadableFileSize($bytes)
    {
        switch ($bytes) {
            case ($bytes >= 1073741824):
                $bytes = number_format($bytes / 1073741824, 2) . ' GB';

                break;
            case ($bytes >= 1048576):
                $bytes = number_format($bytes / 1048576, 2) . ' MB';

                break;
            case ($bytes >= 1024):
                $bytes = number_format($bytes / 1024, 2) . ' KB';

                break;
            case ($bytes > 1):
                $bytes = $bytes . ' bytes';

                break;
            case ($bytes == 1):
                $bytes = $bytes . ' byte';

                break;

            default:
                $bytes = '0 bytes';

                break;
        }
        return $bytes;
    }

    /**
     * generates a uniqe file name (rename the file and add number to it if the file exist on machine)
     */
    protected function  generateUniqeFileName(UploadedFile $file , $path)
    {

        // get file name without extention (so we can add number to file if we need)
        $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        // get file format
        $type = $file->extension();

        // generate dynamic returend file name with format
        $finalFileName = $fileName . '.' . $type;

        // generate store path with file name to check if file exist
        $storePath = $path . $finalFileName;

        $index = 1;
        while (Storage::disk('public')->exists($storePath)) {

            $finalFileName = $fileName . '(' . $index . ')' . '.' . $type;
            $storePath = $path . $finalFileName;

            $index++;
        }

        // it would be dummy.jpg if file dose not exist or dummy(1).jpg if file exist and so on
        // dd($finalFileName);
        return $finalFileName;
    }

    //save file to the storage 
    public function UploadFile(UploadedFile $file, $disk, $filename, $folder = null)
    {
        return $file->storeAs(
            $folder,
            $filename,
            $disk
        );
    }
}

