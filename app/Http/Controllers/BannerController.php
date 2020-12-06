<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index()
    {
        $banners = Banner::all();

        return view('banners.list', ['banners'=>$banners]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View|Response
     */
    public function create()
    {
        return view('banners.new');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Application|RedirectResponse|Response|Redirector
     */
    public function store(Request $request)
    {
        $res = $request->validate([
            'title'=>'required|max:256',
            'url'=>'nullable|max:256|url',
            'expiration'=>'required|date|after:'.now()->toDateString(),
            'bodycolor'=>'required|max:32',
            'textcolor'=>'required|max:32'
        ]);
        $banner = new Banner();
        $banner->user_id = auth()->user()->id;
        $banner->title = $res['title'];
        $banner->url = $res['url'];
        $banner->expiration = $res['expiration'];
        $banner->bcolor = $res['bodycolor'];
        $banner->tcolor = $res['textcolor'];
        $banner->save();
        $banner->users()->sync(User::all());
        return redirect(url('/admin'))->with(['success'=>'Banner created']);
    }

    /**
     * Display the specified resource.
     *
     * @param Banner $banner
     * @return Response
     */
    public function show(Banner $banner)
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Banner $banner
     * @return Application|Factory|View|Response
     */
    public function edit(Banner $banner)
    {
        return view('banners.new', ['banner'=>$banner]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Banner $banner
     * @return Application|RedirectResponse|Response|Redirector
     */
    public function update(Request $request, Banner $banner)
    {
        $res = $request->validate([
            'title'=>'required|max:256',
            'url'=>'nullable|max:256|url',
            'expiration'=>'required|date|after:'.now()->toDateString(),
            'bodycolor'=>'required|max:32',
            'textcolor'=>'required|max:32'
        ]);
        $banner->title = $res['title'];
        $banner->url = $res['url'];
        $banner->expiration = $res['expiration'];
        $banner->bcolor = $res['bodycolor'];
        $banner->tcolor = $res['textcolor'];
        $banner->save();
        return redirect(url('/banners'))->with(['success'=>'Banner edited']);
    }
    /**
     * Detatches the specified resource from a specific user.
     *
     * @param Request $request
     * @param Banner $banner
     * @return Response
     */
    public function desync(Banner $banner)
    {
        $banner->users()->detach(auth()->user());

        return \response(null,200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Banner $banner
     * @return Application|RedirectResponse|Response|Redirector
     */
    public function destroy(Banner $banner)
    {
        $banner->delete();
        return redirect(url('/banners'))->with(['success'=>'Banner deleted']);
    }
}
