<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ListOfMemberModel;

class ResetAttendanceStatus extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'reset:attendance';

    /**
     * The console command description.
     */
    protected $description = 'Reset attendance_status of all members to 0 every Sunday at 1 AM';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $updated = ListOfMemberModel::query()->update(['attendance_status' => 0]);

        $this->info("Successfully reset attendance_status for {$updated} members.");
    }
}
