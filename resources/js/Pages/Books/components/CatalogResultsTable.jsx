import { Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { PerPageSelect } from '@/components/PerPageSelect';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { MoreHorizontal } from 'lucide-react';

function copiesStaffUrl(book) {
    const params = new URLSearchParams({
        title: book.title_statement ?? '',
        author: book.main_author ?? '',
        year: book.pub_year ?? '',
    });

    return `/staff/books/copies?${params.toString()}`;
}

function BookRowActions({ book, onDeleteRequest }) {
    const isSingleCopy = book.copies === 1;

    if (!isSingleCopy) {
        return (
            <Button asChild size="sm" variant="outline">
                <a href={copiesStaffUrl(book)}>View copies</a>
            </Button>
        );
    }

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant="outline" size="sm" className="gap-1">
                    Actions
                    <MoreHorizontal className="size-4 opacity-60" />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="w-44">
                <DropdownMenuItem asChild>
                    <a href={`/book/${book.sample_id}`}>View</a>
                </DropdownMenuItem>
                <DropdownMenuItem asChild>
                    <a href={`/book/${book.sample_id}/edit`}>Edit</a>
                </DropdownMenuItem>
                <DropdownMenuItem
                    onSelect={() => router.post(`/books/${book.sample_id}/archive`)}
                >
                    Archive
                </DropdownMenuItem>
                <DropdownMenuSeparator />
                <DropdownMenuItem
                    className="text-destructive focus:text-destructive"
                    onSelect={() => onDeleteRequest(book)}
                >
                    Delete
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}

function AvailabilityBadge({ availability }) {
    if (!availability) {
        return <span className="text-muted-foreground">—</span>;
    }

    const isAvailable = availability === 'Available';

    return (
        <Badge variant={isAvailable ? 'default' : 'destructive'} className="font-normal">
            {availability}
        </Badge>
    );
}

export function CatalogResultsTable({ books, perPage }) {
    const [deleteTarget, setDeleteTarget] = useState(null);

    function confirmDelete() {
        if (!deleteTarget) {
            return;
        }

        router.delete(`/books/${deleteTarget.sample_id}`, {
            preserveScroll: true,
            onFinish: () => setDeleteTarget(null),
        });
    }

    return (
        <>
            <Card className="overflow-hidden shadow-sm">
                <CardContent className="p-0">
                    <div className="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow className="bg-muted/50 hover:bg-muted/50">
                                    <TableHead>Title</TableHead>
                                    <TableHead>Author</TableHead>
                                    <TableHead>Year</TableHead>
                                    <TableHead>Resource type</TableHead>
                                    <TableHead className="text-center">Copies</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead className="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {books.data.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={7}
                                            className="py-10 text-center text-muted-foreground"
                                        >
                                            No books match your search or filters.
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    books.data.map((book) => (
                                        <TableRow key={`${book.title_statement}-${book.sample_id}`}>
                                            <TableCell className="max-w-[220px] font-medium">
                                                <span className="line-clamp-2">
                                                    {book.title_statement}
                                                </span>
                                            </TableCell>
                                            <TableCell className="max-w-[160px]">
                                                <span className="line-clamp-2">
                                                    {book.main_author}
                                                </span>
                                            </TableCell>
                                            <TableCell>{book.pub_year ?? '—'}</TableCell>
                                            <TableCell>{book.content_type ?? '—'}</TableCell>
                                            <TableCell className="text-center tabular-nums">
                                                {book.copies}
                                            </TableCell>
                                            <TableCell>
                                                <AvailabilityBadge
                                                    availability={book.availability}
                                                />
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <BookRowActions
                                                    book={book}
                                                    onDeleteRequest={setDeleteTarget}
                                                />
                                            </TableCell>
                                        </TableRow>
                                    ))
                                )}
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>

            <div className="mt-4 flex flex-wrap items-center justify-between gap-3">
                <PerPageSelect perPage={perPage} />

                {books.links?.length > 3 ? (
                    <nav
                        className="flex flex-wrap items-center justify-end gap-1"
                        aria-label="Catalog pagination"
                    >
                        {books.links.map((link, index) =>
                            link.url ? (
                                <Button
                                    key={`${link.label}-${index}`}
                                    asChild
                                    size="sm"
                                    variant={link.active ? 'default' : 'outline'}
                                >
                                    <Link
                                        href={link.url}
                                        preserveScroll
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    />
                                </Button>
                            ) : (
                                <Button
                                    key={`${link.label}-${index}`}
                                    size="sm"
                                    variant="outline"
                                    disabled
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ),
                        )}
                    </nav>
                ) : null}
            </div>

            <Dialog open={Boolean(deleteTarget)} onOpenChange={() => setDeleteTarget(null)}>
                <DialogContent showCloseButton={false}>
                    <DialogHeader>
                        <DialogTitle>Delete this book?</DialogTitle>
                        <DialogDescription>
                            This moves{' '}
                            <strong>{deleteTarget?.title_statement}</strong> to trash. You can
                            restore it later from the trash page.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setDeleteTarget(null)}>
                            Cancel
                        </Button>
                        <Button variant="destructive" onClick={confirmDelete}>
                            Delete
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}
