<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Attendance;
use Carbon\Carbon;

class AutoEndTime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:auto-end-time';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '自動で未退勤のユーザーに退勤時間を設定する';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $today = Carbon::today();
        $now = Carbon::now();

        // 当日の出勤記録があり、退勤していないユーザーを取得
        $attendances = Attendance::whereDate('work_date', $today)
            ->whereNull('end_time')  // 退勤時間が未設定のレコード
            ->get();

        foreach ($attendances as $attendance) {
            $attendance->end_time = $now; // 現在時刻を退勤時間に設定
            $attendance->save();
        }

        $this->info('自動退勤処理が完了しました');
    
    }
}
