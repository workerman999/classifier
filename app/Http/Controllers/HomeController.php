<?php

namespace App\Http\Controllers;

use App\Models\Record;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class HomeController extends Controller
{
    public function index()
    {
        return view('index');
    }

    public function getRecords()
    {
        return Record::all();
    }

    public function importCsv(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|file'
        ]);

        if ($request->file->getClientOriginalExtension() != 'csv') return false;

        $path = $request->file('file')->getRealPath();
        $file = file($path);
        $data = array_map('str_getcsv', $file);
        $data = array_slice($data, 1);

        Record::saveCSV($data);

        return Record::all();
    }

    public function calculateRecords(Request $request)
    {
        if (!$request->has('data')) return false;

        $data = json_decode($request->data);
        $model = new Record();
        return $model->saveData($data);
    }

    public function saveRecord(Request $request)
    {
        if (!$request->has('data')) return false;
        $record = json_decode($request->data);

        if ($model = Record::find((int) $record->id)) {
            $model->update([
                'topic' => $record->topic
            ]);
        }
    }

    public function exportCsv()
    {
        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=file.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        $records = Record::all();
        $columns = ['id', 'text', 'topic'];

        $callback = function() use ($records, $columns)
        {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach($records as $record) {
                fputcsv($file, [$record->id, $record->text, $record->topic]);
            }
            fclose($file);
        };
        return Response::stream($callback, 200, $headers);
    }
}
