import { AppBreadcrumb } from '@/components/layout/AppBreadcrumb';
import { AppHeader } from '@/components/layout/AppHeader';
import { AppSidebar } from '@/components/layout/AppSidebar';
import { resolveBreadcrumbs } from '@/config/adminNavigation';
import { resolveDeveloperBreadcrumbs } from '@/config/developerNavigation';
import { useShellProps } from '@/context/ShellPropsContext';
import { SidebarInset, SidebarProvider } from '@/components/ui/sidebar';
import { TooltipProvider } from '@/components/ui/tooltip';

export function AdminShellLayout({
    routeName,
    breadcrumbOverride,
    contentRef,
    children,
}) {
    const { auth, shellBranding } = useShellProps();
    const isDeveloper = auth?.user?.isDeveloper ?? false;
    const breadcrumbs = isDeveloper
        ? resolveDeveloperBreadcrumbs(routeName, breadcrumbOverride)
        : resolveBreadcrumbs(routeName, breadcrumbOverride);
    const branding = shellBranding ?? {};
    const shellStyle = {
        '--primary': branding.primary_color,
        '--secondary': branding.secondary_color,
        '--accent': branding.accent_color,
        '--ring': branding.primary_color,
        '--border': branding.table_border_color,
        '--branding-button': branding.button_color,
        '--branding-table-header': branding.table_header_color,
        '--branding-table-header-text': branding.table_header_text_color,
        '--branding-table-border': branding.table_border_color,
        '--branding-table-hover': branding.table_hover_color,
        '--branding-banner-image': branding.banner_url
            ? `url("${branding.banner_url}")`
            : 'none',
    };

    return (
        <TooltipProvider>
            <SidebarProvider className="admin-shell min-w-0 w-full" style={shellStyle}>
                <AppSidebar />
                <SidebarInset>
                    <AppHeader />
                    <div
                        data-slot="admin-main"
                        className="flex flex-1 flex-col gap-3 p-3 sm:gap-4 sm:p-4 md:p-6"
                    >
                        <AppBreadcrumb items={breadcrumbs} />
                        {children}
                        {contentRef ? (
                            <div
                                ref={contentRef}
                                className="admin-blade-slot min-w-0 [&_.container]:max-w-full"
                            />
                        ) : null}
                    </div>
                </SidebarInset>
            </SidebarProvider>
        </TooltipProvider>
    );
}
