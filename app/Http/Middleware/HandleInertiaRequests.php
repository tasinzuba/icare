<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                // SECURITY (C5): expose only a non-sensitive whitelist, never the raw model.
                // Even with User::$hidden set, this avoids shipping usage counters / ban / audit
                // columns to the browser on every Inertia response.
                'user' => $user?->only([
                    'id',
                    'name',
                    'email',
                    'avatar_url',
                    'is_admin',
                    'role_id',
                    'student_type',
                    'branch_id',
                    'onboarding_completed',
                ]),
            ],
            'dashboardNav' => fn () => $user ? $this->getDashboardNavData($request, $user) : null,
        ];
    }

    private function getDashboardNavData(Request $request, $user): ?array
    {
        try {
            $settings = \App\Models\WebsiteSetting::getSettings();
            
            $safeRoute = function(string $name, $params = []) {
                try { return route($name, $params); } catch (\Exception $e) { return '#'; }
            };

            return [
                'settings' => [
                    'site_title' => $settings->site_title ?? 'CD IELTS',
                    'site_name' => $settings->site_name ?? 'CD IELTS',
                    'logo_url' => $settings->logo_url ?? null,
                    'copyright_text' => $settings->copyright_text ?? ('© ' . date('Y') . ' CD IELTS. All rights reserved.'),
                ],
                'unreadNotificationsCount' => $user->unreadNotifications()->count(),
                'notifications' => $user->unreadNotifications()->take(5)->get()->map(fn ($n) => [
                    'id' => $n->id,
                    'title' => $n->data['title'] ?? 'Notification',
                    'created_at' => $n->created_at->diffForHumans(),
                    'url' => $safeRoute('notifications.show', $n->id),
                ]),
                'isOfflineStudent' => method_exists($user, 'isOfflineStudent') ? $user->isOfflineStudent() : false,
                'avatarUrl' => $user->avatar_url ?? null,
                'routes' => [
                    'dashboard' => $safeRoute('student.dashboard'),
                    'listening' => $safeRoute('student.listening.index'),
                    'reading' => $safeRoute('student.reading.index'),
                    'writing' => $safeRoute('student.writing.index'),
                    'speaking' => $safeRoute('student.speaking.index'),
                    'fullTest' => $safeRoute('student.full-test.index'),
                    'results' => $safeRoute('student.results'),
                    'writingPracticeTask1' => $safeRoute('student.writing-practice.task1'),
                    'writingPracticeTask2' => $safeRoute('student.writing-practice.task2'),
                    'aiTutor' => $safeRoute('student.ai-tutor.index'),
                    'profile' => $safeRoute('profile.edit'),
                    'notifications' => $safeRoute('notifications.index'),
                    'logout' => $safeRoute('logout'),
                ],
                'currentPath' => $request->path(),
            ];
        } catch (\Exception $e) {
            \Log::error('DashboardNav error: ' . $e->getMessage());
            return null;
        }
    }
}
