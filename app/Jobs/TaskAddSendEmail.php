<?php

namespace App\Jobs;

use App\Task;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\MailService;
use Illuminate\Support\Facades\Log;

class TaskAddSendEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $task;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function handle(MailService $mail, User $user)
    {
        $template = 'emails.task_add';
        $user_email = $user->find($this->task->user_id)->email;
        $name = '任务添加通知';
        $data = [
            'task' => $this->task,
            'plan' => 'App\Http\Controllers\Controller'
        ];

        //发送邮件
        $mail->mailSend($template, $user_email, $name, $data);

        //记录到日志
        Log::info('Task add success notice(email):',$this->task->toArray());
    }
}
