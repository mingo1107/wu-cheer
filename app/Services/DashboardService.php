<?php

namespace App\Services;

use App\Repositories\EarthDataRepository;
use App\Repositories\EarthDataDetailRepository;
use App\Repositories\CustomerRepository;
use App\Repositories\CleanerRepository;
use App\Repositories\VerifierRepository;
use App\Repositories\AnnouncementRepository;
use Illuminate\Support\Facades\Auth;

class DashboardService extends BaseService
{
    public function __construct(
        private EarthDataRepository $earthDataRepo,
        private EarthDataDetailRepository $earthDataDetailRepo,
        private CustomerRepository $customerRepo,
        private CleanerRepository $cleanerRepo,
        private VerifierRepository $verifierRepo,
        private AnnouncementRepository $announcementRepo
    ) {
        parent::__construct();
    }

    /**
     * 取得儀表板統計資料
     *
     * @return array
     */
    public function getDashboardStats(): array
    {
        $companyId = Auth::guard('api')->check() && isset(Auth::guard('api')->user()->company_id)
            ? Auth::guard('api')->user()->company_id
            : null;

        // 土單統計
        $earthDataStats = $this->earthDataRepo->getStats($companyId);

        // 土單明細統計
        $earthDataDetailStats = $this->earthDataDetailRepo->getStatsByCompany($companyId);

        // 基礎資料統計
        $basicStats = [
            'customers' => $this->customerRepo->getCountByCompany($companyId),
            'cleaners' => $this->cleanerRepo->getCountByCompany($companyId),
            'verifiers' => $this->verifierRepo->getActiveCountByCompany($companyId),
        ];

        // 最近活動
        $recentActivities = $this->getRecentActivities($companyId);

        // 本月核銷趨勢
        $monthlyTrend = $this->earthDataDetailRepo->getMonthlyVerifyTrend($companyId);

        return [
            'earth_data' => $earthDataStats,
            'earth_data_detail' => $earthDataDetailStats,
            'basic' => $basicStats,
            'recent_activities' => $recentActivities,
            'monthly_trend' => $monthlyTrend,
        ];
    }

    /**
     * 取得最近活動
     *
     * @param int|null $companyId
     * @return array
     */
    private function getRecentActivities(?int $companyId): array
    {
        // 最近建立的土單（5筆）
        $recentEarthData = $this->earthDataRepo->getRecentEarthData($companyId, 5)
            ->map(fn($item) => [
                'id' => $item->id,
                'type' => 'earth_data',
                'title' => $item->batch_no . ' - ' . $item->project_name,
                'subtitle' => $item->customer_name,
                'created_at' => $item->created_at,
            ]);

        // 最近核銷記錄（5筆）
        $recentVerifies = $this->earthDataDetailRepo->getRecentVerifies($companyId, 5)
            ->map(fn($item) => [
                'id' => $item->id,
                'type' => 'verify',
                'title' => '核銷：' . $item->barcode,
                'subtitle' => $item->project_name . ' - ' . ($item->verifier_name ?? '未知'),
                'created_at' => $item->verified_at,
            ]);

        // 最近公告（5筆）
        $recentAnnouncements = $this->announcementRepo->getRecentActiveAnnouncements($companyId, 5)
            ->map(fn($item) => [
                'id' => $item->id,
                'type' => 'announcement',
                'title' => $item->title,
                'subtitle' => mb_substr(strip_tags($item->content), 0, 50) . '...',
                'created_at' => $item->created_at,
            ]);

        // 合併並排序
        $allActivities = collect()
            ->merge($recentEarthData)
            ->merge($recentVerifies)
            ->merge($recentAnnouncements)
            ->sortByDesc('created_at')
            ->take(10)
            ->values()
            ->all();

        return $allActivities;
    }
}
