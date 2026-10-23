<?php
namespace App\Services;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
class VideoAvailability
{
    public function inspect(string $videoId): array
    {
        $url = 'https://www.youtube.com/oembed?'.http_build_query(['format'=>'json','url'=>'https://www.youtube.com/watch?v='.$videoId]);
        try {
            $response = Http::connectTimeout(3)->timeout(5)->withoutRedirecting()->get($url);
        } catch (ConnectionException $exception) {
            return ['state'=>'provider_failure','status'=>null,'url'=>$url];
        }
        $status=$response->status();$body=$response->body();
        $result=['state'=>'unknown','status'=>$status,'url'=>$url,'body_sha256'=>hash('sha256',$body)];
        if ($status===200 && $response->json('type')==='video' && $response->json('provider_name')==='YouTube') {
            return array_merge($result,['state'=>'available']);
        }
        if ($status===429 || $status>=500 || ($response->json('error')!==null)) {
            return array_merge($result,['state'=>'provider_failure']);
        }
        // Only bounded plain-text oEmbed errors are candidates. HTML challenges and
        // structured API authorization/quota errors are not video-specific evidence.
        $expected=[401=>'Unauthorized',403=>'Forbidden',404=>'Not Found',410=>'Gone'];
        if (isset($expected[$status]) && str_starts_with(strtolower($response->header('Content-Type')??''),'text/plain') && trim($body)===$expected[$status]) {
            return array_merge($result,['state'=>'candidate','body'=>$body]);
        }
        return $result;
    }
    public function reason(array $result): string
    {
        $label=in_array($result['status'],[401,403],true)
            ? 'Video private or inaccessible; privacy is not confirmed'
            : 'Video missing or unavailable; deletion is not confirmed';
        return $label.' (YouTube oEmbed HTTP '.$result['status'].'; response: '.trim($result['body']).').';
    }
}
