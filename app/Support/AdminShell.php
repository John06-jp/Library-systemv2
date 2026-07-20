<?php

namespace App\Support;

use App\Models\AdminActivity;
use App\Models\User;
use App\Services\BrandingService;
use Illuminate\Http\Request;

class AdminShell
{
    /**
     * @return array<string, mixed>|null
     */
    public static function authUser(?User $user): ?array
    {
        if (! $user) {
            return null;
        }

        return [
            'id' => $user->id,
            'name' => $user->fullName(),
            'email' => $user->email,
            'role' => $user->role,
            'isAdmin' => $user->role === 'admin',
            'isDeveloper' => $user->role === 'developer',
            'initials' => $user->initials(),
            'avatarUrl' => $user->profilePictureUrl(),
        ];
    }

    /**
     * Branding snapshot for the developer portal sidebar.
     *
     * @return array<string, string>
     */
    public static function shellBranding(): array
    {
        /** @var BrandingService $service */
        $service = app(BrandingService::class);
        $active = $service->active();

        return [
            'sidebar_logo_url' => $service->assetUrl('sidebar_logo_path'),
            'sidebar_brand_name' => (string) ($active['sidebar_brand_name'] ?? 'Library System'),
            'sidebar_brand_subtitle' => (string) ($active['sidebar_brand_subtitle'] ?? ''),
            'sidebar_background_color' => (string) ($active['sidebar_background_color'] ?? '#1E293B'),
            'sidebar_text_color' => (string) ($active['sidebar_text_color'] ?? '#CBD5E1'),
            'sidebar_brand_text_color' => (string) ($active['sidebar_brand_text_color'] ?? '#FFFFFF'),
            'sidebar_active_color' => (string) ($active['sidebar_active_color'] ?? '#3B82F6'),
            'sidebar_hover_background_color' => (string) ($active['sidebar_hover_background_color'] ?? '#334155'),
            'sidebar_hover_text_color' => (string) ($active['sidebar_hover_text_color'] ?? '#F8FAFC'),
            'sidebar_footer_background_color' => (string) ($active['sidebar_footer_background_color'] ?? '#1E293B'),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function adminActivity(?User $user): ?array
    {
        if (! $user || ! in_array($user->role, ['admin', 'staff'], true)) {
            return null;
        }

        $since = $user->notification_last_seen_at;

        $activities = AdminActivity::query()
            ->patronNotifications()
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn (AdminActivity $activity) => [
                'id' => $activity->id,
                'title' => $activity->title,
                'body' => $activity->body,
                'action_url' => $activity->action_url,
                'created_at' => $activity->created_at?->timezone('Asia/Manila')->diffForHumans(),
                'is_unread' => ! $since || $activity->created_at->gt($since),
            ])
            ->values()
            ->all();

        $unreadCount = AdminActivity::query()
            ->patronNotifications()
            ->when($since, fn ($q) => $q->where('created_at', '>', $since))
            ->count();

        return [
            'unreadCount' => $unreadCount,
            'activities' => $activities,
            'urls' => [
                'markSeen' => route('admin.activities.mark_seen'),
                'recent' => route('admin.activities.recent'),
            ],
        ];
    }

    /**
     * Props consumed by the React admin shell on Blade pages.
     *
     * @return array<string, mixed>
     */
    public static function pageProps(Request $request): array
    {
        $user = $request->user();

        return [
            'auth' => [
                'user' => self::authUser($user),
            ],
            'routeName' => $request->route()?->getName(),
            'adminActivity' => self::adminActivity($user),
            'shellBranding' => self::shellBranding(),
        ];
    }
}
