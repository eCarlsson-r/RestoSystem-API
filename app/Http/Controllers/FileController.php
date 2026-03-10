<?php

namespace App\Http\Controllers;

use App\Models\File as FileModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class FileController
{
    public function show(Request $request)
    {
        $request->validate([
            'fileid' => 'required',
        ]);

        $file = FileModel::find($request->fileid);

        if (!$file) {
            return response()->json([
                'err' => 1,
                'msg' => 'The file is not registered on the server.'
            ]);
        }

        $fname = $file->name . "." . $file->ext;
        $path = public_path('files/' . $fname);

        if (File::exists($path)) {
            return response()->json([
                'err' => 0,
                'msg' => 'The file exists on the server.',
                'file-name' => $fname
            ]);
        } else {
            return response()->json([
                'err' => 1,
                'msg' => 'The file is registered, but it is either removed or renamed.'
            ]);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'file-name' => 'required|string',
            'file-ext' => 'required|string',
            'file-type' => 'required|string',
            'data-uri' => 'required|string',
        ]);

        $fileName = $request->input('file-name');
        $fileExt = $request->input('file-ext');
        $imageFileType = $request->input('file-type');
        $dataUri = $request->input('data-uri');

        $directory = public_path('files');
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $targetFile = $fileName . "." . $fileExt;
        $path = $directory . '/' . $targetFile;

        $data = explode(',', $dataUri);
        $base64Data = isset($data[1]) ? $data[1] : $data[0];
        
        $fileUpload = File::put($path, base64_decode($base64Data));

        if ($fileUpload !== false) {
            $fileSize = File::size($path) / 1000; // KB
            
            $fileRecord = FileModel::create([
                'name' => $fileName,
                'type' => $imageFileType,
                'ext' => $fileExt,
                'size' => $fileSize,
                'upload_date' => now()->toDateString(),
            ]);

            return response()->json([
                'err' => 0,
                'msg' => 'File successfully uploaded',
                'file-id' => $fileRecord->id
            ]);
        } else {
            return response()->json([
                'err' => 1,
                'msg' => 'Failed to upload file.'
            ]);
        }
    }
}
