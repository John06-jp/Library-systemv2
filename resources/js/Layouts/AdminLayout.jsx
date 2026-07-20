import { usePage } from '@inertiajs/react';
import { ShellPropsProvider } from '@/context/ShellPropsContext';
import { AdminShellLayout } from '@/Layouts/AdminShellLayout';

export default function AdminLayout({ children, breadcrumbs: breadcrumbOverride }) {
    const { auth, routeName, adminActivity, shellBranding } = usePage().props;

    return (
        <ShellPropsProvider value={{ auth, routeName, adminActivity, shellBranding }}>
            <AdminShellLayout routeName={routeName} breadcrumbOverride={breadcrumbOverride}>
                {children}
            </AdminShellLayout>
        </ShellPropsProvider>
    );
}
