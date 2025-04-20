<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ListOfMemberModel;

class ResetAttendanceStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset:attendance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset attendance_status of all members to 0 every Sunday';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        ListOfMemberModel::query()->update(['attendance_status' => 0]);
        $this->info('Successfully reset attendance_status for all members.');
    }
}