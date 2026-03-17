<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlacedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;
    protected $hasReward;

    public function __construct(Order $order, bool $hasReward = false)
    {
        $this->order = $order;
        $this->hasReward = $hasReward;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $rewardLabel = $this->hasReward ? ' 🎁 [REWARD INCLUDED]' : '';
        
        return (new MailMessage)
            ->subject('New Order Placed' . $rewardLabel . ' - ' . $this->order->order_number)
            ->greeting('Hello Admin!')
            ->line('A new order has been placed on GasGo.')
            ->line('')
            ->line('**Order Details:**')
            ->line('Order Number: ' . $this->order->order_number)
            ->line('Customer: ' . $this->order->user->name ?? 'N/A')
            ->line('Email: ' . $this->order->user->email ?? 'N/A')
            ->line('Phone: ' . $this->order->contact_number)
            ->line('Delivery Address: ' . $this->order->delivery_address)
            ->line('Total Amount: ₱' . number_format($this->order->total_amount, 2))
            ->line('Payment Method: ' . ucfirst($this->order->payment_method))
            ->line('')
            ->when($this->hasReward, function ($message) {
                return $message->line('⭐ **REWARD INCLUDED** - Pack the freebie with this order!')
                    ->line('Review the reward items in the order details.');
            })
            ->line('')
            ->action('View Order', url('/admin/orders/' . $this->order->id))
            ->line('Thank you for managing GasGo!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'customer_name' => $this->order->user->name ?? 'Unknown',
            'total_amount' => $this->order->total_amount,
            'has_reward' => $this->hasReward,
            'reward_flag' => $this->hasReward ? 'REWARD INCLUDED' : 'STANDARD ORDER',
        ];
    }
}
