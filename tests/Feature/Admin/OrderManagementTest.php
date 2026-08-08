<?php

namespace Tests\Feature\Admin;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderManagementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class OrderManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->admin()->create();
    }

    protected function makeOrder(array $overrides = [], array $itemOverrides = []): Order
    {
        $order = Order::factory()->create(array_merge([
            'order_number' => 'ORD-20260807-0001',
            'customer_name' => 'أحمد محمد',
            'customer_phone' => '+963912345678',
            'notes' => 'يرجى التجهيز بسرعة',
            'subtotal' => '170000.00',
            'total' => '170000.00',
            'currency' => 'ل.س',
            'status' => OrderStatus::Pending,
            'whatsapp_sent_at' => now()->subMinutes(5),
        ], $overrides));

        OrderItem::factory()->create(array_merge([
            'order_id' => $order->id,
            'product_name' => 'برغر كلاسيك',
            'product_price' => '85000.00',
            'quantity' => 2,
            'subtotal' => '170000.00',
            'note' => 'بدون بصل',
        ], $itemOverrides));

        return $order->fresh('items');
    }

    public function test_guest_cannot_access_orders_index(): void
    {
        $this->get(route('admin.orders.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_guest_cannot_view_order_details(): void
    {
        $order = $this->makeOrder();

        $this->get(route('admin.orders.show', $order))
            ->assertRedirect(route('admin.login'));
    }

    public function test_guest_cannot_update_order_status(): void
    {
        $order = $this->makeOrder();

        $this->patch(route('admin.orders.status.update', $order), [
            'status' => OrderStatus::Confirmed->value,
        ])->assertRedirect(route('admin.login'));

        $this->assertSame(OrderStatus::Pending, $order->fresh()->status);
    }

    public function test_non_admin_cannot_access_orders(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $order = $this->makeOrder();

        $this->actingAs($user)
            ->get(route('admin.orders.index'))
            ->assertRedirect(route('admin.login'));

        $this->actingAs($user)
            ->get(route('admin.orders.show', $order))
            ->assertRedirect(route('admin.login'));

        $this->actingAs($user)
            ->patch(route('admin.orders.status.update', $order), [
                'status' => OrderStatus::Confirmed->value,
            ])
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_access_orders_index(): void
    {
        $this->makeOrder();

        $this->actingAs($this->admin())
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee('الطلبات', false)
            ->assertSee('ORD-20260807-0001', false)
            ->assertSee('قيد التأكيد', false)
            ->assertSee('عرض الطلب', false);
    }

    public function test_orders_are_sorted_newest_first(): void
    {
        $older = $this->makeOrder([
            'order_number' => 'ORD-20260807-0001',
            'created_at' => now()->subHour(),
        ]);

        $newer = Order::factory()->create([
            'order_number' => 'ORD-20260807-0002',
            'customer_name' => 'سارة',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.orders.index'))
            ->assertOk();

        $this->assertTrue(
            strpos($response->getContent(), $newer->order_number) < strpos($response->getContent(), $older->order_number)
        );
    }

    public function test_orders_pagination_preserves_filters(): void
    {
        Order::factory()->count(21)->create([
            'status' => OrderStatus::Pending,
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.orders.index', ['status' => 'pending', 'page' => 2]))
            ->assertOk()
            ->assertSee('status=pending', false);
    }

    public function test_empty_state_without_orders(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee('لا توجد طلبات حالياً.', false);
    }

    public function test_search_by_order_number_name_and_phone(): void
    {
        $this->makeOrder([
            'order_number' => 'ORD-20260807-0099',
            'customer_name' => 'أحمد محمد',
            'customer_phone' => '+963912345678',
        ]);

        Order::factory()->create([
            'order_number' => 'ORD-20260807-0100',
            'customer_name' => 'سارة',
            'customer_phone' => '+963998765432',
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.orders.index', ['search' => 'ORD-20260807-0099']))
            ->assertOk()
            ->assertSee('ORD-20260807-0099', false)
            ->assertDontSee('ORD-20260807-0100', false);

        $this->actingAs($this->admin())
            ->get(route('admin.orders.index', ['search' => 'أحمد']))
            ->assertOk()
            ->assertSee('ORD-20260807-0099', false)
            ->assertDontSee('ORD-20260807-0100', false);

        $this->actingAs($this->admin())
            ->get(route('admin.orders.index', ['search' => '912345678']))
            ->assertOk()
            ->assertSee('ORD-20260807-0099', false)
            ->assertDontSee('ORD-20260807-0100', false);
    }

    public function test_status_and_date_filters_work_together(): void
    {
        $today = now()->toDateString();

        $this->makeOrder([
            'order_number' => 'ORD-TODAY-PENDING',
            'status' => OrderStatus::Pending,
            'created_at' => now(),
        ]);

        Order::factory()->create([
            'order_number' => 'ORD-TODAY-READY',
            'status' => OrderStatus::Ready,
            'created_at' => now(),
        ]);

        Order::factory()->create([
            'order_number' => 'ORD-YESTERDAY-PENDING',
            'status' => OrderStatus::Pending,
            'created_at' => now()->subDay(),
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.orders.index', [
                'status' => 'pending',
                'date' => $today,
            ]))
            ->assertOk()
            ->assertSee('ORD-TODAY-PENDING', false)
            ->assertDontSee('ORD-TODAY-READY', false)
            ->assertDontSee('ORD-YESTERDAY-PENDING', false);
    }

    public function test_invalid_status_filter_is_ignored(): void
    {
        $this->makeOrder(['order_number' => 'ORD-VALID-1']);

        $this->actingAs($this->admin())
            ->get(route('admin.orders.index', ['status' => 'not-a-status']))
            ->assertOk()
            ->assertSee('ORD-VALID-1', false);
    }

    public function test_filtered_empty_state_offers_reset(): void
    {
        $this->makeOrder();

        $this->actingAs($this->admin())
            ->get(route('admin.orders.index', ['search' => 'لا-يوجد']))
            ->assertOk()
            ->assertSee('لا توجد نتائج مطابقة لبحثك.', false)
            ->assertSee('إعادة ضبط الفلاتر', false);
    }

    public function test_admin_can_view_order_details_with_snapshot_data(): void
    {
        $order = $this->makeOrder();

        $this->actingAs($this->admin())
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee($order->order_number, false)
            ->assertSee('أحمد محمد', false)
            ->assertSee('+963912345678', false)
            ->assertSee('برغر كلاسيك', false)
            ->assertSee('بدون بصل', false)
            ->assertSee('يرجى التجهيز بسرعة', false)
            ->assertSee('170,000 ل.س', false)
            ->assertSee('تم التحويل إلى واتساب', false)
            ->assertDontSee('تم إرسال الرسالة', false)
            ->assertDontSee('تم استلام الرسالة', false);
    }

    public function test_historical_snapshot_survives_product_changes(): void
    {
        $product = Product::factory()->create([
            'name' => 'برغر كلاسيك',
            'price' => '85000.00',
        ]);

        $order = $this->makeOrder([], [
            'product_id' => $product->id,
            'product_name' => 'برغر كلاسيك',
            'product_price' => '85000.00',
        ]);

        $product->update([
            'name' => 'برغر ديلوكس',
            'price' => '95000.00',
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('برغر كلاسيك', false)
            ->assertSee('85,000 ل.س', false)
            ->assertDontSee('برغر ديلوكس', false)
            ->assertDontSee('95,000 ل.س', false);
    }

    public function test_whatsapp_null_timestamp_message(): void
    {
        $order = $this->makeOrder(['whatsapp_sent_at' => null]);

        $this->actingAs($this->admin())
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('لم يتم التحويل إلى واتساب', false);
    }

    public function test_valid_status_transitions(): void
    {
        $order = $this->makeOrder(['status' => OrderStatus::Pending]);
        $admin = $this->admin();

        $flow = [
            OrderStatus::Confirmed,
            OrderStatus::Preparing,
            OrderStatus::Ready,
            OrderStatus::Completed,
        ];

        foreach ($flow as $status) {
            $this->actingAs($admin)
                ->from(route('admin.orders.show', $order))
                ->patch(route('admin.orders.status.update', $order), [
                    'status' => $status->value,
                ])
                ->assertRedirect(route('admin.orders.show', $order))
                ->assertSessionHas('success', 'تم تحديث حالة الطلب بنجاح.');

            $this->assertSame($status, $order->fresh()->status);
        }
    }

    public function test_cancellation_allowed_before_completion(): void
    {
        foreach ([OrderStatus::Pending, OrderStatus::Confirmed, OrderStatus::Preparing, OrderStatus::Ready] as $from) {
            $order = $this->makeOrder([
                'order_number' => 'ORD-CANCEL-'.$from->value,
                'status' => $from,
            ]);

            $this->actingAs($this->admin())
                ->patch(route('admin.orders.status.update', $order), [
                    'status' => OrderStatus::Cancelled->value,
                ])
                ->assertRedirect(route('admin.orders.show', $order));

            $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
        }
    }

    public function test_invalid_status_transitions_are_rejected(): void
    {
        $completed = $this->makeOrder([
            'order_number' => 'ORD-DONE-1',
            'status' => OrderStatus::Completed,
        ]);

        $this->actingAs($this->admin())
            ->from(route('admin.orders.show', $completed))
            ->patch(route('admin.orders.status.update', $completed), [
                'status' => OrderStatus::Preparing->value,
            ])
            ->assertRedirect(route('admin.orders.show', $completed))
            ->assertSessionHasErrors('status');

        $this->assertSame(OrderStatus::Completed, $completed->fresh()->status);

        $cancelled = $this->makeOrder([
            'order_number' => 'ORD-CANC-1',
            'status' => OrderStatus::Cancelled,
        ]);

        $this->actingAs($this->admin())
            ->from(route('admin.orders.show', $cancelled))
            ->patch(route('admin.orders.status.update', $cancelled), [
                'status' => OrderStatus::Completed->value,
            ])
            ->assertSessionHasErrors('status');

        $this->assertSame(OrderStatus::Cancelled, $cancelled->fresh()->status);
    }

    public function test_opening_order_does_not_change_status(): void
    {
        $order = $this->makeOrder(['status' => OrderStatus::Pending]);

        $this->actingAs($this->admin())
            ->get(route('admin.orders.show', $order))
            ->assertOk();

        $this->assertSame(OrderStatus::Pending, $order->fresh()->status);
    }

    public function test_no_order_delete_route_exists(): void
    {
        $this->assertFalse(Route::has('admin.orders.destroy'));

        $order = $this->makeOrder();

        $this->actingAs($this->admin())
            ->delete('/admin/orders/'.$order->id)
            ->assertStatus(405);

        $this->assertDatabaseHas('orders', ['id' => $order->id]);
    }

    public function test_unknown_order_returns_not_found(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/orders/999999')
            ->assertNotFound();
    }

    public function test_dashboard_shows_order_summary_cards(): void
    {
        $this->makeOrder(['status' => OrderStatus::Pending]);
        Order::factory()->create(['status' => OrderStatus::Preparing]);
        Order::factory()->create([
            'status' => OrderStatus::Completed,
            'created_at' => now(),
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('طلبات اليوم', false)
            ->assertSee('قيد التأكيد', false)
            ->assertSee('قيد التحضير', false)
            ->assertSee('مكتملة اليوم', false);
    }

    public function test_pending_count_appears_in_navigation(): void
    {
        Order::factory()->count(2)->create(['status' => OrderStatus::Pending]);

        $this->actingAs($this->admin())
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee('nav-count', false)
            ->assertSee('>2</span>', false);
    }

    public function test_service_transition_matrix(): void
    {
        $service = app(OrderManagementService::class);

        $this->assertTrue($service->canTransition(OrderStatus::Pending, OrderStatus::Confirmed));
        $this->assertTrue($service->canTransition(OrderStatus::Ready, OrderStatus::Cancelled));
        $this->assertFalse($service->canTransition(OrderStatus::Completed, OrderStatus::Preparing));
        $this->assertFalse($service->canTransition(OrderStatus::Cancelled, OrderStatus::Completed));
    }
}
