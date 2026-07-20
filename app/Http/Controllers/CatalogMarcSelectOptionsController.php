<?php

namespace App\Http\Controllers;

use App\Models\MarcField;
use App\Models\AdminActivity;
use App\Services\AdminActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogMarcSelectOptionsController extends Controller
{
    public function index(): View
    {
        $sections = [];

        foreach (config('catalog.extensible_select_marc', []) as $def) {
            $marc = MarcField::findForTagSubfield($def['tag'], $def['subfield'] ?? null);
            if (! $marc) {
                continue;
            }

            $saved = MarcField::normalizeOptionsArray($marc->options);
            $merged = $marc->mergedSelectOptions($def['book_column'] ?? null);
            $savedLower = array_map('mb_strtolower', $saved);
            $fromRecords = array_values(array_filter($merged, function ($opt) use ($savedLower) {
                return ! in_array(mb_strtolower($opt), $savedLower, true);
            }));

            $sections[] = [
                'def' => $def,
                'marc' => $marc,
                'saved' => $saved,
                'from_records' => $fromRecords,
            ];
        }

        return view('admin.catalog_select_options.index', compact('sections'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tag' => ['required', 'string', 'size:3', 'regex:/^[0-9]{3}$/'],
            'subfield' => ['nullable', 'string', 'size:1'],
            'option' => ['required', 'string', 'max:255'],
        ]);

        $subfield = filled($data['subfield'] ?? null) ? $data['subfield'] : null;

        if (! MarcField::isExtensibleSelect($data['tag'], $subfield)) {
            abort(403);
        }

        $marc = MarcField::findForTagSubfield($data['tag'], $subfield);

        if (! $marc || $marc->input_type !== 'select') {
            return back()->with('error', 'MARC select field not found. Run migrations or MarcFrameworkSeeder.');
        }

        $option = trim($data['option']);
        $options = MarcField::normalizeOptionsArray($marc->options);
        $exists = collect($options)->contains(fn ($o) => strcasecmp($o, $option) === 0);

        if (! $exists) {
            $options[] = $option;
            sort($options, SORT_NATURAL | SORT_FLAG_CASE);
            $marc->options = $options;
            $marc->save();
        }

        AdminActivityLogger::staff(
            AdminActivity::TYPE_SETTINGS,
            $exists ? 'Catalog option unchanged' : 'Catalog dropdown option added',
            "{$data['tag']}{$subfield}: {$option}",
            route('admin.catalog_select_options.index'),
            'book',
            $marc,
        );

        return redirect()
            ->route('admin.catalog_select_options.index', ['field' => $data['tag'] . ($subfield ?? '')])
            ->with('success', $exists ? 'That option already exists.' : "Added “{$option}”.");
    }

    public function destroy(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tag' => ['required', 'string', 'size:3', 'regex:/^[0-9]{3}$/'],
            'subfield' => ['nullable', 'string', 'size:1'],
            'option' => ['required', 'string', 'max:255'],
        ]);

        $subfield = filled($data['subfield'] ?? null) ? $data['subfield'] : null;

        if (! MarcField::isExtensibleSelect($data['tag'], $subfield)) {
            abort(403);
        }

        $marc = MarcField::findForTagSubfield($data['tag'], $subfield);

        if (! $marc) {
            return back()->with('error', 'MARC field not found.');
        }

        $remove = trim($data['option']);
        $options = array_values(array_filter(
            MarcField::normalizeOptionsArray($marc->options),
            fn ($o) => strcasecmp($o, $remove) !== 0
        ));

        $marc->options = $options ?: null;
        $marc->save();

        AdminActivityLogger::staff(
            AdminActivity::TYPE_SETTINGS,
            'Catalog dropdown option removed',
            "{$data['tag']}{$subfield}: {$remove}",
            route('admin.catalog_select_options.index'),
            'book',
            $marc,
        );

        return redirect()
            ->route('admin.catalog_select_options.index', ['field' => $data['tag'] . ($subfield ?? '')])
            ->with('success', "Removed “{$remove}” from the list. Existing books keep their stored value.");
    }
}
