<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/2/002
 * Time: 13:59
 * 抄送
 */

namespace App\Repository\Web;


use App\Models\StepCc;
use Illuminate\Support\Facades\Auth;

class CcRepository
{
    /**
     *获取抄送列表
     */
    public function index()
    {
        $data = StepCc::with('flowRun.stepRun')->where('staff_sn', Auth::id())
            ->filterByQueryString()
            ->sortByQueryString()
            ->withPagination();
        return $data;
    }

    /**
     * 抄送详情
     * @param $id
     * @return array
     */
    public function detail($id)
    {
        $data = StepCc::with(['form', 'step', 'flowRun'])->where('staff_sn', Auth::id())->findOrFail($id);
        //表单data
        $formRepository = new FormRepository();
        //表单data
        $formData = $formRepository->getFormData($data->flow_run_id);//获取表单data数据
        //表单字段
        $fields = $formRepository->getFields($data->form_id);
        $data->form_data = $formData;
        $data->fields = $fields;
        $data->field_groups = $data->form->fieldGroups->toArray();
        return $data;
    }
}