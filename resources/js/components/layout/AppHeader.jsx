import { Separator } from '@/components/ui/separator';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { AdminNotifications } from '@/components/layout/AdminNotifications';
import { AppUserMenu } from '@/components/layout/AppUserMenu';

export function AppHeader() {
    return (
        <header
            data-slot="admin-header"
            className="relative z-30 flex h-14 shrink-0 items-center gap-2 border-b bg-background px-4"
        >
            <SidebarTrigger type="button" className="-ml-1" />
            <Separator orientation="vertical" className="mr-2 h-4" />
            <div data-slot="admin-header-actions" className="flex flex-1 items-center justify-end gap-2">
                <AdminNotifications />
                <AppUserMenu />
            </div>
        </header>
    );
}
