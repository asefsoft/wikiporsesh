<?php


namespace App\Article\CrawlDetail;

use App\InstaPage;
use App\Url;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WikiPorseshArticleDetail extends ArticleDetail
{

    public function parse_video_info(): bool {

        try {
            $info = json_decode($this->video_html_source, true);

            if (is_array($info) && isset($info['data']['attributes'])) {
                $this->video_info = $info;
                $this->video_info_found = true;

                $attr = $info['data']['attributes'];
                $this->video_title = $attr['title'] ?? '';
                $this->video_description = $attr['description'] ?? '';
                $this->video_tags = collect($attr['tags'] ?? []);
                $this->video_poster_url = $attr['big_poster'] ?? '';
                $this->video_thumb_url = $attr['small_poster'] ?? '';
                $this->video_duration = $attr['duration'] ?? 0;
                $this->video_category = $attr['category']['name_en'] ?? '';
                $this->video_publish_date = Date::make($attr['mdate'] ?? '');

                $this->get_video_streams();

                // default page
                $this->video_page = InstaPage::find(0);

                return true;
            }
            else {
                Log::warning(sprintf("Aparat info not valid to be parsed:\n%s", $this->video_html_source));
            }
        } catch (\Exception $e) {
            \Log::error('error on aparat pars data: ' . $e->getMessage());
        }

        return false;
    }



    protected function get_video_streams() {
        $this->video_streams = collect();
        if (! $this->video_info_found) {
            return false;
        }

        $attr = $this->video_info['data']['attributes'];

        if (isset($attr['file_link_all'])) {
            $srcs = collect(($attr['file_link_all']));

            $all_streams = $srcs->map(function ($src) {
                return new AparatVideoStream(collect($src)->toArray());
            });

//            Log::info(sprintf("Total streams found: %s, %s ", $all_streams->count(), $this->getSourceUrl()));

            if ($all_streams->count()>=1) {
                $this->video_stream_found = true;
                $this->video_streams = $all_streams;
                return true;
            }
        }

        Log::warning(sprintf("No streams found for %s\n%s", $this->getVideoTitle(), json_encode($attr)));

        return false;

    }

    protected function get_video_thumb_url(){
        if (! $this->video_info_found) return false;

        $pattern="#(https:\/\/static\.cdn\.asset\.aparat\.com\/avt\/[0-9]*-[0-9]*)(-b)(__[0-9]*)*\.jpg#";

        return preg_replace($pattern,'$1$3.jpg',$this->video_poster_url) ?? '';
    }

}
