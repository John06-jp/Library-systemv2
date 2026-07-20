import { router } from '@inertiajs/react';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

const DEFAULT_OPTIONS = [10, 25, 50, 100, 250, 500];

export function PerPageSelect({
    perPage,
    options = DEFAULT_OPTIONS,
    className,
    pageParam = 'page',
}) {
    const current = Number(perPage) || options[0];

    const onChange = (value) => {
        const params = new URLSearchParams(window.location.search);
        params.set('per_page', value);
        params.delete(pageParam);

        router.get(
            `${window.location.pathname}?${params.toString()}`,
            {},
            { preserveScroll: true, preserveState: true },
        );
    };

    return (
        <div className={`flex flex-wrap items-center gap-2 ${className ?? ''}`}>
            <span className="text-muted-foreground text-sm">Show</span>
            <Select value={String(current)} onValueChange={onChange}>
                <SelectTrigger className="h-8 w-[5.5rem]" size="sm">
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    {options.map((option) => (
                        <SelectItem key={option} value={String(option)}>
                            {option}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
            <span className="text-muted-foreground text-sm">results per page</span>
        </div>
    );
}
