<?php

namespace App\Jobs;

use App\Http\Controllers\API\CommonController;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessUserCreatedLocation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user_info; //user_id,IP

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $user_info)
    {
        $this->user_info = $user_info;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // $ip_location = CommonController::get_IP_location($this->user_info["IP"]);
        $ip_location = CommonController::get_IP_location("35.229.214.71");
        $ip_location_log = sprintf(
            "country:%s city:%s district:%s hosting:%s mobile:%s",
            $ip_location->country,
            $ip_location->city,
            $ip_location->district,
            strval($ip_location->hosting),
            strval($ip_location->mobile),
        );
        if (strlen($ip_location_log) > 254) {
            $ip_location_log = substr($ip_location_log, 0, 254); //数据库字段类型为varchar255
        }
        User::where('id', $this->user_info['user_id'])->update(["created_location" => $ip_location_log]);
    }
}
