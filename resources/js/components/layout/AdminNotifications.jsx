import { useEffect, useState } from 'react';
import { useShellProps } from '@/context/ShellPropsContext';
import { BellIcon } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { ScrollArea } from '@/components/ui/scroll-area';

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

export function AdminNotifications() {
    const { adminActivity, auth } = useShellProps();
    const [unreadCount, setUnreadCount] = useState(adminActivity?.unreadCount ?? 0);
    const [activities, setActivities] = useState(adminActivity?.activities ?? []);

    useEffect(() => {
        setUnreadCount(adminActivity?.unreadCount ?? 0);
        setActivities(adminActivity?.activities ?? []);
    }, [adminActivity]);

    useEffect(() => {
        const recentUrl = adminActivity?.urls?.recent;

        if (!recentUrl) {
            return undefined;
        }

        const interval = setInterval(async () => {
            try {
                const response = await fetch(recentUrl, {
                    headers: { Accept: 'application/json' },
                });

                if (!response.ok) {
                    return;
                }

                const data = await response.json();
                setUnreadCount(data.unread_count ?? 0);

                if (data.activities) {
                    setActivities(data.activities);
                }
            } catch {
                // ignore polling errors
            }
        }, 60000);

        return () => clearInterval(interval);
    }, [adminActivity?.urls?.recent]);

    if (!auth?.user || !adminActivity?.urls) {
        return null;
    }

    async function markAllRead() {
        try {
            const response = await fetch(adminActivity.urls.markSeen, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken(),
                    Accept: 'application/json',
                },
            });

            if (response.ok) {
                setUnreadCount(0);
                setActivities((prev) =>
                    prev.map((activity) => ({ ...activity, is_unread: false })),
                );
            }
        } catch {
            // ignore polling errors
        }
    }

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant="ghost" size="icon" className="relative">
                    <BellIcon className="size-5" />
                    {unreadCount > 0 && (
                        <Badge
                            variant="destructive"
                            className="absolute -top-1 -right-1 flex size-5 items-center justify-center rounded-full p-0 text-[10px]"
                        >
                            {unreadCount > 99 ? '99+' : unreadCount}
                        </Badge>
                    )}
                    <span className="sr-only">Patron notifications</span>
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="w-80">
                <DropdownMenuLabel className="flex items-center justify-between">
                    <span>Notifications</span>
                    <Button
                        variant="link"
                        size="sm"
                        className="h-auto p-0 text-xs"
                        onClick={(event) => {
                            event.preventDefault();
                            markAllRead();
                        }}
                    >
                        Mark all read
                    </Button>
                </DropdownMenuLabel>
                <DropdownMenuSeparator />
                <ScrollArea className="max-h-72">
                    {activities.length === 0 ? (
                        <p className="px-2 py-4 text-sm text-muted-foreground">
                            No patron notifications yet.
                        </p>
                    ) : (
                        activities.map((activity) => (
                            <DropdownMenuItem key={activity.id} asChild>
                                <a
                                    href={activity.action_url || '/admin/activities'}
                                    className={`flex flex-col items-start gap-0.5 ${
                                        activity.is_unread ? 'bg-muted/50' : ''
                                    }`}
                                >
                                    <span className="text-sm font-medium">{activity.title}</span>
                                    {activity.body && (
                                        <span className="text-xs text-muted-foreground line-clamp-2">
                                            {activity.body}
                                        </span>
                                    )}
                                    {activity.created_at && (
                                        <span className="text-xs text-muted-foreground">
                                            {activity.created_at}
                                        </span>
                                    )}
                                </a>
                            </DropdownMenuItem>
                        ))
                    )}
                </ScrollArea>
                <DropdownMenuSeparator />
                <DropdownMenuItem asChild>
                    <a href="/admin/activities" className="text-xs">
                        View activity log →
                    </a>
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
