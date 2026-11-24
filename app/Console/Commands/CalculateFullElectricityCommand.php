<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CalculateFullElectricityCommand extends Command
{
    /**
     * 參數定義：包含四種契約容量、實際需量、各時段度數、功率因數與調整係數
     */
    protected $signature = 'elec:calculate-full
                            {--month=9 : 帳單月份 (1-12)}
                            {--c_normal=950 : 經常契約容量 (kW)}
                            {--d_normal=1131 : 經常實際最高需量 (kW)}
                            {--kwh_peak=98316 : 尖峰用電度數}
                            {--c_half=0 : 半尖峰契約容量 (kW)}
                            {--d_half=1223 : 半尖峰實際最高需量 (kW)}
                            {--kwh_half=168056 : 半尖峰用電度數}
                            {--c_sat=0 : 週六半尖峰契約容量 (kW)}
                            {--d_sat=921 : 週六半尖峰實際最高需量 (kW)}
                            {--kwh_sat=49032 : 週六半尖峰度數}
                            {--c_off=0 : 離峰契約容量 (kW)}
                            {--d_off=1041 : 離峰實際最高需量 (kW)}
                            {--kwh_off=199760 : 離峰用電度數}
                            {--pf=98 : 功率因數 (0-100)}
                            {--adj=0.938 : 調整係數 (帳單上的 0.938)}';

    protected $description = '模擬 114 年 9 月高壓三段式電費計算系統';

    public function handle()
    {
        $month = (int) $this->option('month');
        // 夏月判定：6/1 ~ 9/30
        $isSummer = ($month >= 6 && $month <= 9);

        // 1. 取得 114 年最新電價單價
        $rates = $this->getRates($isSummer);

        // 2. 計算基本電費 (約定部分)
        $agreedNormal = $this->option('c_normal') * $rates['b_normal'];
        $agreedHalf   = $this->option('c_half')   * $rates['b_half'];
        $agreedSat    = $this->option('c_sat')    * $rates['b_sat'];
        $agreedOff    = $this->option('c_off')    * $rates['b_off'];
        $totalAgreed  = $agreedNormal + $agreedHalf + $agreedSat + $agreedOff;

        // 3. 計算超約罰金 (核心邏輯：離峰需量 1041 就在這裡判定)
        $penaltyNormal = $this->calcPenalty($this->option('c_normal'), $this->option('d_normal'), $rates['b_normal']);

        // 半尖峰判定基準：經常 + 半尖
        $halfThreshold = $this->option('c_normal') + $this->option('c_half');
        $penaltyHalf   = $this->calcPenalty($halfThreshold, $this->option('d_half'), $rates['b_normal']);

        // 離峰判定基準：經常 + 半尖 + 離峰
        $offThreshold  = $this->option('c_normal') + $this->option('c_half') + $this->option('c_off');
        $penaltyOff    = $this->calcPenalty($offThreshold, $this->option('d_off'), $rates['b_normal']);

        $totalPenalty = $penaltyNormal + $penaltyHalf + $penaltyOff;

        // 4. 計算流動電費 (度數 * 單價)
        $flowFee = ($this->option('kwh_peak') * $rates['u_peak']) +
                   ($this->option('kwh_half') * $rates['u_half']) +
                   ($this->option('kwh_sat')  * $rates['u_sat']) +
                   ($this->option('kwh_off')  * $rates['u_off']);

        // 5. 功率因數獎懲 (基於基本費 + 流動費)
        $subtotal = $totalAgreed + $totalPenalty + $flowFee;
        $pfBonus = round($subtotal * (($this->option('pf') - 80) * 0.001), 2);

        // 6. 最終加總與調整係數
        $totalBeforeTax = ($subtotal - $pfBonus) * $this->option('adj');
        $tax = round($totalBeforeTax * 0.05, 0); // 營業稅 5%

        // 輸出結果表格
        $this->info("--- 114年{$month}月 電費計算報告 ---");
        $this->table(['項目', '金額 (TWD)'], [
            ['基本電費 (約定)', number_format($totalAgreed, 2)],
            ['基本電費 (非約定/罰金)', '<fg=red>' . number_format($totalPenalty, 2) . '</>'],
            ['流動電費 (度數計費)', number_format($flowFee, 2)],
            ['功率因數獎勵 (PF:'.$this->option('pf').'%)', '- ' . number_format($pfBonus, 2)],
            ['調整係數影響 ('.$this->option('adj').')', '已套用'],
            ['稅前總計', number_format($totalBeforeTax, 2)],
            ['營業稅 (5%)', number_format($tax, 2)],
            ['應繳總金額', '<fg=yellow>' . number_format($totalBeforeTax + $tax, 0) . '</>'],
        ]);
    }

    /**
     * 計算階梯罰金 (2倍/3倍)
     */
    private function calcPenalty($contract, $demand, $rate)
    {
        $over = $demand - $contract;
        if ($over <= 0) return 0;

        $limit = $contract * 0.1;
        if ($over <= $limit) {
            return $over * $rate * 2;
        } else {
            return ($limit * $rate * 2) + (($over - $limit) * $rate * 3);
        }
    }

    /**
     * 114 年最新高壓三段式電價單價
     */
    private function getRates($isSummer)
    {
        return $isSummer ? [
            'b_normal' => 236.20, 'b_half' => 166.90, 'b_sat' => 47.24, 'b_off' => 47.24,
            'u_peak' => 8.12, 'u_half' => 5.02, 'u_sat' => 2.50, 'u_off' => 2.23
        ] : [
            'b_normal' => 173.20, 'b_half' => 173.20, 'b_sat' => 34.64, 'b_off' => 34.64,
            'u_peak' => 0, 'u_half' => 4.71, 'u_sat' => 2.31, 'u_off' => 2.11
        ];
    }
}
