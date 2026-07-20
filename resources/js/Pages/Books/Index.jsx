import { Head, usePage } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Alert, AlertDescription } from '@/components/ui/alert';
import {
    CatalogEmptyState,
    CatalogFilterSidebar,
    CatalogResultsSummary,
} from '@/Pages/Books/components/CatalogSidebar';
import { CatalogResultsTable } from '@/Pages/Books/components/CatalogResultsTable';

export default function BooksIndex({ books, programs, filters, hasActiveQuery }) {
    const { flash } = usePage().props;

    return (
        <AdminLayout>
            <Head title="Catalog" />

            <div className="space-y-4">
                {flash?.success ? (
                    <Alert className="border-primary/30 bg-primary/5 text-foreground">
                        <AlertDescription>{flash.success}</AlertDescription>
                    </Alert>
                ) : null}
                {flash?.error ? (
                    <Alert variant="destructive">
                        <AlertDescription>{flash.error}</AlertDescription>
                    </Alert>
                ) : null}

                <div className="grid grid-cols-1 items-start gap-4 lg:grid-cols-[minmax(240px,280px)_1fr]">
                    <CatalogFilterSidebar
                        programs={programs}
                        filters={filters}
                        hasActiveQuery={hasActiveQuery}
                    />

                    <div className="min-w-0 space-y-4">
                        {hasActiveQuery ? (
                            <>
                                <CatalogResultsSummary books={books} filters={filters} />
                                <CatalogResultsTable books={books} perPage={filters.per_page} />
                            </>
                        ) : (
                            <CatalogEmptyState />
                        )}
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
