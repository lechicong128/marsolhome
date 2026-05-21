<?php

namespace App\Http\Controllers;

use App\Models\Website;
use App\Models\Language;
use App\Services\ServiceService;
use App\Traits\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Img;

class AdminWebsiteController extends Controller
{
    use UploadFile;
    protected $fnServiecs;
    public function __construct(Request $request, ServiceService $ServiceService)
    {
        parent::__construct($request);
        DB::enableQueryLog();
        $this->limit  = 4;
        $this->limit_section  = 5;
        $this->fnServiecs = $ServiceService;
    }

    public function homepage(){
        $title = lang('dt_homepage');
        $lang_serviecs = Language::get()->toArray();
        $dataWebsite = [];
        foreach($lang_serviecs as $key => $language) {
            $websiteData = Website::where('language', $language['code'])
                ->where('type', 'homepage')->first();
            $websiteData->content = json_decode($websiteData->content);
            $dataWebsite[$language['code']] = $websiteData->content;
        }

        return view('admin.admin_website.homepage',[
            'title' => $title,
            'homePage' => $dataWebsite ?? [],
            'lang_current' => $lang_serviecs ?? [],
        ]);
    }

    public function submit_homepage(){
        $data = $this->request->input();
        try {
            DB::beginTransaction();
            $allLang = $data['allLang'] ?? null;
            $section1 = $data['section1'] ?? null;
            $dataPost = [];
            if(!empty($section1)) {
                foreach($section1 as $lang => $value) {
                    $dataPost[$lang]['section1'] = $value;
                }
            }

            $section2 = $data['section2'] ?? null;
            if(!empty($section2)) {
                foreach ($section2 as $lang => $value) {
                    $dataPost[$lang]['section2'] = $value;
                }
            }
            $section3 = $data['section3'] ?? null;
            if(!empty($section3)) {
                foreach($section3 as $lang => $value) {
                    $dataPost[$lang]['section3'] = $value;
                }
            }
            if(!empty($section3['tab'])) {
                foreach ($section3['tab'] as $key => $tab) {
                    if (!empty($tab['img_before'])) {
                        foreach($allLang as $lang) {
                            $dataPost[$lang]['section3']['tab'][$key]['img'] = $tab['img_before'];
                        }
                    }
                }
            }
            unset($section3['tab']);
            if ($this->request->file('section3')) {
                if (!empty($this->request->file('section3')['tab'])) {
                    foreach($this->request->file('section3')['tab'] as $key => $isFile) {
                        if(!empty($isFile['img'])) {
                            $path = $this->UploadFile(
                                $isFile['img'],
                                'homepage/section3',
                                260,
                                390,
                                false
                            );
                            foreach($allLang as $lang) {
                                $dataPost[$lang]['section3']['tab'][$key]['img'] = $path;
                            }
                        }
                    }

                }
            }

            $section4 = $data['section4'] ?? null;
            if(!empty($section4)) {
                foreach($section4 as $lang => $value) {
                    $dataPost[$lang]['section4'] = $value;
                }
            }
            if(!empty($section4['tab'])) {
                foreach ($section4['tab'] as $key => $tab) {
                    if (!empty($tab['img_before'])) {
                        foreach($allLang as $lang) {
                            $dataPost[$lang]['section4']['tab'][$key]['img'] = $tab['img_before'];
                        }
                    }
                    if (!empty($tab['icon_before'])) {
                        foreach($allLang as $lang) {
                            $dataPost[$lang]['section4']['tab'][$key]['icon'] = $tab['icon_before'];
                        }
                    }
                }
            }
            unset($section4['tab']);

            if ($this->request->file('section4')) {
                foreach($this->request->file('section4')['tab'] as $key => $isFile) {
                    if(!empty($isFile['icon'])) {
                        $path = $this->UploadFile(
                            $isFile['icon'],
                            'homepage/section4',
                            260,
                            390,
                            false
                        );
                        foreach($allLang as $lang) {
                            $dataPost[$lang]['section4']['tab'][$key]['icon'] = $path;
                        }
                    }
                    if(!empty($isFile['img'])) {
                        $path = $this->UploadFile(
                            $isFile['img'],
                            'homepage/section4',
                            260,
                            390,
                            false
                        );
                        foreach($allLang as $lang) {
                            $dataPost[$lang]['section4']['tab'][$key]['img'] = $path;
                        }
                    }
                }
            }

            $section5 = $data['section5'] ?? null;
            if(!empty($section5)) {
                foreach($section5 as $lang => $value) {
                    $dataPost[$lang]['section5'] = $value;
                }
            }
            if(!empty($section5['tab'])) {
                foreach ($section5['tab'] as $key => $tab) {
                    if (!empty($tab['img_before'])) {
                        foreach($allLang as $lang) {
                            $dataPost[$lang]['section5']['tab'][$key]['img'] = $tab['img_before'];
                        }
                    }
                }
            }
            unset($section5['tab']);

            if ($this->request->file('section5')) {
                foreach($this->request->file('section5')['tab'] as $key => $isFile) {
                    if(!empty($isFile['img'])) {
                        $path = $this->UploadFile(
                            $isFile['img'],
                            'homepage/section5',
                            260,
                            390,
                            false
                        );
                        foreach($allLang as $lang) {
                            $dataPost[$lang]['section5']['tab'][$key]['img'] = $path;
                        }
                    }
                }
            }


            $section6 = $data['section6'] ?? null;
            if(!empty($section6)) {
                foreach($section6 as $lang => $value) {
                    $dataPost[$lang]['section6'] = $value;
                }
            }
            $section7 = $data['section7'] ?? null;
            if(!empty($section7)) {
                foreach($section7 as $lang => $value) {
                    $dataPost[$lang]['section7'] = $value;
                }
            }
            $section8 = $data['section8'] ?? null;
            if(!empty($section8)) {
                foreach($section8 as $lang => $value) {
                    $dataPost[$lang]['section8'] = $value;
                }
            }
            $section9 = $data['section9'] ?? null;
            if(!empty($section9)) {
                foreach($section9 as $lang => $value) {
                    $dataPost[$lang]['section9'] = $value;
                }
            }

            foreach($dataPost as $lang => $value) {
                if($lang == 'tab') continue;
                $websiteData = Website::where('language', $lang)
                    ->where('type', 'homepage')->first();
                if(!empty($websiteData)) {
                    $websiteData->content = json_encode($value);
                    $websiteData->save();
                } else {
                    $newWebsite = new Website();
                    $newWebsite->language = $lang;
                    $newWebsite->type = 'homepage';
                    $newWebsite->content = json_encode($value);
                    $newWebsite->save();
                }
            }
            DB::commit();
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);
        }
        catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }


    public function helpcentre(){
        $title = lang('c_help_centre');
        $lang_serviecs = Language::get()->toArray();
        $dataWebsite = [];
        foreach($lang_serviecs as $key => $language) {
            $websiteData = Website::where('language', $language['code'])
                ->where('type', 'helpcentre')->first();
            if(!empty($websiteData->id)) {
                $websiteData->content = !empty($websiteData->content) ? json_decode($websiteData->content) : null;
                $dataWebsite[$language['code']] = $websiteData->content;
            }
        }
//        dd($dataWebsite);
        return view('admin.admin_website.helpcentre',[
            'title' => $title,
            'helpcentre' => $dataWebsite ?? [],
            'lang_current' => $lang_serviecs ?? [],
        ]);
    }

    public function submit_helpcentre(){
        $data = $this->request->input();
        try {
            DB::beginTransaction();
            $allLang = $data['allLang'] ?? null;
            $dataPost = [];
            $messager = $this->request->input('messager');
            $zalo = $this->request->input('zalo');
            $hotline = $this->request->input('hotline');
            $image_before = $this->request->input('image_before');
            $link_goole_map = $this->request->input('link_goole_map');
            foreach($allLang as $key => $lang) {
                $dataLang = $this->request->input('section')[$lang] ? $this->request->input('section')[$lang] : null;
                foreach($dataLang as $k => $v) {
                    $dataPost[$lang][$k] = $v;
                }

                $dataPost[$lang]['messager']['link'] = $messager['link'];
                $dataPost[$lang]['zalo']['link'] = $zalo['link'];
                $dataPost[$lang]['hotline']['phone'] = $hotline['phone'];
                $dataPost[$lang]['image'] = $image_before;
                $dataPost[$lang]['link_goole_map'] = $link_goole_map;
            }
            if ($this->request->file('image')) {
                if (!empty($this->request->file('image'))) {
                    foreach($this->request->file('image') as $key => $isFile) {
                        if(!empty($isFile)) {
                            $path = $this->UploadFile(
                                $isFile,
                                'web/helpcentre',
                                260,
                                390,
                                false
                            );
                            foreach($allLang as $lang) {
                                $image = is_array($path) ? $path[0] : $path;
                                if(!empty($dataPost[$lang]['image'][$key])) {
                                    $this->deleteFile($dataPost[$lang]['image'][$key]);
                                }
                                $dataPost[$lang]['image'][$key] = $image;
                            }
                        }
                    }
                }
            }


            if(!empty($dataPost)) {
                foreach($dataPost as $lang => $value) {
                    $websiteData = Website::where('language', $lang)->where('type', 'helpcentre')->first();
                    if (!empty($websiteData)) {
                        $websiteData->content = json_encode($value);
                        $websiteData->save();
                    } else {
                        $newWebsite = new Website();
                        $newWebsite->language = $lang;
                        $newWebsite->type = 'helpcentre';
                        $newWebsite->content = json_encode($value);
                        $newWebsite->save();
                    }
                }
            }
            DB::commit();
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);

        }
        catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function feedback(){
        $title = lang('c_feedback');
        $lang_serviecs = Language::get()->toArray();
        $dataWebsite = [];
        foreach($lang_serviecs as $key => $language) {
            $websiteData = Website::where('language', $language['code'])
                ->where('type', 'feedback')->first();
            if(!empty($websiteData->id)) {
                $websiteData->content = !empty($websiteData->content) ? json_decode($websiteData->content) : null;
                $dataWebsite[$language['code']] = $websiteData->content;
            }
        }
        return view('admin.admin_website.feedback',[
            'title' => $title,
            'feedback' => $dataWebsite ?? [],
            'lang_current' => $lang_serviecs ?? [],
        ]);
    }

    public function submit_feedback(){
        $data = $this->request->input();
        try {
            DB::beginTransaction();
            $allLang = $data['allLang'] ?? null;
            $dataPost = [];
            $image_before = $this->request->input('image_before');
            foreach($allLang as $key => $lang) {
                $dataLang = $this->request->input('section')[$lang] ? $this->request->input('section')[$lang] : null;
                foreach($dataLang as $k => $v) {
                    $dataPost[$lang][$k] = $v;
                }
                $dataPost[$lang]['image'] = $image_before;
            }
            if ($this->request->file('image')) {
                if (!empty($this->request->file('image'))) {
                    $path = $this->UploadFile(
                        $this->request->file('image'),
                        'web/feedback',
                        260,
                        390,
                        false
                    );
                    foreach($allLang as $lang) {
                        $image = is_array($path) ? $path[0] : $path;
                        if(!empty($dataPost[$lang]['image'])) {
                            $this->deleteFile($dataPost[$lang]['image']);
                        }
                        $dataPost[$lang]['image'] = $image;
                    }
                }
            }



            if(!empty($dataPost)) {
                foreach($dataPost as $lang => $value) {
                    $websiteData = Website::where('language', $lang)->where('type', 'feedback')->first();
                    if (!empty($websiteData)) {
                        $websiteData->content = json_encode($value);
                        $websiteData->save();
                    } else {
                        $newWebsite = new Website();
                        $newWebsite->language = $lang;
                        $newWebsite->type = 'feedback';
                        $newWebsite->content = json_encode($value);
                        $newWebsite->save();
                    }
                }
            }
            DB::commit();
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);

        }
        catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }


    public function reviewhub(){
        $title = lang('c_reviewhub');
        $lang_serviecs = Language::get()->toArray();
        $dataWebsite = [];
        foreach($lang_serviecs as $key => $language) {
            $websiteData = Website::where('language', $language['code'])->where('type', 'reviewhub')->first();
            if (!empty($websiteData->id)) {
                $websiteData->content = !empty($websiteData->content) ? json_decode($websiteData->content) : null;
                $dataWebsite[$language['code']] = $websiteData->content;
            }
        }
        return view('admin.admin_website.reviewhub',[
            'title' => $title,
            'reviewhub' => $dataWebsite ?? [],
            'lang_current' => $lang_serviecs ?? [],
        ]);
    }

    public function submit_reviewhub(){
        $data = $this->request->input();
        try {
            DB::beginTransaction();
            $allLang = $data['allLang'] ?? null;
            $dataPost = [];
            $title = $this->request->input('title');
            $image_before = $this->request->input('image_before');

            foreach($allLang as $key => $lang) {
                $dataLang = $this->request->input('section')[$lang] ? $this->request->input('section')[$lang] : null;
                foreach($dataLang as $k => $v) {
                    $dataPost[$lang][$k] = $v;
                }
                $dataPost[$lang]['image'] = $image_before ?? NULL;
            }
            if ($this->request->file('image')) {
                if (!empty($this->request->file('image'))) {
                    $path = $this->UploadFile(
                        $this->request->file('image'),
                        'web/reviewhub',
                        260,
                        390,
                        false
                    );
                    foreach($allLang as $lang) {
                        $image = is_array($path) ? $path[0] : $path;
                        if(!empty($dataPost[$lang]['image'])) {
                            $this->deleteFile($dataPost[$lang]['image']);
                        }
                        $dataPost[$lang]['image'] = $image;
                    }
                }
            }
            if(!empty($dataPost)) {
                foreach($dataPost as $lang => $value) {
                    $websiteData = Website::where('language', $lang)->where('type', 'reviewhub')->first();
                    if (!empty($websiteData)) {
                        $websiteData->content = json_encode($value);
                        $websiteData->save();
                    } else {
                        $newWebsite = new Website();
                        $newWebsite->language = $lang;
                        $newWebsite->type = 'reviewhub';
                        $newWebsite->content = json_encode($value);
                        $newWebsite->save();
                    }
                }
            }
            DB::commit();
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);

        }
        catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }

    public function donations_and_charity(){
        $title = lang('donations_and_charity');
        $lang_serviecs = Language::get()->toArray();
        $dataWebsite = [];
        foreach($lang_serviecs as $key => $language) {
            $websiteData = Website::where('language', $language['code'])
                ->where('type', 'donations_and_charity')->first();
            if(!empty($websiteData->id)) {
                $websiteData->content = !empty($websiteData->content) ? json_decode($websiteData->content) : null;
                $dataWebsite[$language['code']] = $websiteData->content;
            }
        }
        return view('admin.admin_website.donations_and_charity',[
            'title' => $title,
            'donations_and_charity' => $dataWebsite ?? [],
            'lang_current' => $lang_serviecs ?? [],
        ]);
    }

    public function submit_donations_and_charity(){
        $data = $this->request->input();
        try {
            DB::beginTransaction();
            $allLang = $data['allLang'] ?? null;
            $section1 = $data['section1'] ?? null;
            $dataPost = [];
            if(!empty($section1)) {
                foreach($section1 as $lang => $value) {
                    $dataPost[$lang]['section1'] = $value;
                }
            }

            $section2 = $data['section2'] ?? null;
            if(!empty($section2)) {
                foreach ($section2 as $lang => $value) {
                    $dataPost[$lang]['section2'] = $value;
                }
            }
            $section3 = $data['section3'] ?? null;
            if(!empty($section3)) {
                foreach($section3 as $lang => $value) {
                    $dataPost[$lang]['section3'] = $value;
                }
            }

            $section4 = $data['section4'] ?? null;
            $imageBefore = $data['image_before'] ?? null;
            if(!empty($section4)) {
                foreach($section4 as $lang => $value) {
                    $dataPost[$lang]['section4'] = $value;
                    $dataPost[$lang]['section4']['image'] = $imageBefore;
                }
            }
            if ($this->request->file('image')) {
                if (!empty($this->request->file('image'))) {
                    if(!empty($this->request->file('image'))) {
                        $path = $this->UploadFile(
                            $this->request->file('image'),
                            'web/donations_and_charity',
                            260,
                            390,
                            false
                        );
                        foreach($allLang as $lang) {
                            $image = is_array($path) ? $path[0] : $path;
                            if(!empty($dataPost[$lang]['section4']['image'])) {
                                $this->deleteFile($dataPost[$lang]['section4']['image']);
                            }
                            $dataPost[$lang]['section4']['image'] = $image;
                        }
                    }
                }
            }

            foreach($dataPost as $lang => $value) {
                if($lang == 'tab') continue;
                $websiteData = Website::where('language', $lang)
                    ->where('type', 'donations_and_charity')->first();
                if(!empty($websiteData)) {
                    $websiteData->content = json_encode($value);
                    $websiteData->save();
                }
                else
                {
                    $newWebsite = new Website();
                    $newWebsite->language = $lang;
                    $newWebsite->type = 'donations_and_charity';
                    $newWebsite->content = json_encode($value);
                    $newWebsite->save();
                }
            }
            DB::commit();
            $data['result'] = true;
            $data['message'] = lang('dt_success');
            return response()->json($data);

        }
        catch (\Exception $exception) {
            DB::rollBack();
            $data['result'] = false;
            $data['message'] = $exception->getMessage();
            return response()->json($data);
        }
    }
}
