<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use App\Models\TopPage;
use App\Models\TopPageDetail;
use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TopPageController extends BaseController
{
    public function store(Request $request)
    {

        $user = auth()->user();
        $existingTopPage = $user->topPage();

        if ($existingTopPage) {
            return response()->json(['message' => 'User already has a Top Page.'], 400);
        }

        $request->validate([
            'organization' => 'required|max:255',
            'district' => 'required|max:255',
            'province' => 'required|max:255',
            'overview' => 'required',
            'official_website' => 'nullable|url',
            'status' => 'required|in:active,inactive',
            'image_id' => 'nullable|integer|exists:uploads,id',
            'video_id' => 'nullable|integer|exists:uploads,id',
            'cover_image_id' => 'nullable|integer|exists:uploads,id',
            'fb_link' => 'nullable|url',
            'insta_link' => 'nullable|url',
        ]);

        $top_page = new TopPage();

        $user_id = Auth::id();
        $area = [
            'district' => $request->district,
            'province' => $request->province
        ];
        $languages = config('app.language_array');

        if ($request->upload_ids) {
            $top_page->upload_id = json_encode($request->upload_ids);
            deleteImage($request->upload_ids);
        }
        $top_page->user_id = $user_id;
        $top_page->organization = $request->organization;
        $top_page->overview = $request->overview;
        $top_page->area = $area;
        $top_page->about = $request->about;
        $top_page->summary = $request->summary;
        $top_page->cover_image = $request->input('image_id') ?: null;
        $top_page->profile_image = $request->input('cover_image_id') ?: null;
        $top_page->intro_video = $request->input('video_id') ?: null;
        $top_page->official_website = $request->official_website;
        $top_page->fb_link = $request->fb_link;
        $top_page->insta_link = $request->insta_link;
        $top_page->status = $request->status;
        $top_page->save();
        foreach ($languages as $language) {
            $top_page_detail = new TopPageDetail();
            $top_page_detail->organization = translate($language, $request->organization);
            $top_page_detail->area = translate($language, $area);
            $top_page_detail->overview = translate($language, $request->overview);
            $top_page_detail->about = translate($language, $request->about);
            $top_page_detail->summary = translate($language, $request->summary);
            $top_page_detail->top_page_id = $top_page->id;
            $top_page_detail->lang = $language;
            $top_page_detail->save();
        }

        return $this->handleResponseSuccess($top_page, 'Top page created successfully');
    }

    public function show(Request $request, TopPage $top_page)
    {
        $language = $request->language;

        if ($language) {
            $top_page->top_page_detail = $top_page->topPageDetail()->where('language', $language)->get();
        }
        $upload_ids = json_decode($top_page->upload_id, true);
        if ($upload_ids) {
            $top_page->uploads = Upload::whereIn('id', $upload_ids)->get();
        }
        return $this->handleResponseSuccess($top_page, 'top page data details');
    }

    public function update(Request $request, TopPage $top_page)
    {
        $request->validate([
            'organization' => 'required|max:255',
            'district' => 'required|max:255',
            'province' => 'required|max:255',
            'overview' => 'required',
            'official_website' => 'nullable|url',
            'status' => 'required|in:active,inactive',
            'image_id' => 'nullable|integer|exists:uploads,id',
            'video_id' => 'nullable|integer|exists:uploads,id',
            'cover_image_id' => 'nullable|integer|exists:uploads,id',
            'fb_link' => 'nullable|url',
            'insta_link' => 'nullable|url',
        ]);

        $area = [
            'district' => $request->district,
            'province' => $request->province
        ];
        $languages = config('app.language_array');
        $top_page->organization = $request->organization;
        $top_page->overview = $request->overview;
        $top_page->area = $area;
        $top_page->about = $request->about;
        $top_page->summary = $request->summary;
        $top_page->official_website = $request->official_website;
        $top_page->cover_image = $request->input('image_id') ?: null;
        $top_page->profile_image = $request->input('cover_image_id') ?: null;
        $top_page->intro_video = $request->input('video_id') ?: null;
        $top_page->fb_link = $request->fb_link;
        $top_page->insta_link = $request->insta_link;
        $top_page->status = $request->status;
        $top_page->save();
        $top_page->topPageDetail()->delete();
        foreach ($languages as $language) {
            $top_page_detail = new TopPageDetail();
            $top_page_detail->organization = translate($language, $request->organization);
            $top_page_detail->area = translate($language, $area);
            $top_page_detail->overview = translate($language, $request->overview);
            $top_page_detail->about = translate($language, $request->about);
            $top_page_detail->summary = translate($language, $request->summary);
            $top_page_detail->top_page_id = $top_page->id;
            $top_page_detail->lang = $language;
            $top_page_detail->save();
        }

        return $this->handleResponseSuccess($top_page, 'Top page updated successfully');
    }

    public function updateDetails(Request $request, TopPage $top_page)
    {
        $request->validate([
            'organization' => 'required|max:255',
            'district' => 'required|max:255',
            'province' => 'required|max:255',
            'overview' => 'required',
            'official_website' => 'nullable|url',
        ]);

        $area = [
            'district' => $request->district,
            'province' => $request->province
        ];
        $language = $request->language;

        $top_page_detail = $top_page->topPageDetail()->where('lang', $language)->first();
        $top_page_detail->organization = translate($language, $request->organization);
        $top_page_detail->area = translate($language, $area);
        $top_page_detail->overview = translate($language, $request->overview);
        $top_page_detail->about = translate($language, $request->about);
        $top_page_detail->summary = translate($language, $request->summary);
        $top_page_detail->top_page_id = $top_page->id;
        $top_page_detail->lang = $language;
        $top_page_detail->save();

        return $this->handleResponseSuccess($top_page_detail, 'Top page detail updated successfully');
    }
}
