<?php

namespace App\Notifications;

use App\Models\Delivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RiderReturningNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $delivery;

    public function __construct(Delivery $delivery)
    {
        $this->delivery = $delivery;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Rider Returning to Store - ' . $this->delivery->order->order_number)
            ->greeting('Hello Admin!')
            ->line('A rider has completed their deliveries and is returning to the store.')
            ->line('')
            ->line('**Rider Information:**')
            ->line('Rider: ' . $this->delivery->rider->name ?? 'N/A')
            ->line('Contact: ' . $this->delivery->rider->phone ?? 'N/A')
            ->line('')
            ->line('**Return Status:**')
            ->line('Last Delivery Order: ' . $this->delivery->order->order_number)
            ->line('Return Time: ' . $this->delivery->returned_at?->format('M d, Y g:i A') ?? 'Just Now')
            ->line('')
            ->action('View Deliveries', url('/admin/deliveries'))
            ->line('Thank you for using GasGo!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'delivery_id' => $this->delivery->id,
            'rider_id' => $this->delivery->rider_id,
            'rider_name' => $this->delivery->rider->name ?? 'N/A',
            'order_number' => $this->delivery->order->order_number,
            'status' => 'returning_to_store',
            'message' => 'Rider ' . ($this->delivery->rider->name ?? 'A rider') . ' is returning to store.',
        ];
    }
}
