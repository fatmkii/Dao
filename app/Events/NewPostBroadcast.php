<?php

namespace App\Events;

use App\Models\Post;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewPostBroadcast implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $post_id;
    public $thread_id;
    public $post_floor;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(int $thread_id, int $post_id, int $post_floor)
    {
        $this->post_id = $post_id;
        $this->thread_id = $thread_id;
        $this->post_floor = $post_floor;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('thread_' . $this->thread_id);
    }

    //自定义广播名称
    public function broadcastAs()
    {
        return 'NewPost';
    }

    //广播的内容
    // public function broadcastWith()
    // {
    //     return [
    //         'data' => $this->post_,
    //     ];
    // }

    //判定是否可以广播
    public function broadcastWhen()
    {
        return true;
    }
}
