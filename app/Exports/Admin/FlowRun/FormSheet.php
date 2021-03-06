<?php

namespace App\Exports\Admin\FlowRun;

use App\Models\Form;
use App\Models\Region;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\BeforeWriting;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithMapping;

class FormSheet implements FromCollection, WithTitle, WithColumnFormatting, WithEvents
{
//    use Exportable;
    private $formId;
    private $runIds;
    protected $cellCount = 0;
    //返回code
    protected $code;
    protected $path;

    public function __construct(int $formId, array $runIds, string $code, $path)
    {
        $this->formId = $formId;
        $this->runIds = $runIds;
        $this->code = $code;
        $this->path = $path;
    }


    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
//        $data = DB::table('test')->orderBy('id')->limit(20000)->get();
        $data = $this->getData();
        return $data;
    }

    public function query()
    {
        // TODO: Implement query() method.
    }

    public function title(): string
    {
        $form = Form::withTrashed()->findOrFail($this->formId);
        $sheetName = $form->created_at->format('Y-m-d H时i分s秒');
        return $sheetName;
    }


    public function columnFormats(): array
    {
        $cells = [];
        $key = 'A';
        for ($i = 1; $i <= $this->cellCount; $i++) {
            $cells[$key] = NumberFormat::FORMAT_TEXT;
            $key++;
        }
        return $cells;
    }

    public function registerEvents(): array
    {

        return [
            BeforeExport::class => function (BeforeExport $event) {
//                dump('BeforeExport');
            },
            BeforeWriting::class => function (BeforeWriting $event) {
//                dump('BeforeWriting');
            },
            BeforeSheet::class => function (BeforeSheet $event) {
                $data = [
                    'progress' => 2,
                    'type' => 'sheet',
                    'message' => '生成excel中...',
                    'path' => $this->path,
                    'url' => config('app.url') . '/storage/' . $this->path
                ];
                $cache = Cache::get($this->code);
                if (Cache::has($this->code)) {
                    $newProgress = $cache['progress'] + 2;
                    if ($newProgress >= 30)
                        $newProgress = 30;
                    $data['progress'] = $newProgress;
                }
                Cache::put($this->code, $data, 120);
            },
            AfterSheet::class => function (AfterSheet $event) {
                $data = Cache::get($this->code);
                $data['progress'] = $data['progress'] + 2;
                if ($data['progress'] >= 30)
                    $data['progress'] = 30;
                Cache::put($this->code, $data, 120);
            },
        ];
    }

    protected function getData()
    {
        $formData = DB::table('form_data_' . $this->formId)->whereIn('run_id', $this->runIds)->get();
        $header = $this->getExcelHeader($this->formId);
        //单元格数量
        $this->cellCount = count($header);
        $newFormData = [];
        foreach ($formData as $k => $v) {
            foreach ($v as $field => $value) {
                if (!in_array($field, ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                    if ($value) {
                        $newValue = json_decode($value, true);
                        if (is_array($newValue) && $newValue && !is_null($value)) {
                            if (count($newValue) == count($newValue, 1)) {
                                //一维数组
                                if (array_has($newValue, 'text')) {
                                    $value = $newValue['text'];
                                } elseif (array_has($newValue, ['province_id', 'city_id', 'county_id', 'address'])) {
                                    $regionFullName = $newValue['county_id'] ? $this->getRegionName($newValue['county_id']) : '';
                                    $value = $regionFullName . $newValue['address'];
                                } elseif (array_has($newValue, ['province_id', 'city_id', 'county_id'])) {
                                    $value = $newValue['county_id'] ? $this->getRegionName($newValue['county_id']) : '';
                                } elseif (array_has($newValue, ['province_id', 'city_id'])) {
                                    $value = $newValue['city_id'] ? $this->getRegionName($newValue['city_id']) : '';
                                } elseif (array_has($newValue, ['province_id'])) {
                                    $value = $newValue['province_id'] ? $this->getRegionName($newValue['province_id']) : '';
                                } else {
                                    $value = implode(',', $newValue);
                                }
                            } else {
                                //二维数组
                                $value = implode(',', array_pluck($newValue, 'text'));
                            }
                        } elseif (is_array($newValue) && count($newValue) == 0) {
                            $value = '';
                        }
                    } else {
                        $value = '';
                    }
                    $newFormData[$k][$field] = $value;
                }

            }
        }
        $data = array_collapse([[$header], $newFormData]);
        $data = collect($data);
        return $data;
    }

    /**
     * 获取地区长字段名称
     * @param $id
     * @return mixed
     */
    protected function getRegionName($id)
    {
        return Region::find($id)->full_name;
    }

    /**
     * 获取excel头
     * @param $formId
     * @return array
     */
    public function getExcelHeader($formId)
    {
        $columns = DB::select('show full columns from form_data_' . $formId);
        $columns = array_filter($columns, function ($column) {
            return !in_array($column->Field, ['id', 'created_at', 'updated_at', 'deleted_at']);
        });
        $header = array_pluck($columns, 'Comment');
        return $header;
    }
}
