<?php

namespace App\Models;

use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;
use stdClass;

class Record extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'id', 'text', 'topic'
    ];

    public static function saveCSV($data)
    {
        foreach($data as $row) {
            if (!empty($row[0]) && !empty($row[1])) {
                self::updateOrCreate(['id' => (int) $row[0]],
                    [
                        'text' => $row[1],
                        'topic' => $row[2]
                    ]);
            }
        }
    }

    public function saveData($data)
    {
        foreach($data as $row) {
            if (isset($row->source) && ($row->source == 'predicted')) continue;

            if (!empty($row->id) && !empty($row->text)) {
                self::updateOrCreate(['id' => (int) $row->id],
                    [
                        'text' => $row->text,
                        'topic' => $row->topic,
                    ]);
            }
        }
        $requestData = self::all();
        return $this->getApi($requestData->toArray());
    }

    private function getApi($data)
    {
        $request = new Client();

        $object = new stdClass();
        $object->command = 'classifier-test';
        $object->input = $data;

        $requestResult = $request->request('POST', 'http://r-dev.conjointly.co/api/v1/', [
            'headers' => ['X-Token' => 'TEST'],
            'timeout' => 200,
            'json' => $object
        ]);

        if ($requestResult->getStatusCode() == 200){
            $answer = json_decode($requestResult->getBody());
            if (isset($answer->result) && $answer->result->status == 'OK') {
                return response()->json($answer->result->result);
            }
        } else {
            return response()->json('Connect error');
        }
    }
}
