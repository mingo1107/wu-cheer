<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CalculateTaiwanPowerBillCommand extends Command
{
    /**
     * 定義指令與 Input 參數
     */
    protected $signature = 'elec:calculate-bill
                            {--type=hv : 電壓類型 (hv:高壓, ehv:特高壓)}
                            {--month=9 : 帳單月份}
                            {--c_normal=950 : 經常契約容量 (kW)}
                            {--d_normal=1131 : 經常實際需量 (kW)}
                            {--c_half=0 : 半尖峰契約容量 (kW)}
                            {--d_half=1223 : 半尖峰實際需量 (kW)}
                            {--c_sat=0 : 週六半尖峰契約容量 (kW)}
                            {--d_sat=921 : 週六半尖峰實際需量 (kW)}
                            {--c_off=0 : 離峰契約容量 (kW)}
                            {--d_off=1041 : 離峰實際需量 (kW)}
                            {--kwh_peak=98316 : 尖峰用電度數}
                            {--kwh_half=168056 : 半尖峰用電度數}
                            {--kwh_sat=49032 : 週六半尖峰用電度數}
                            {--kwh_off=199760 : 離峰用電度數}
                            {--pf=98 : 功率因數 (0-100)}
                            {--pf_base=80 : 功率因數基準 (標準為 80)}
                            {--adj_factor=0.938 : 調整係數}
                            {--rate_peak= : 自訂尖峰流動費率 (若未設定則自動帶入預設值)}
                            {--rate_half= : 自訂半尖峰流動費率}
                            {--rate_sat= : 自訂週六流動費率}
                            {--rate_off= : 自訂離峰流動費率}
                            {--real_tax_base=2554739 : 帳單稅前金額 (用於比對)}
                            {--real_tax=127737 : 帳單稅額 (用於比對)}
                            {--real_agreed_base= : 帳單基本電費 (約定)}
                            {--real_penalty= : 帳單非約定基本電費}
                            {--real_flow_fee= : 帳單流動電費}
                            {--real_pf_bonus= : 帳單功率因數調整}
                            {--real_policy_adj= : 帳單政策性調整}
                            {--policy_adj= : 政策性電價調整金額 (若未設定則自動帶入預設值)}
                            {--base_rate= : 自訂基本電費費率 (經常)}
                            {--file= : 參數設定檔路徑 (JSON 格式，相對於 storage/app/power-bills)}';

    protected $description = '完全自動化計算 114 年 9 月高壓三段式電費與獎懲邏輯';

    public function handle()
    {
        // 0. 讀取 JSON 設定檔 (若有)
        $jsonParams = [];
        $file = $this->option('file');

        if ($file) {
            $filePath = storage_path('app/power-bills/' . $file);
            if (file_exists($filePath)) {
                $jsonContent = file_get_contents($filePath);
                $jsonParams = json_decode($jsonContent, true);
                $this->info("已讀取參數檔：$file");
            } else {
                $this->error("找不到檔案：$filePath");
                return;
            }
        }

        // Helper：取得參數 (優先順序：JSON > CLI Option > Default)
        // 為了讓 JSON 生效，我們需要判斷 CLI 是否傳入預設值。
        // 但簡單起見，我們依照 User 指示「以檔案內的資料為主」。
        $getParam = function($key) use ($jsonParams) {
            // 如果 JSON 有該 Key，直接使用 JSON 的值
            if (isset($jsonParams[$key])) {
                return $jsonParams[$key];
            }
            // 否則使用 CLI 參數 (含預設值)
            return $this->option($key);
        };

        // 1. 設定費率與參數
        $type = $getParam('type'); // hv or ehv

        // 預設參數變數
        $baseRate = 0;
        $baseRateHalf = 0;
        $rates = [];

        // 政策調整金額：優先讀取參數/JSON，若無則依類型給預設值
        $policyAdjInput = $getParam('policy_adj');
        if ($policyAdjInput !== null && $policyAdjInput !== '') {
             $policyAdjAmount = (float)$policyAdjInput;
        } else {
             // 內部預設值邏輯
             $policyAdjAmount = ($type === 'ehv') ? -1557605.2 : -169791.1;
        }

        if ($type === 'ehv') {
            // --- 特高壓 (Extra High Voltage) - 114/07 ---
            $baseRate = 223.6;
            $baseRateHalf = 38.15;
            $policyAdjAmount = -1557605.2; // 範例值
            $rates = ['peak' => 8.865, 'half' => 5.474, 'sat' => 2.256, 'off' => 2.256];
        } else {
            // --- 高壓 (High Voltage) - 114/09 ---
            $baseRate = 209.87;
            $baseRateHalf = 0; // 高壓通常無此獨立合約費率
            // $policyAdjAmount 已在上方處理
            $rates = ['peak' => 9.39, 'half' => 5.85, 'sat' => 2.60, 'off' => 2.53];
        }

        if ($getParam('base_rate')) {
            $baseRate = (float)$getParam('base_rate');
        }

        // 定義離峰契約費率 (用於增量罰金計算) - 移至此處確保罰金計算前可用
        $baseRateOff = ($baseRate > 200) ? 44.70 : 33.30;



        // 2. 基本電費 (約定部分)
        $agreedBase = round(
            ($getParam('c_normal') * $baseRate) +
            ($getParam('c_half') * $baseRateHalf),
            1
        );

        // 3. 非約定基本電費 (超約罰金)
        // 3. 非約定基本電費 (超約罰金)
        // 3. 非約定基本電費 (超約罰金) - 重構為台電「增量不重複」邏輯
        // 定義各時段契約容量 (累積)
        $cNormal = $getParam('c_normal'); // 經常
        $cHalfTotal = $cNormal + $getParam('c_half'); // 經常 + 半尖峰
        $cSatTotal = $cHalfTotal + $getParam('c_sat'); // 經常 + 半尖峰 + 週六
        $cOffTotal = $cSatTotal + $getParam('c_off'); // 經常 + 半尖峰 + 週六 + 離峰 (總契約)

        // 計算各時段「原始超約量」
        $exceedNormal = max(0, $getParam('d_normal') - $cNormal);
        $exceedHalf = max(0, $getParam('d_half') - $cHalfTotal);
        $exceedSat = max(0, $getParam('d_sat') - $cSatTotal);
        $exceedOff = max(0, $getParam('d_off') - $cOffTotal);

        // 計算「淨增量超約」 (依照圖說規則扣抵)
        // 規則：各時間之超約用電，超出部分不重複計算。
        // e.g. 離峰超出數應扣除 尖峰、半尖峰 或 週六半尖峰 超出數之較大者。

        // 1. 經常 (尖峰) 超約
        $volNormal = $exceedNormal;

        // 2. 半尖峰 淨增量 (扣除經常超約)
        $volHalf = max(0, $exceedHalf - $exceedNormal);

        // 3. 週六半尖峰 淨增量 (扣除已經算過的較大者：經常 or 半尖峰)
        // 註：這取決於週六是否視為獨立？圖說："扣除尖峰或半尖峰...之較大者"
        $volSat = max(0, $exceedSat - max($exceedNormal, $exceedHalf));

        // 4. 離峰 淨增量 (扣除前三者較大者)
        $volOff = max(0, $exceedOff - max($exceedNormal, $exceedHalf, $exceedSat));

        // 計算各段罰金金額
        $pNormalAmt = $this->calculatePenaltyForExceedance($volNormal, $cNormal, $baseRate);
        $pHalfAmt = $this->calculatePenaltyForExceedance($volHalf, $cHalfTotal, $baseRate); // 半尖峰通常也是用經常費率計算? 或是半尖峰費率?
        // 驗證過的高壓案例中，經常超約用166.9.
        // 半尖峰若有額外契約，費率依當月設定。若無，則視同經常延伸。
        // 在三段式電價中，基本費率通常只有「經常」與「離峰/週六」。
        // 暫定：半尖峰增量仍用 baseRate (166.9)，除非有明確半尖峰契約費率。

        // 週六與離峰增量，使用 baseRateOff (33.3)
        $pSatAmt = $this->calculatePenaltyForExceedance($volSat, $cSatTotal, $baseRateOff);
        $pOffAmt = $this->calculatePenaltyForExceedance($volOff, $cOffTotal, $baseRateOff);

        $totalPenalty = $pNormalAmt + $pHalfAmt + $pSatAmt + $pOffAmt;

        // 4. 設定流動電費費率 (優先使用傳入參數，否則使用預設值)
        // 預設費率 logic
        $defaultRates = [];
        if ($type === 'ehv') {
            // 特高壓預設 (114/07) 測試值
            $defaultRates = ['peak' => 8.865, 'half' => 5.474, 'sat' => 2.256, 'off' => 2.256];
        } else {
            // 高壓預設 (114/09) 測試值
            $defaultRates = ['peak' => 9.39, 'half' => 5.85, 'sat' => 2.60, 'off' => 2.53];
        }


        // 參數覆寫
        $rates = [
            'peak' => $getParam('rate_peak') ? (float)$getParam('rate_peak') : $defaultRates['peak'],
            'half' => $getParam('rate_half') ? (float)$getParam('rate_half') : $defaultRates['half'],
            'sat'  => $getParam('rate_sat')  ? (float)$getParam('rate_sat')  : $defaultRates['sat'],
            'off'  => $getParam('rate_off')  ? (float)$getParam('rate_off')  : $defaultRates['off'],
        ];

        // 3.5 設定契約基本費率 (用於罰金計算)
        // 離峰契約單價：若未指定則依月份判斷 (夏月 44.7 / 非夏月 33.3)
        // 簡單判斷：若 baseRate 為 223.6 或 233.6 (夏月經常)，則離峰設為 44.7
        // 若 baseRate 為 166.9 (非夏月經常)，則離峰設為 33.3
        // 這裡先寫死邏輯，未來可參數化
        $baseRateOff = ($baseRate > 200) ? 44.70 : 33.30;

        // 4. 流動電費計算
        $flowFee = round(
            ($getParam('kwh_peak') * $rates['peak']) +
            ($getParam('kwh_half') * $rates['half']) +
            ($getParam('kwh_sat')  * $rates['sat']) +
            ($getParam('kwh_off')  * $rates['off']),
            1
        );

        // 5. 功率因數獎酬 (PF 獎懲公式化)
        // 基準 = 約定基本費 + 流動電費 (註：部分產業會包含罰金)
        // 注意：帳單上的 PF 調整金額通常已包含「調整係數 (0.938)」
        $pfBonus = $this->calculatePfBonus(
            $agreedBase + $flowFee,
            (float)$getParam('pf'),
            (float)$getParam('adj_factor'),
            (float)$getParam('pf_base')
        );

        // 6. 總計與調整
        // 根據帳單排版邏輯：
        // 各項金額 (含基本、流動、罰金、PF、調整等) 加總即為「應繳總金額」。
        // 這意味著：列表中的各項金額皆為「含稅」金額 (或台電帳單計算方式為總項加總後不外加營業稅，而是內含)。

        // 應繳總金額 (含稅)
        $totalAmountDue = $agreedBase + $flowFee + $totalPenalty - $pfBonus - 10.0 + $policyAdjAmount;

        // 回推稅前金額與稅額 (除以 1.05)
        // 帳單顯示：稅前 2,554,739，稅額 127,737
        // 系統計算：
        $taxBase = round($totalAmountDue / 1.05, 0);
        $tax = $totalAmountDue - $taxBase;

        // 7. 輸出結果表格
        // 7. 輸出結果表格
        $titleStr = ($type === 'ehv') ? '特高壓 (114/07)' : '高壓 (114/09)';
        $this->info("--- 電費自動化計算報告 [$titleStr] ---");

        // 取得帳單比對數值 (細項)
        $realAgreedBase = $getParam('real_agreed_base');
        $realPenalty = $getParam('real_penalty');
        $realFlowFee = $getParam('real_flow_fee');
        $realPfBonus = $getParam('real_pf_bonus'); // 應為負值 or 正值? 通常輸入時帶入帳單上的數值
        $realPolicyAdj = $getParam('real_policy_adj'); // 通常為負值

        // Helper to format or show '---'
        $fmt = function($val, $decimals=1) {
            return ($val !== null && $val !== '') ? number_format((float)$val, $decimals) : '---';
        };

        // 取得帳單比對數值 (總額)
        $realTaxBase = (int)$getParam('real_tax_base');
        $realTax = (int)$getParam('real_tax');
        $realTotal = $realTaxBase + $realTax;

        // 差異計算
        $diffTaxBase = $taxBase - $realTaxBase;
        $diffTax = $tax - $realTax;
        $diffTotal = $totalAmountDue - $realTotal;

        $this->table(
            ['項目', '帳單金額 (A)', '系統計算 (B)', '差異 (B-A)'],
            [
                ['基本電費 (約定)', $fmt($realAgreedBase, 1), number_format($agreedBase, 1), ($realAgreedBase !== null) ? number_format($agreedBase - $realAgreedBase, 1) : '---'],
                ['非約定基本電費 (罰金)', $fmt($realPenalty, 1), number_format($totalPenalty, 1), ($realPenalty !== null) ? number_format($totalPenalty - $realPenalty, 1) : '---'],
                ['流動電費 (度數計費)', $fmt($realFlowFee, 1), number_format($flowFee, 1), ($realFlowFee !== null) ? number_format($flowFee - $realFlowFee, 1) : '---'],
                ['功率因數調整', $fmt($realPfBonus, 1), number_format($pfBonus * -1, 1), ($realPfBonus !== null) ? number_format(($pfBonus * -1) - $realPfBonus, 1) : '---'], // pfBonus 在系統算出來是正的獎勵金，但在表上要顯示負項，或依帳單顯示
                ['政策性電價調整', $fmt($realPolicyAdj, 1), number_format($policyAdjAmount, 1), ($realPolicyAdj !== null) ? number_format($policyAdjAmount - $realPolicyAdj, 1) : '---'],
                new \Symfony\Component\Console\Helper\TableSeparator(),
                ['稅前總額', number_format($realTaxBase), number_format($taxBase, 0), number_format($diffTaxBase, 0)],
                ['營業稅 (5%)', number_format($realTax), number_format($tax, 0), number_format($diffTax, 0)],
                ['應繳總金額', number_format($realTotal), '<fg=yellow>' . number_format($totalAmountDue, 0) . '</>', number_format($diffTotal, 0)],
            ]
        );

        $this->info("註：若需完全吻合帳單，政策性電價調整 (Policy Adj) 需傳入準確負項金額。");
    }



    /**
     * 計算特定超約量的罰金
     * 邏輯：雖然是「增量」，但寬限值比例 (10%) 仍以該時段的「總契約容量」為基準？
     * 驗證案例顯示：當離峰增量 9kW (總超約 106>70) 時，卻用了 2 倍罰金。
     * 這意味著，針對「增量部分」，它可能被視為「新的超約」，重新享有由 0 開始的 10% 寬限額度？
     *
     * 假設邏輯：增量視同獨立超約。
     * 10% Limit = Contract * 10%
     * 若 增量 <= Limit: 2倍
     * 若 增量 > Limit: 前段 2倍, 後段 3倍
     */
    private function calculatePenaltyForExceedance($volume, $contract, $rate)
    {
        if ($volume <= 0) return 0;

        $limit10 = $contract * 0.1;

        if ($volume <= $limit10) {
            return round($volume * $rate * 2, 1);
        } else {
            $tier1 = $limit10 * $rate * 2;
            $tier2 = ($volume - $limit10) * $rate * 3;
            return round($tier1 + $tier2, 1);
        }
    }

    /**
     * 功率因數獎酬公式：基準 80%，每 1% 增減 0.1% (上限 95%)
     * 調整係數 (0.938) 需在此步驟套用
     */
    private function calculatePfBonus($basis, $pf, $adjFactor, $basePf = 80)
    {
        if ($pf <= $basePf) return 0;

        // 獎勵上限通常計至 95%
        $effectivePf = min($pf, 95);
        $rewardRate = ($effectivePf - $basePf) * 0.001;

        // 根據帳單結果，PF 獎勵金額需乘上調整係數
        return round($basis * $rewardRate * $adjFactor, 1);
    }
}
