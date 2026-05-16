<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = Account::all();
        $bdo = $accounts->where('name', 'BDO Savings')->first();
        $cash = $accounts->where('name', 'Cash on Hand')->first();
        $gcash = $accounts->where('name', 'GCash')->first();
        $cc = $accounts->where('name', 'BDO Credit Card')->first();

        $cats = Category::all()->keyBy('name');
        $now = Carbon::now();
        $m1 = $now->copy()->subMonths(2);
        $m2 = $now->copy()->subMonth();
        $m3 = $now->copy();

        $txns = [
            [$bdo->id, $cats['Salary']->id, 'income', 35000, $m1->copy()->day(1), 'Monthly Salary'],
            [$bdo->id, $cats['Freelance']->id, 'income', 8000, $m1->copy()->day(5), 'Website project'],
            [$cash->id, $cats['Food & Dining']->id, 'expense', 350, $m1->copy()->day(2), 'Jollibee lunch'],
            [$gcash->id, $cats['Transportation']->id, 'expense', 180, $m1->copy()->day(3), 'Grab ride'],
            [$bdo->id, $cats['Utilities']->id, 'expense', 1800, $m1->copy()->day(5), 'Meralco bill'],
            [$bdo->id, $cats['Utilities']->id, 'expense', 1299, $m1->copy()->day(7), 'PLDT internet'],
            [$cash->id, $cats['Food & Dining']->id, 'expense', 250, $m1->copy()->day(8), 'McDo breakfast'],
            [$cc->id, $cats['Shopping']->id, 'expense', 2500, $m1->copy()->day(10), 'SM store'],
            [$gcash->id, $cats['Entertainment']->id, 'expense', 299, $m1->copy()->day(11), 'Netflix'],
            [$cash->id, $cats['Groceries']->id, 'expense', 3200, $m1->copy()->day(12), 'SM groceries'],
            [$cash->id, $cats['Transportation']->id, 'expense', 500, $m1->copy()->day(13), 'Beep reload'],
            [$bdo->id, $cats['Rent']->id, 'expense', 12000, $m1->copy()->day(1), 'Apartment rent'],
            [$gcash->id, $cats['Food & Dining']->id, 'expense', 450, $m1->copy()->day(15), 'GrabFood'],
            [$cash->id, $cats['Personal Care']->id, 'expense', 800, $m1->copy()->day(16), 'Haircut'],
            [$bdo->id, $cats['Salary']->id, 'income', 35000, $m1->copy()->day(15), 'Salary 2nd half'],
            [$cc->id, $cats['Health & Medical']->id, 'expense', 1500, $m1->copy()->day(18), 'Medical checkup'],
            [$cash->id, $cats['Food & Dining']->id, 'expense', 380, $m1->copy()->day(20), 'Chowking dinner'],
            [$gcash->id, $cats['Entertainment']->id, 'expense', 150, $m1->copy()->day(22), 'Spotify'],
            [$cash->id, $cats['Groceries']->id, 'expense', 2800, $m1->copy()->day(25), 'Puregold'],
            [$bdo->id, $cats['Education']->id, 'expense', 1500, $m1->copy()->day(28), 'Udemy course'],
            [$bdo->id, $cats['Salary']->id, 'income', 35000, $m2->copy()->day(1), 'Salary April'],
            [$cash->id, $cats['Food & Dining']->id, 'expense', 420, $m2->copy()->day(2), 'KFC meal'],
            [$gcash->id, $cats['Transportation']->id, 'expense', 250, $m2->copy()->day(3), 'Grab to mall'],
            [$bdo->id, $cats['Utilities']->id, 'expense', 2100, $m2->copy()->day(5), 'Meralco April'],
            [$bdo->id, $cats['Utilities']->id, 'expense', 1299, $m2->copy()->day(7), 'PLDT April'],
            [$bdo->id, $cats['Rent']->id, 'expense', 12000, $m2->copy()->day(1), 'Rent April'],
            [$cash->id, $cats['Food & Dining']->id, 'expense', 280, $m2->copy()->day(6), 'Mang Inasal'],
            [$cc->id, $cats['Shopping']->id, 'expense', 4500, $m2->copy()->day(8), 'Uniqlo clothes'],
            [$gcash->id, $cats['Entertainment']->id, 'expense', 299, $m2->copy()->day(9), 'Netflix'],
            [$cash->id, $cats['Groceries']->id, 'expense', 3500, $m2->copy()->day(10), 'SM weekly'],
            [$cash->id, $cats['Transportation']->id, 'expense', 400, $m2->copy()->day(11), 'MRT fare'],
            [$bdo->id, $cats['Freelance']->id, 'income', 12000, $m2->copy()->day(12), 'Logo design'],
            [$gcash->id, $cats['Food & Dining']->id, 'expense', 520, $m2->copy()->day(14), 'Foodpanda'],
            [$bdo->id, $cats['Salary']->id, 'income', 35000, $m2->copy()->day(15), 'Salary 2nd half'],
            [$cash->id, $cats['Personal Care']->id, 'expense', 600, $m2->copy()->day(16), 'Skincare'],
            [$cc->id, $cats['Entertainment']->id, 'expense', 800, $m2->copy()->day(18), 'Cinema'],
            [$cash->id, $cats['Food & Dining']->id, 'expense', 350, $m2->copy()->day(20), 'Pizza'],
            [$cash->id, $cats['Groceries']->id, 'expense', 2900, $m2->copy()->day(22), 'Puregold'],
            [$gcash->id, $cats['Transportation']->id, 'expense', 200, $m2->copy()->day(24), 'Angkas'],
            [$bdo->id, $cats['Health & Medical']->id, 'expense', 2000, $m2->copy()->day(26), 'Dental'],
            [$bdo->id, $cats['Salary']->id, 'income', 35000, $m3->copy()->day(1), 'Salary May'],
            [$bdo->id, $cats['Rent']->id, 'expense', 12000, $m3->copy()->day(1), 'Rent May'],
            [$cash->id, $cats['Food & Dining']->id, 'expense', 290, $m3->copy()->day(2), 'Jollibee'],
            [$gcash->id, $cats['Transportation']->id, 'expense', 320, $m3->copy()->day(2), 'Grab to work'],
            [$bdo->id, $cats['Utilities']->id, 'expense', 1950, $m3->copy()->day(3), 'Meralco May'],
            [$bdo->id, $cats['Utilities']->id, 'expense', 1299, $m3->copy()->day(5), 'PLDT May'],
            [$cash->id, $cats['Groceries']->id, 'expense', 3800, $m3->copy()->day(4), 'SM groceries'],
            [$cc->id, $cats['Shopping']->id, 'expense', 1800, $m3->copy()->day(5), 'Lazada'],
            [$gcash->id, $cats['Entertainment']->id, 'expense', 299, $m3->copy()->day(6), 'Netflix'],
            [$cash->id, $cats['Food & Dining']->id, 'expense', 480, $m3->copy()->day(7), 'Restaurant'],
            [$cash->id, $cats['Transportation']->id, 'expense', 300, $m3->copy()->day(8), 'Beep reload'],
            [$gcash->id, $cats['Food & Dining']->id, 'expense', 380, $m3->copy()->day(9), 'GrabFood'],
            [$bdo->id, $cats['Freelance']->id, 'income', 5000, $m3->copy()->day(10), 'Design task'],
            [$cash->id, $cats['Personal Care']->id, 'expense', 450, $m3->copy()->day(10), 'Haircut'],
            [$cc->id, $cats['Health & Medical']->id, 'expense', 3500, $m3->copy()->day(11), 'Physical exam'],
            [$cash->id, $cats['Food & Dining']->id, 'expense', 320, $m3->copy()->day(12), 'Inasal lunch'],
            [$bdo->id, $cats['Salary']->id, 'income', 35000, $m3->copy()->day(15), 'Salary 2nd half'],
            [$cash->id, $cats['Groceries']->id, 'expense', 2600, $m3->copy()->day(14), 'Puregold'],
            [$gcash->id, $cats['Entertainment']->id, 'expense', 150, $m3->copy()->day(15), 'Spotify'],
            [$cash->id, $cats['Food & Dining']->id, 'expense', 550, $m3->copy()->day(min($m3->day, 16)), 'Samgyupsal'],
        ];

        foreach ($txns as [$accId, $catId, $type, $amt, $date, $desc]) {
            $t = Transaction::create([
                'account_id' => $accId, 'category_id' => $catId,
                'type' => $type, 'amount' => $amt,
                'transaction_date' => $date, 'description' => $desc,
            ]);
            $acc = Account::find($accId);
            $acc->current_balance = $type === 'income'
                ? (float)$acc->current_balance + $amt
                : (float)$acc->current_balance - $amt;
            $acc->save();
        }
    }
}
