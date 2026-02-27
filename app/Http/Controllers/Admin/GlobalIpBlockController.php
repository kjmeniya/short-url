<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IpBlock;
use Illuminate\Http\Request;

class GlobalIpBlockController extends Controller
{
    public function index()
    {
        $blocks = IpBlock::whereNull('short_url_id')->latest()->paginate(20, ['*'], 'blocks_page');
        $logs = \App\Models\BlockedLog::with('shortUrl')->latest()->paginate(20, ['*'], 'logs_page');
        return view('admin.global-ip-blocks.index', compact('blocks', 'logs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type'  => 'required|in:ip,cidr',
            'value' => 'required|string|max:255',
        ]);

        IpBlock::create($data);

        return redirect()->route('admin.global-ip-blocks.index')
            ->with('success', 'Global IP Block added successfully.');
    }

    public function destroy(IpBlock $globalIpBlock)
    {
        if ($globalIpBlock->short_url_id === null) {
            $globalIpBlock->delete();
            return redirect()->route('admin.global-ip-blocks.index')
                ->with('success', 'Global IP Block removed.');
        }

        return redirect()->route('admin.global-ip-blocks.index')
            ->with('error', 'Cannot remove non-global block from here.');
    }
}
