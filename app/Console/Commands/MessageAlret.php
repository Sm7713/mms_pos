<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\OrderCreated;
use App\Notifications\RememberPaymentForSellPoint;
use Carbon\Carbon;
use DateTime;
use Illuminate\Console\Command;

class MessageAlret extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'message:alert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        // $current = now()->format('H:i:s');

        $users=User::all();
        foreach($users as $user){
            if($user->sellPoint->max_amount<200)
            $user->notify(new RememberPaymentForSellPoint());
        }

    //  return $this->info("Current Time ". $current);

    //    return $this->info($current);
    }
}
