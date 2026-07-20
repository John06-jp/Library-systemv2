import { Head } from '@inertiajs/react';
import { Button } from '@/components/ui/button';

const swatches = [
    { name: 'USM Blue', token: '--usm-blue', role: 'Primary actions' },
    { name: 'USM Green', token: '--usm-green', role: 'Secondary / success' },
    { name: 'USM Gold', token: '--usm-gold', role: 'Accent highlights' },
    { name: 'USM Maroon', token: '--usm-maroon', role: 'Destructive emphasis' },
];

export default function DesignSystem({ branding }) {
    return (
        <>
            <Head title="USM Design System" />

            <div className="min-h-screen bg-muted/40">
                <header className="bg-primary text-primary-foreground">
                    <div className="mx-auto flex max-w-5xl flex-col gap-2 px-6 py-8">
                        <p className="text-sm font-medium uppercase tracking-wider text-primary-foreground/80">
                            University of Southern Mindanao
                        </p>
                        <h1 className="text-3xl font-semibold">PANTAS shadcn Theme</h1>
                        <p className="max-w-2xl text-primary-foreground/90">
                            Aligned with{' '}
                            <a
                                href="https://www.usm.edu.ph/"
                                className="underline underline-offset-4"
                                target="_blank"
                                rel="noreferrer"
                            >
                                usm.edu.ph
                            </a>{' '}
                            and <code className="rounded bg-primary-foreground/10 px-1">public/branding/branding.css</code>.
                        </p>
                    </div>
                </header>

                <main className="mx-auto max-w-5xl space-y-8 px-6 py-8">
                    <section className="rounded-xl border bg-card p-6 shadow-sm">
                        <h2 className="mb-4 text-lg font-semibold">Brand swatches</h2>
                        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            {swatches.map((swatch) => (
                                <div key={swatch.token} className="overflow-hidden rounded-lg border">
                                    <div
                                        className="h-20"
                                        style={{ backgroundColor: `var(${swatch.token})` }}
                                    />
                                    <div className="space-y-1 p-3">
                                        <p className="font-medium">{swatch.name}</p>
                                        <p className="text-xs text-muted-foreground">{swatch.role}</p>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </section>

                    <section className="rounded-xl border bg-card p-6 shadow-sm">
                        <h2 className="mb-4 text-lg font-semibold">Button variants</h2>
                        <div className="flex flex-wrap gap-3">
                            <Button>Primary (USM Blue)</Button>
                            <Button variant="secondary">Secondary (USM Green)</Button>
                            <Button variant="outline">Outline</Button>
                            <Button variant="ghost">Ghost</Button>
                            <Button variant="destructive">Destructive</Button>
                            <Button variant="link">Link</Button>
                        </div>
                    </section>

                    <section className="rounded-xl border bg-card p-6 shadow-sm">
                        <h2 className="mb-2 text-lg font-semibold">Shared branding config</h2>
                        <pre className="overflow-x-auto rounded-lg bg-muted p-4 text-xs">
                            {JSON.stringify(branding, null, 2)}
                        </pre>
                    </section>
                </main>

                <footer className="mt-8 bg-primary py-4 text-center text-sm text-primary-foreground">
                    Pantas © {new Date().getFullYear()} · USM Library
                </footer>
            </div>
        </>
    );
}
