<?php

namespace App\Console\Commands;

use App\Models\Salary;
use App\Notifications\AvgSalaryNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use NotificationChannels\Telegram\TelegramChannel;

class AvgSalary extends Command
{
    protected $signature = 'avg:salary';

    protected $description = 'Command description';

    public function handle()
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Calculate total income for the current month
        $currentTotalIncome  = Salary::whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->sum('amount');


        $currentDaysInMonth = Carbon::now()->day; // Number of days from the start of the month to the current date
        $currentAverageIncomePerDay = (int) floor($currentTotalIncome / $currentDaysInMonth / 10) * 10;

        $data_array = sprintf('Current Month: Average Income Per Day - %s', $currentAverageIncomePerDay);

        //Notification::route(TelegramChannel::class, '')->notify(new AvgSalaryNotification($data_array));


        // Calculate total income and average income per day for the last 3 months
        for ($i = 0; $i < 2; $i++) {
            $month = Carbon::now()->subMonths($i);
            $totalIncome = Salary::whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->sum('amount');

            $daysInMonth = $month->daysInMonth;
            $averageIncomePerDay = (int) floor($totalIncome / $daysInMonth / 10) * 10;

            $data_array .= sprintf("\n%s: Average Income Per Day - %s", $month->format('M Y'), $averageIncomePerDay);
        }
        Notification::route(TelegramChannel::class, '')->notify(new AvgSalaryNotification($data_array));

    }
}