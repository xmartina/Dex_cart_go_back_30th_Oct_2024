<?php

namespace App\Notifications\Order;

use App\Models\Order;
use App\Models\Customer;
use App\Notifications\Push\HasNotifications;
use App\Services\FCMService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCreated extends Notification implements ShouldQueue
{
    use Queueable;

    public $order;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        //push notification to vendor
        $token = optional($this->order->shop->owner)->fcm_token;
        $customer_token = optional($this->order->customer)->fcm_token;
        $warehouse_admin_token = !is_null($this->order->warehouse) ? optional($this->order->warehouse->manager)->fcm_token : null;

        if (!is_null($token)) {
            FCMService::send($token, [
                'title' => trans('notifications.order_created.subject', ['order' => $this->order->order_number]),
                'body' => trans('notifications.order_created.message', ['order' => $this->order->order_number]),
            ]);
        }

        if (!is_null($customer_token)) {
            FCMService::send($customer_token, [
                'title' => trans('notifications.order_created.subject', ['order' => $this->order->order_number]),

                'body' => trans('notifications.order_created.message', ['order' => $this->order->order_number]),
            ]);
        }

        if (!is_null($warehouse_admin_token)) {
            FCMService::send($token, [
                'title' => trans('notifications.order_created.subject', ['order' => $this->order->order_number]),
                'body' => trans('notifications.order_created.message', ['order' => $this->order->order_number]),
            ]);
        }

        if ($this->order->device_id !== null) {
            HasNotifications::pushNotification(self::toArray($notifiable));
        }

        if ($notifiable instanceof Customer) {
            return ['mail', 'database'];
        }

        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $subject = trans('notifications.order_created.subject', ['order' => $this->order->order_number]);

        return (new MailMessage)
            ->from(get_sender_email(), get_sender_name())
            ->subject($subject)
            ->markdown('admin.mail.order.created', [
                'url' => get_shop_url($this->order->shop),
                'order' => $this->order
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'order' => $this->order->order_number,
            'device_id' => $this->order->device_id,
            'subject' => trans('notifications.order_created.subject', ['order' => $this->order->order_number]),
            'message' => trans('notifications.order_created.message', ['order' => $this->order->order_number]),
            'status' => $this->order->orderStatus(true),
        ];
    }
}
