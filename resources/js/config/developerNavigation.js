import { isNavGroupActive, isNavItemActive } from '@/config/adminNavigation';

/**
 * Developer portal sidebar navigation.
 */
export const developerNavigation = [
    {
        label: 'Dashboard',
        href: '/developer/dashboard',
        routeName: 'developer.dashboard',
        icon: 'LayoutDashboard',
    },
    {
        label: 'Branding Settings',
        href: '/developer/branding',
        routeName: 'developer.branding.edit',
        icon: 'Palette',
    },
    {
        label: 'Activity Log',
        href: '/developer/branding/activity',
        routeName: 'developer.branding.activity',
        icon: 'Activity',
    },
    {
        label: 'Version History',
        href: '/developer/branding/versions',
        routeName: 'developer.branding.versions',
        icon: 'History',
    },
];

function findTrail(items, routeName, parents = []) {
    for (const item of items) {
        if (item.routeName === routeName) {
            return [...parents, item];
        }

        if (item.routePrefix && routeName?.startsWith(item.routePrefix)) {
            return [...parents, item];
        }

        if (item.children) {
            const trail = findTrail(item.children, routeName, [...parents, item]);

            if (trail) {
                return trail;
            }
        }
    }

    return null;
}

export function resolveDeveloperBreadcrumbs(routeName, override) {
    if (override?.length) {
        return override;
    }

    if (!routeName) {
        return [{ label: 'Dashboard', href: '/developer/dashboard', isCurrent: true }];
    }

    const trail = findTrail(developerNavigation, routeName);

    if (!trail) {
        return [{ label: 'Dashboard', href: '/developer/dashboard', isCurrent: true }];
    }

    return trail.map((item, index) => {
        const isLast = index === trail.length - 1;
        const href =
            item.href ??
            item.children?.find((child) => child.href)?.href ??
            null;

        return {
            label: item.label,
            href: isLast ? null : href,
            isCurrent: isLast,
        };
    });
}

export { isNavGroupActive, isNavItemActive };
