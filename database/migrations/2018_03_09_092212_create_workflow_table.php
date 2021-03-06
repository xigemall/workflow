<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkflowTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /* ---- 表单和字段 START ---- */

        /* 表单分类 */
        Schema::create('form_types', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->char('name', 20)->comment('表单分类名称');
            $table->unsignedTinyInteger('sort')->comment('排序')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['name', 'deleted_at']);
        });

        /* 表单 */
        Schema::create('forms', function (Blueprint $table) {
            $table->increments('id');
            $table->char('name', 20)->comment('表单名称');
            $table->char('description', 200)->comment('表单描述')->default('');
            $table->unsignedSmallInteger('form_type_id')->comment('分类ID');
            $table->unsignedInteger('number')->nullable()->index()->comment('表单编号');
            $table->unsignedTinyInteger('sort')->comment('排序')->default(0);
            $table->unsignedTinyInteger('pc_template')->comment('启用PC模板 1是 0否')->default(0);
            $table->unsignedTinyInteger('mobile_template')->comment('启用移动端模板 1是 0否')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('form_type_id')->references('id')->on('form_types');
            $table->unique(['name', 'deleted_at']);
        });

        /*表单列表控件*/
        Schema::create('form_grids', function (Blueprint $table) {
            $table->increments('id');
            $table->char('name',20)->comment('控件名称');
            $table->char('key',20)->comment('键名');
            $table->unsignedSmallInteger('row')->nullable()->comment('控件行');
            $table->unsignedSmallInteger('col')->nullable()->comment('控件列');
            $table->unsignedSmallInteger('x')->nullable()->comment('控件x轴');
            $table->unsignedSmallInteger('y')->nullable()->comment('控件y轴');
            $table->unsignedSmallInteger('mobile_y')->comment('移动端控件y轴')->nullable();
            $table->unsignedInteger('form_id')->comment('表单id');
            $table->foreign('form_id')->references('id')->on('forms');
            $table->timestamps();
            $table->softDeletes();
        });
        /*表单列表控件*/

        /* 字段 */
        Schema::create('fields', function (Blueprint $table) {
            $table->increments('id');
            $table->char('key', 20)->comment('字段键名');
            $table->char('name', 20)->comment('字段名称');
            $table->char('description', 200)->comment('字段描述')->default('');
            $table->char('type', 20)
                ->comment("字段类型 'int'数字, 'text'文本, 'date'日期, 'datetime'日期时间, 'time'时间, 'array'数组,'select'选择器, 'file'文件,'department'部门控件，'staff'员工控件，'shop'店铺控件,'region'地区 ,'api'接口");
            $table->unsignedTinyInteger('is_checkbox')->default(0)->comment('控件是否多选 0否 1是');
            $table->text('condition')->nullable()->comment('控件条件');
            $table->unsignedTinyInteger('region_level')->nullable()->comment('地区联动级数 1，2，3,4');
            $table->char('min', 20)->comment('最小值 可填数字、日期、today等')->default('');
            $table->char('max', 20)->comment('最大值')->default('');
            $table->unsignedTinyInteger('scale')->comment('小数位数')->default(0);
            $table->string('default_value', 500)->comment('默认值/计算公式')->default('');
            $table->text('options')->nullable()->comment('可选值');
            $table->unsignedInteger('form_id')->comment('表单ID');
            $table->unsignedInteger('form_grid_id')->comment('列表控件ID')->nullable();
            $table->unsignedTinyInteger('sort')->default(0)->comment('字段排序');
            $table->unsignedInteger('field_api_configuration_id')->nullable()->comment('字段接口配置ID');
            $table->unsignedTinyInteger('row')->comment('字段行')->nullable();
            $table->unsignedTinyInteger('col')->comment('字段列')->nullable();
            $table->unsignedSmallInteger('x')->comment('字段x轴')->nullable();
            $table->unsignedSmallInteger('y')->comment('字段y轴')->nullable();
            $table->unsignedSmallInteger('mobile_y')->comment('移动端y轴')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('form_id')->references('id')->on('forms');
            $table->foreign('form_grid_id')->references('id')->on('form_grids');
        });

        /* 验证规则 */
        Schema::create('validators', function (Blueprint $table) {
            $table->increments('id');
            $table->char('name', 20)->comment('规则名称');
            $table->char('description', 200)->comment('规则描述')->default('');
            $table->char('type', 20)->comment('规则类型 regex(正则表达式) in(用,号分割的字符串) mimes(用,号分割的扩展类型的字符串)')->default('');
            $table->string('params')->comment('规则参数')->default('');
            $table->unsignedTinyInteger('is_locked')->comment('是否锁定')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['name', 'deleted_at']);
        });

        /* 字段/验证规则中间表 */
        Schema::create('fields_has_validators', function (Blueprint $table) {
            $table->unsignedInteger('field_id');
            $table->unsignedInteger('validator_id');
            $table->foreign('field_id')->references('id')->on('fields');
            $table->foreign('validator_id')->references('id')->on('validators');
            $table->primary(['field_id', 'validator_id']);
        });

        /* ---- 表单和字段 END ---- */

        /* ---- 流程 START ---- */


        /* 流程分类 */
        Schema::create('flow_types', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->char('name', 20)->comment('流程分类名称');
            $table->unsignedTinyInteger('sort')->comment('排序')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['name', 'deleted_at']);
        });

        /* 流程 */
        Schema::create('flows', function (Blueprint $table) {
            $table->increments('id');
            $table->char('name', 20)->comment('流程名称');
            $table->char('description', 200)->comment('流程描述')->default('');
            $table->string('icon')->comment('流程图标')->nullable()->default('');
            $table->unsignedSmallInteger('flow_type_id')->comment('分类ID');
            $table->unsignedInteger('form_id')->comment('表单ID');
            $table->unsignedTinyInteger('sort')->comment('排序')->default(0);
            $table->unsignedInteger('number')->nullable()->index()->comment('流程编号');
            $table->unsignedTinyInteger('is_active')->comment('是否启用')->default(0);
            $table->string('start_callback_uri')->comment('发起回调地址')->default('');
            $table->enum('accept_start_callback',[0,1])->comment('是否接收发起回调返回值 0否 1是')->default(0);
            $table->string('end_callback_uri')->comment('结束回调地址')->default('');
            $table->enum('accept_end_callback',[0,1])->comment('是否接收结束回调返回值 0否 1是')->default(0);
            $table->enum('send_message',[1,0])->comment('发送消息 1是 0否')->default(1);
            $table->unsignedTinyInteger('is_client')->comment('是否客服端发起 1是 0否')->default(1);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('flow_type_id')->references('id')->on('flow_types');
            $table->foreign('form_id')->references('id')->on('forms');
            $table->unique(['name', 'deleted_at']);
        });

        /* 发起权限（部门） */
        Schema::create('flows_has_departments', function (Blueprint $table) {
            $table->unsignedInteger('flow_id');
            $table->unsignedInteger('department_id');
            $table->foreign('flow_id')->references('id')->on('flows');
            $table->index(['flow_id', 'department_id']);
        });

        /* 发起权限（角色） */
        Schema::create('flows_has_roles', function (Blueprint $table) {
            $table->unsignedInteger('flow_id');
            $table->unsignedInteger('role_id');
            $table->foreign('flow_id')->references('id')->on('flows');
            $table->index(['flow_id', 'role_id']);
        });

        /* 发起权限（员工） */
        Schema::create('flows_has_staff', function (Blueprint $table) {
            $table->unsignedInteger('flow_id');
            $table->unsignedInteger('staff_sn');
            $table->foreign('flow_id')->references('id')->on('flows');
            $table->index(['flow_id', 'staff_sn']);
        });

        /* 步骤 */
        Schema::create('steps', function (Blueprint $table) {
            $table->increments('id');
            $table->char('name', 20)->comment('步骤名称');
            $table->char('description', 200)->comment('步骤描述')->default('');
            $table->unsignedInteger('flow_id')->comment('流程ID');
            $table->unsignedInteger('step_key')->comment('步骤标识');
            $table->string('prev_step_key', 50)->comment('上一步标识')->default('');
            $table->string('next_step_key', 50)->comment('下一步标识')->default('');
            $table->text('available_fields')->nullable()->comment('可用字段');
            $table->text('hidden_fields')->comment('隐藏字段')->nullable();
            $table->text('editable_fields')->comment('可编辑字段')->nullable();
            $table->text('required_fields')->comment('必填字段')->nullable();
            $table->unsignedTinyInteger('approver_type')->comment('审批人类型 0全部审批人，1选择审批人，2选择配置部门的审批人，3当前人管理者')->default(0);
            $table->unsignedInteger('step_approver_id')->comment('审批人配置ID')->nullable();
            $table->text('approvers')
                ->comment('审批人，json对象{staff:[],roles:[],departments:[]},仅一人时为固定审批人,空值自由选择')
                ->nullable();
            $table->string('allow_condition', 800)->comment('访问条件，字段：${field}，其他参数：_params_')->default('');
            $table->string('skip_condition', 800)->comment('略过条件')->default('');
            $table->unsignedTinyInteger('reject_type')->comment('退回类型：0.禁止 1.到上一步 2.到之前任意步骤')->default(0);
            $table->unsignedTinyInteger('concurrent_type')->comment('并发类型：0.禁止 1.允许 2.强制')->default(0);
            $table->unsignedTinyInteger('merge_type')->comment('合并类型：0.非必须 1.必须')->default(0);
            $table->string('start_callback_uri')->comment('开始回调地址')->default('');
            $table->enum('accept_start_callback',[0,1])->comment('是否接收开始回调返回值 0否 1是')->default(0);
            $table->string('check_callback_uri')->comment('查看回调地址')->default('');
            $table->enum('accept_check_callback',[0,1])->comment('是否接收查看回调返回值 0否 1是')->default(0);
            $table->string('approve_callback_uri')->comment('通过回调地址')->default('');
            $table->enum('accept_approve_callback',[0,1])->comment('是否接收通过回调返回值 0否 1是')->default(0);
            $table->string('reject_callback_uri')->comment('驳回回调地址')->default('');
            $table->enum('accept_reject_callback',[0,1])->comment('是否接收驳回回调返回值 0否 1是')->default(0);
            $table->string('transfer_callback_uri')->comment('转交回调地址')->default('');
            $table->enum('accept_transfer_callback',[0,1])->comment('是否接收转交回调返回值 0否 1是')->default(0);
            $table->string('end_callback_uri')->comment('结束回调地址')->default('');
            $table->enum('accept_end_callback',[0,1])->comment('是否接收结束回调返回值 0否 1是')->default(0);
            $table->string('withdraw_callback_uri')->comment('撤回回调地址')->default('');
            $table->enum('accept_withdraw_callback',[0,1])->comment('是否接收撤回回调返回值 0否 1是')->default(0);
            $table->char('x',50)->comment('步骤坐标X轴')->default('');
            $table->char('y',50)->comment('步骤坐标Y轴')->default('');
            $table->enum('send_todo',[1,0])->comment('发送待办信息 1是 0否')->default(1);
            $table->enum('send_start',[1,0])->comment('发送信息给发起人 1是 0否')->default(0);
            $table->enum('is_cc',[0,1])->comment('是否抄送 1是，0否')->default(0);
            $table->text('cc_person')->comment('抄送人')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['flow_id', 'step_key','deleted_at']);
            $table->foreign('flow_id')->references('id')->on('flows');
        });

        /* 运行-流程 */
        Schema::create('flow_run', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('flow_id')->comment('流程ID');
            $table->unsignedInteger('flow_number')->comment('流程编号')->index();
            $table->char('name', 20)->comment('流程名称')->index();
            $table->unsignedInteger('flow_type_id')->comment('流程分类ID')->index();
            $table->unsignedInteger('form_id')->comment('表单ID');
            $table->unsignedInteger('form_number')->comment('表单编号')->index();
            $table->unsignedMediumInteger('creator_sn')->comment('发起人编号');
            $table->char('creator_name', 10)->comment('发起人姓名');
            $table->tinyInteger('status')->comment('流程状态 0:运行中 1:结束 -2:撤回 -1:驳回')->default(0);
            $table->dateTime('end_at')->comment('结束时间')->nullable();
            $table->char('process_instance_id',50)->nullable()->comment('审批实例id')->index();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('flow_id')->references('id')->on('flows');
            $table->foreign('form_id')->references('id')->on('forms');
        });

        /* 运行-步骤 */
        Schema::create('step_run', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('step_id')->comment('步骤ID');
            $table->unsignedInteger('step_key')->comment('步骤key');
            $table->char('step_name', 20)->comment('步骤名称');
            $table->unsignedInteger('flow_type_id')->comment('流程分类ID');
            $table->unsignedInteger('flow_id')->comment('流程ID');
            $table->char('flow_name', 20)->comment('流程名称');
            $table->unsignedInteger('flow_run_id')->comment('运行ID');
            $table->unsignedInteger('form_id')->comment('表单ID');
            $table->unsignedInteger('data_id')->comment('表单数据ID');
            $table->unsignedMediumInteger('approver_sn')->comment('审批人编号');
            $table->char('approver_name', 10)->comment('审批人姓名');
            $table->dateTime('checked_at')->comment('查看时间')->nullable();
            $table->tinyInteger('action_type')->default(0)->comment('操作类型 0:未操作,1：发起，2：通过，3：转交，-1：驳回,-2:撤回,-3:取消');
            $table->dateTime('acted_at')->comment('操作时间')->nullable();
            $table->char('remark', 200)->comment('操作备注')->default('');
            $table->unsignedTinyInteger('is_rejected')->default(0)->comment('上一步为驳回 1是 0否');
            $table->text('next_id')->nullable()->comment('下一步id');
            $table->text('prev_id')->nullable()->comment('上一步id');
            $table->unsignedTinyInteger('is_send_todo')->default(0)->comment('是否发送待办通知 0否，1是');
            $table->timestamps();
            $table->softDeletes();
            $table->index('step_key');
            $table->index('approver_sn');
            $table->index('flow_run_id');
            $table->index('flow_type_id');
            $table->foreign('flow_id')->references('id')->on('flows');
            $table->foreign('flow_run_id')->references('id')->on('flow_run');
            $table->foreign('step_id')->references('id')->on('steps');
            $table->foreign('form_id')->references('id')->on('forms');
        });
        /* ---- 流程 END ---- */
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('step_run');
        Schema::dropIfExists('flow_run');
        Schema::dropIfExists('steps');
        Schema::dropIfExists('flows_has_departments');
        Schema::dropIfExists('flows_has_roles');
        Schema::dropIfExists('flows_has_staff');
        Schema::dropIfExists('flows');
        Schema::dropIfExists('flow_types');
        Schema::dropIfExists('fields_has_validators');
        Schema::dropIfExists('field_user_widgets');
        Schema::dropIfExists('fields');
        Schema::dropIfExists('validators');
        Schema::dropIfExists('form_grids');
        Schema::dropIfExists('forms');
        Schema::dropIfExists('form_types');
    }
}
