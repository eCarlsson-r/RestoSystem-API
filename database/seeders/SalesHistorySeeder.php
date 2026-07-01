<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalesHistorySeeder extends Seeder
{
    // ── Seed config ──────────────────────────────────────────────────────────
    const DAYS_BACK = 90;
    const BRANCH_ID = 1;
    const CUSTOMER_ID = 1;
    const EMPLOYEE_IDS = [1, 2];
    const TABLE_IDS = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
    const BUFFET_IDS = [1, 2];
    const PAY_METHODS = ['CS' => 55, 'TF' => 30, 'CC' => 15]; // cash / transfer / card
    const INSERT_CHUNK_SIZE = 500;
    const STOCK_LOG_PREFIX = 'HISTSEED-'; // marks rows owned by this seeder, for idempotent re-runs

    // Rows planted by the hand-written demo seeders (SaleSeeder/ReservationSeeder) — cleanup must never touch these.
    const PROTECTED_SALE_IDS = [1, 2, 3];
    const PROTECTED_RESERVATION_IDS = [1, 2];

    private array $products = [];
    private array $drinkProductIds = [];
    private array $mainProductIds = [];
    private array $snackProductIds = [];
    private array $buffets = [];
    private array $recipesByProduct = [];
    private array $stockByKey = [];
    private array $stockDeductions = [];
    private array $stockRemainder = [];
    private int $cashierUserId = 3;
    private int $adminUserId = 1;

    private array $recordBuffer = [];
    private array $invoiceBuffer = [];
    private array $stockLogBuffer = [];

    private int $salesInserted = 0;
    private int $recordsInserted = 0;
    private int $invoicesInserted = 0;
    private int $reservationsInserted = 0;
    private int $stockLogsInserted = 0;

    public function run(): void
    {
        $this->command->info('RestoSystem Sales History Seeder — ' . self::DAYS_BACK . ' days');

        $this->loadReferenceData();

        if (empty($this->products) || empty($this->buffets)) {
            $this->command->error('Missing base data — run ProductSeeder / BuffetSeeder / TableSeeder first.');
            return;
        }

        $startDate = Carbon::now()->subDays(self::DAYS_BACK - 1)->startOfDay();
        $today = Carbon::now()->startOfDay();

        DB::transaction(function () use ($startDate, $today) {
            $this->cleanup($startDate, $today);

            for ($d = 0; $d < self::DAYS_BACK; $d++) {
                $date = $startDate->copy()->addDays($d);
                $this->seedDay($date, $date->isSameDay($today));

                if ($d % 10 === 0) {
                    $this->command->info("  Day {$d}/" . self::DAYS_BACK . " seeded — date: {$date->toDateString()}");
                }
            }

            $this->flushRecords(true);
            $this->flushInvoices(true);
            $this->flushStockLogs(true);
            $this->applyStockDeductions();
        });

        $this->command->info('');
        $this->command->info('Done.');
        $this->command->info("  Sales:        {$this->salesInserted}");
        $this->command->info("  Sale records: {$this->recordsInserted}");
        $this->command->info("  Invoices:     {$this->invoicesInserted}");
        $this->command->info("  Reservations: {$this->reservationsInserted}");
        $this->command->info("  Stock logs:   {$this->stockLogsInserted}");
    }

    // ── Setup ────────────────────────────────────────────────────────────────

    private function loadReferenceData(): void
    {
        $this->products = DB::table('products')->get()->keyBy('id')->all();

        $categoryIdByName = DB::table('categories')->pluck('id', 'name');

        $byCategory = [];
        foreach ($this->products as $product) {
            $byCategory[$product->category_id][] = $product->id;
        }

        $this->drinkProductIds = $byCategory[$categoryIdByName['Minuman'] ?? 0] ?? array_keys($this->products);
        $this->mainProductIds = array_merge(
            $byCategory[$categoryIdByName['Nasi'] ?? 0] ?? [],
            $byCategory[$categoryIdByName['Mie'] ?? 0] ?? [],
            $byCategory[$categoryIdByName['Daging'] ?? 0] ?? []
        );
        $this->snackProductIds = $byCategory[$categoryIdByName['Snack'] ?? 0] ?? [];
        if (empty($this->mainProductIds)) {
            $this->mainProductIds = array_keys($this->products);
        }

        $this->buffets = DB::table('buffets')->get()->keyBy('id')->all();

        foreach (DB::table('recipes')->get() as $recipe) {
            $this->recipesByProduct[(string) $recipe->product_id][] = $recipe;
        }

        $this->stockByKey = DB::table('stocks')
            ->where('branch_id', self::BRANCH_ID)
            ->get()
            ->keyBy(fn ($s) => $s->item_type . '-' . $s->item_code)
            ->all();

        $employees = DB::table('employees')->whereIn('id', self::EMPLOYEE_IDS)->get()->keyBy('id');
        $this->cashierUserId = $employees[2]->user_id ?? 3;
    }

    // ── Idempotent cleanup of a previous run's rolling window ───────────────

    private function cleanup(Carbon $startDate, Carbon $today): void
    {
        $saleIds = DB::table('sales')
            ->where('branch_id', self::BRANCH_ID)
            ->where('customer_id', self::CUSTOMER_ID)
            ->whereNotIn('id', self::PROTECTED_SALE_IDS)
            ->where('date', '>=', $startDate->toDateString())
            ->where('date', '<=', $today->toDateString())
            ->pluck('id');

        if ($saleIds->isEmpty()) {
            return;
        }

        // Reverse this seeder's previous stock deductions before wiping its log trail.
        do {
            $logs = DB::table('stock_logs')
                ->where('invoice_id', 'like', self::STOCK_LOG_PREFIX . '%')
                ->orderBy('id')
                ->limit(self::INSERT_CHUNK_SIZE)
                ->get(['id', 'stock_id', 'get_qty']);

            foreach ($logs as $log) {
                DB::table('stocks')->where('id', $log->stock_id)->increment('quantity', $log->get_qty);
            }

            if ($logs->isNotEmpty()) {
                DB::table('stock_logs')->whereIn('id', $logs->pluck('id'))->delete();
            }
        } while ($logs->count() === self::INSERT_CHUNK_SIZE);

        // sale_records / sale_invoices cascade-delete with their parent sale. Sales must go
        // before reservations since sales.reservation_id is a (non-cascading) FK to reservations.
        foreach ($saleIds->chunk(self::INSERT_CHUNK_SIZE) as $chunk) {
            DB::table('sales')->whereIn('id', $chunk)->delete();
        }

        DB::table('reservations')
            ->whereNotIn('id', self::PROTECTED_RESERVATION_IDS)
            ->where('branch_id', self::BRANCH_ID)
            ->where('customer_id', self::CUSTOMER_ID)
            ->where('event_date', '>=', $startDate->toDateString())
            ->where('event_date', '<=', $today->toDateString())
            ->delete();
    }

    // ── Daily generation ─────────────────────────────────────────────────────

    private function seedDay(Carbon $date, bool $isToday): void
    {
        $dayOfWeek = $date->dayOfWeek; // 0=Sun, 6=Sat
        $isWeekend = in_array($dayOfWeek, [0, 6]);
        $isFriday = $dayOfWeek === 5;

        if ($isWeekend) {
            $txCount = rand(18, 28);
        } elseif ($isFriday) {
            $txCount = rand(14, 22);
        } else {
            $txCount = rand(8, 16);
        }

        $reservationSales = $this->seedReservations($date, $isWeekend, $isToday);
        $txCount = max(0, $txCount - $reservationSales);

        for ($t = 0; $t < $txCount; $t++) {
            $this->seedTransaction($date);
        }

        $this->flushRecords();
        $this->flushInvoices();
        $this->flushStockLogs();
    }

    /** Books buffet reservations for the day and, for the ones that "show up", turns them into a linked sale. */
    private function seedReservations(Carbon $date, bool $isWeekend, bool $isToday): int
    {
        $bookingChance = $isWeekend ? 70 : 15;
        if (rand(1, 100) > $bookingChance) {
            return 0;
        }

        $salesFromReservations = 0;

        foreach (range(1, rand(1, 3)) as $_) {
            $buffetId = self::BUFFET_IDS[array_rand(self::BUFFET_IDS)];
            $buffet = $this->buffets[$buffetId];
            $employeeId = self::EMPLOYEE_IDS[array_rand(self::EMPLOYEE_IDS)];
            $eventHour = rand(11, 19);
            $pax = rand(2, 12);
            $adultCount = max(1, (int) round($pax * 0.75));
            $childCount = $pax - $adultCount;
            $deposit = (int) round($buffet->price_adult * $pax * 0.3);

            $outcome = $isToday
                ? $this->weightedRandom(['confirmed' => 45, 'pending' => 40, 'checked_in' => 15])
                : $this->weightedRandom(['checked_in' => 70, 'cancelled' => 12, 'no_show' => 8, 'confirmed' => 10]);

            $bookedAt = $date->copy()->subDays(rand(1, 14))->setTime(rand(9, 17), rand(0, 59));

            $reservationId = DB::table('reservations')->insertGetId([
                'customer_id' => self::CUSTOMER_ID,
                'event_date' => $date->toDateString(),
                'event_time' => sprintf('%02d:00:00', $eventHour),
                'buffet_id' => $buffetId,
                'branch_id' => self::BRANCH_ID,
                'employee_id' => $employeeId,
                'guaranteed_pax' => $pax,
                'deposit_amount' => $deposit,
                'deposit_status' => $outcome === 'cancelled' ? 'refunded' : 'paid',
                'status' => $outcome === 'checked_in' ? 'confirmed' : $outcome,
                'notes' => null,
                'sale_id' => null,
                'created_at' => $bookedAt,
                'updated_at' => $bookedAt,
            ]);
            $this->reservationsInserted++;

            if ($outcome === 'checked_in') {
                $saleId = $this->seedTransaction($date, [
                    'tableId' => self::TABLE_IDS[array_rand(self::TABLE_IDS)],
                    'employeeId' => $employeeId,
                    'hour' => $eventHour,
                    'buffetId' => $buffetId,
                    'adultCount' => $adultCount,
                    'childCount' => $childCount,
                    'reservationId' => $reservationId,
                ]);

                DB::table('reservations')->where('id', $reservationId)->update([
                    'sale_id' => $saleId,
                    'status' => 'checked_in',
                    'updated_at' => $date->copy()->setTime($eventHour, rand(0, 30)),
                ]);

                $salesFromReservations++;
            }
        }

        return $salesFromReservations;
    }

    /** Creates one sale + its records + invoice. Returns the sale id. */
    private function seedTransaction(Carbon $date, array $overrides = []): int
    {
        $isWeekend = in_array($date->dayOfWeek, [0, 6]);

        $hour = $overrides['hour'] ?? $this->weightedHour();
        $minute = rand(0, 59);
        $saleTime = sprintf('%02d:%02d:%02d', $hour, $minute, rand(0, 59));
        $saleTs = $date->copy()->setTime($hour, $minute);

        $tableId = $overrides['tableId'] ?? self::TABLE_IDS[array_rand(self::TABLE_IDS)];
        $employeeId = $overrides['employeeId'] ?? self::EMPLOYEE_IDS[array_rand(self::EMPLOYEE_IDS)];
        $reservationId = $overrides['reservationId'] ?? null;

        $isBuffet = isset($overrides['buffetId']) || rand(1, 100) <= ($isWeekend ? 25 : 10);
        $buffetId = $overrides['buffetId'] ?? ($isBuffet ? self::BUFFET_IDS[array_rand(self::BUFFET_IDS)] : null);
        $buffet = $isBuffet ? $this->buffets[$buffetId] : null;
        $adultCount = $overrides['adultCount'] ?? ($isBuffet ? rand(2, 8) : null);
        $childCount = $overrides['childCount'] ?? ($isBuffet ? rand(0, 3) : null);
        $adultPrice = $isBuffet ? $buffet->price_adult : null;
        $childPrice = $isBuffet ? $buffet->price_child : null;
        $duration = $isBuffet ? $buffet->duration_minutes : null;

        $discount = rand(1, 100) <= 10 ? (rand(0, 1) ? 5 : 10) : 0; // occasional loyalty/promo discount
        $tax = 10;

        $saleId = DB::table('sales')->insertGetId([
            'branch_id' => self::BRANCH_ID,
            'table_id' => $tableId,
            'employee_id' => $employeeId,
            'customer_id' => self::CUSTOMER_ID,
            'buffet_id' => $buffetId,
            'adult_count' => $adultCount,
            'child_count' => $childCount,
            'adult_price' => $adultPrice,
            'child_price' => $childPrice,
            'duration_minutes' => $duration,
            'date' => $date->toDateString(),
            'time' => $saleTime,
            'discount' => $discount,
            'tax' => $tax,
            'status' => 'D', // historical sales are always closed out
            'reservation_id' => $reservationId,
            'buffet_start_at' => $isBuffet ? $saleTs : null,
            'buffet_end_at' => $isBuffet ? $saleTs->copy()->addMinutes($duration) : null,
            'created_at' => $saleTs,
            'updated_at' => $saleTs,
        ]);
        $this->salesInserted++;

        // Buffet sessions mostly live off the spread; alacarte sessions order more line items.
        $itemCount = $isBuffet ? rand(1, 4) : rand(2, 6);
        $selectedProducts = $this->pickProducts($itemCount);

        $subtotal = 0;
        foreach ($selectedProducts as $pid) {
            $product = $this->products[$pid];
            $qty = rand(1, 3);
            $price = (int) $product->price;
            $lineDiscountPcnt = rand(1, 100) <= 5 ? 10 : 0;
            $lineDiscountAmnt = (int) round($price * $qty * $lineDiscountPcnt / 100);
            $subtotal += ($price * $qty) - $lineDiscountAmnt;

            $this->recordBuffer[] = [
                'sale_id' => $saleId,
                'item_type' => 'product',
                'item_code' => (string) $pid,
                'quantity' => $qty,
                'item_price' => $price,
                'discount_pcnt' => $lineDiscountPcnt,
                'discount_amnt' => $lineDiscountAmnt,
                'item_note' => null,
                'item_status' => 'D',
                'order_employee' => (string) $employeeId,
                'order_date' => $date->toDateString(),
                'order_time' => $saleTime,
                'deliver_employee' => (string) $employeeId,
                'printed_at' => $saleTs,
                'created_at' => $saleTs,
                'updated_at' => $saleTs,
            ];
            $this->recordsInserted++;

            $this->queueStockDeduction((int) $pid, $qty, $saleId, $saleTs);
        }

        if ($isBuffet) {
            $subtotal += ($adultCount * $adultPrice) + ($childCount * $childPrice);
        }

        $subtotal = (int) round($subtotal * (1 - $discount / 100));
        $totalWithTax = (int) round($subtotal * (1 + $tax / 100));

        $payMethod = $this->weightedRandom(self::PAY_METHODS);
        $payBank = $payMethod === 'TF' ? 'BCA' : null;
        $cardType = $payMethod === 'CC' ? 'VISA' : null;
        $payAmount = $payMethod === 'CS' ? (int) (ceil($totalWithTax / 1000) * 1000) : $totalWithTax;
        $payChange = $payAmount - $totalWithTax;
        $invoiceEmployeeId = rand(1, 100) <= 85 ? $this->cashierUserId : $this->adminUserId;
        $invoiceTs = $saleTs->copy()->addMinutes(rand(30, 90));

        $this->invoiceBuffer[] = [
            'sale_id' => $saleId,
            'pay_method' => $payMethod,
            'pay_bank' => $payBank,
            'pay_card' => null,
            'pay_amount' => $payAmount,
            'pay_change' => $payChange,
            'card_type' => $cardType,
            'voucher' => null,
            'employee_id' => $invoiceEmployeeId,
            'created_at' => $invoiceTs,
            'updated_at' => $invoiceTs,
        ];
        $this->invoicesInserted++;

        return $saleId;
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /** Always includes a drink; leans on mains, with the occasional snack, mirroring real ordering habits. */
    private function pickProducts(int $count): array
    {
        $selected = [$this->drinkProductIds[array_rand($this->drinkProductIds)]];

        $pool = array_merge($this->mainProductIds, $this->mainProductIds, $this->snackProductIds, array_keys($this->products));

        while (count($selected) < $count && count($selected) < count($this->products)) {
            $pid = $pool[array_rand($pool)];
            if (!in_array($pid, $selected, true)) {
                $selected[] = $pid;
            }
        }

        return $selected;
    }

    /** Biases toward the busiest slice of the lunch/dinner windows rather than a flat spread. */
    private function weightedHour(): int
    {
        $isLunch = rand(1, 100) <= 55;

        return $isLunch
            ? $this->weightedRandom([11 => 15, 12 => 35, 13 => 35, 14 => 15])
            : $this->weightedRandom([17 => 10, 18 => 25, 19 => 30, 20 => 25, 21 => 10]);
    }

    /** Deducts recipe-linked stock for a sold product, accruing fractional recipe quantities until a whole unit is owed. */
    private function queueStockDeduction(int $productId, int $qty, int $saleId, Carbon $saleTs): void
    {
        $recipes = $this->recipesByProduct[(string) $productId] ?? [];

        foreach ($recipes as $recipe) {
            $stock = $this->stockByKey[$recipe->item_type . '-' . $recipe->item_code] ?? null;
            if (!$stock) {
                continue;
            }

            $remainder = ($this->stockRemainder[$stock->id] ?? 0) + ($recipe->quantity * $qty);
            $deduct = (int) floor($remainder);
            $this->stockRemainder[$stock->id] = $remainder - $deduct;

            if ($deduct <= 0) {
                continue;
            }

            $this->stockDeductions[$stock->id] = ($this->stockDeductions[$stock->id] ?? 0) + $deduct;

            $productName = $this->products[$productId]->name ?? 'product';
            $this->stockLogBuffer[] = [
                'stock_id' => $stock->id,
                'invoice_id' => self::STOCK_LOG_PREFIX . $saleId,
                'description' => "Auto-deduct for sale #{$saleId} ({$productName} x{$qty})",
                'add_qty' => 0,
                'get_qty' => $deduct,
                'created_at' => $saleTs,
                'updated_at' => $saleTs,
            ];
            $this->stockLogsInserted++;
        }
    }

    private function applyStockDeductions(): void
    {
        foreach ($this->stockDeductions as $stockId => $qty) {
            DB::table('stocks')->where('id', $stockId)->decrement('quantity', $qty);
        }
    }

    private function flushRecords(bool $force = false): void
    {
        if (empty($this->recordBuffer) || (!$force && count($this->recordBuffer) < self::INSERT_CHUNK_SIZE)) {
            return;
        }
        foreach (array_chunk($this->recordBuffer, self::INSERT_CHUNK_SIZE) as $chunk) {
            DB::table('sale_records')->insert($chunk);
        }
        $this->recordBuffer = [];
    }

    private function flushInvoices(bool $force = false): void
    {
        if (empty($this->invoiceBuffer) || (!$force && count($this->invoiceBuffer) < self::INSERT_CHUNK_SIZE)) {
            return;
        }
        foreach (array_chunk($this->invoiceBuffer, self::INSERT_CHUNK_SIZE) as $chunk) {
            DB::table('sale_invoices')->insert($chunk);
        }
        $this->invoiceBuffer = [];
    }

    private function flushStockLogs(bool $force = false): void
    {
        if (empty($this->stockLogBuffer) || (!$force && count($this->stockLogBuffer) < self::INSERT_CHUNK_SIZE)) {
            return;
        }
        foreach (array_chunk($this->stockLogBuffer, self::INSERT_CHUNK_SIZE) as $chunk) {
            DB::table('stock_logs')->insert($chunk);
        }
        $this->stockLogBuffer = [];
    }

    private function weightedRandom(array $weights): int|string
    {
        $total = array_sum($weights);
        $rand = rand(1, $total);
        $cumulative = 0;
        foreach ($weights as $key => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $key;
            }
        }
        return array_key_first($weights);
    }
}
